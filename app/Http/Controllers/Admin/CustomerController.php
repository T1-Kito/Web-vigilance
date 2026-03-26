<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    public function lookup(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if ($q === '') {
            return response()->json([]);
        }

        $customers = Customer::query()
            ->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('tax_id', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'tax_id', 'invoice_recipient', 'email', 'phone']);

        return response()->json($customers);
    }

    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('q')) {
            $q = trim((string) $request->query('q'));
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('tax_id', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        $customers = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    public function importForm()
    {
        return view('admin.customers.import');
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');

        try {
            $data = Excel::toArray([], $file);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();

            if (stripos($msg, 'not recognised as an OLE file') !== false) {
                return back()->with('error', 'File Excel không đúng định dạng. Nếu bạn đang dùng file .xls, vui lòng lưu lại thành .xlsx rồi import lại.');
            }

            return back()->with('error', 'Không đọc được file Excel. Vui lòng kiểm tra lại file và thử lại.');
        }
        $rows = $data[0] ?? [];

        $headerRowIndex = 0;
        foreach ($rows as $i => $row) {
            if (!empty(array_filter($row))) {
                $headerRowIndex = $i;
                break;
            }
        }

        $headerRaw = $rows[$headerRowIndex] ?? [];
        $header = array_map(function ($h) {
            return trim((string) $h);
        }, $headerRaw);

        unset($rows[$headerRowIndex]);

        $map = [
            'Tên khách hàng' => 'name',
            'MST/CCCD chủ hộ' => 'tax_id',
            'Địa chỉ' => 'address',
            'Người nhận HĐ' => 'invoice_recipient',
            'Email' => 'email',
            'Số điện thoại' => 'phone',
        ];

        $imported = 0;

        foreach ($rows as $row) {
            if (!is_array($row) || empty(array_filter($row))) {
                continue;
            }

            $rowData = [];
            foreach ($map as $excelHeader => $field) {
                $idx = array_search($excelHeader, $header);
                if ($idx !== false) {
                    $rowData[$field] = isset($row[$idx]) ? trim((string) $row[$idx]) : null;
                }
            }

            $name = trim((string) ($rowData['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $taxId = trim((string) ($rowData['tax_id'] ?? ''));
            $payload = [
                'name' => $name,
                'tax_id' => $taxId !== '' ? $taxId : null,
                'address' => ($rowData['address'] ?? '') !== '' ? $rowData['address'] : null,
                'invoice_recipient' => ($rowData['invoice_recipient'] ?? '') !== '' ? $rowData['invoice_recipient'] : null,
                'email' => ($rowData['email'] ?? '') !== '' ? $rowData['email'] : null,
                'phone' => ($rowData['phone'] ?? '') !== '' ? $rowData['phone'] : null,
            ];

            if ($taxId !== '') {
                Customer::updateOrCreate(['tax_id' => $taxId], $payload);
            } else {
                Customer::create($payload);
            }

            $imported++;
        }

        ActivityLogger::log('customer.import_excel', null, 'Import khách hàng từ Excel', [
            'imported' => $imported,
        ], $request);

        return redirect()->route('admin.customers.index')->with('success', 'Đã import ' . $imported . ' khách hàng!');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tax_id' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'invoice_recipient' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'phone' => 'nullable|string|max:30',
        ]);

        $customer = Customer::create($validated);

        ActivityLogger::log('customer.create', $customer, 'Tạo khách hàng: ' . ($customer->name ?? ''), [
            'name' => $customer->name ?? null,
            'tax_id' => $customer->tax_id ?? null,
        ], $request);

        return redirect()->route('admin.customers.index')->with('success', 'Đã tạo khách hàng!');
    }

    public function show(Customer $customer)
    {
        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'tax_id' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'invoice_recipient' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'phone' => 'nullable|string|max:30',
        ]);

        $before = $customer->only(['name', 'tax_id', 'address', 'invoice_recipient', 'email', 'phone']);

        $customer->update($validated);

        $after = $customer->fresh()->only(['name', 'tax_id', 'address', 'invoice_recipient', 'email', 'phone']);

        ActivityLogger::log('customer.update', $customer, 'Cập nhật khách hàng: ' . ($customer->name ?? ''), [
            'before' => $before,
            'after' => $after,
        ], $request);

        return redirect()->route('admin.customers.show', $customer)->with('success', 'Đã cập nhật khách hàng!');
    }

    public function destroy(Customer $customer, Request $request)
    {
        ActivityLogger::log('customer.delete', $customer, 'Xóa khách hàng: ' . ($customer->name ?? ''), [
            'name' => $customer->name ?? null,
            'tax_id' => $customer->tax_id ?? null,
        ], $request);

        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Đã xóa khách hàng!');
    }
}
