<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Support\ActivityLogger;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\WarrantyImport;
 



class WarrantyController extends Controller
{
    public function index(Request $request)
    {
        $query = Warranty::with(['product', 'claims']);
        
        // Tìm kiếm theo số seri
        if ($request->filled('serial_search')) {
            $serialSearch = $request->serial_search;
            $query->where('serial_number', 'LIKE', "%{$serialSearch}%");
        }

        // Tìm kiếm theo model / tên sản phẩm
        if ($request->filled('model_search')) {
            $modelSearch = $request->model_search;
            $query->where(function ($q) use ($modelSearch) {
                $q->where('model_name', 'LIKE', "%{$modelSearch}%")
                    ->orWhereHas('product', function ($pq) use ($modelSearch) {
                        $pq->where('name', 'LIKE', "%{$modelSearch}%")
                            ->orWhere('brand', 'LIKE', "%{$modelSearch}%")
                            ->orWhere('serial_number', 'LIKE', "%{$modelSearch}%");
                    });
            });
        }

        // Tìm kiếm theo tên khách hàng
        if ($request->filled('customer_search')) {
            $customerSearch = $request->customer_search;
            $query->where('customer_name', 'LIKE', "%{$customerSearch}%");
        }

        // Tìm kiếm theo ngày mua
        if ($request->filled('purchase_date_search')) {
            $query->whereDate('purchase_date', $request->purchase_date_search);
        }
        
        $warranties = $query
            ->orderByRaw('purchase_date IS NULL')
            ->orderByDesc('purchase_date')
            ->orderByDesc('created_at')
            ->paginate(20);
        
        // Tính toán stats từ toàn bộ dữ liệu (không phân trang)
        $statsQuery = Warranty::query();
        if ($request->filled('serial_search')) {
            $statsQuery->where('serial_number', 'LIKE', "%{$request->serial_search}%");
        }

        if ($request->filled('model_search')) {
            $modelSearch = $request->model_search;
            $statsQuery->where(function ($q) use ($modelSearch) {
                $q->where('model_name', 'LIKE', "%{$modelSearch}%")
                    ->orWhereHas('product', function ($pq) use ($modelSearch) {
                        $pq->where('name', 'LIKE', "%{$modelSearch}%")
                            ->orWhere('brand', 'LIKE', "%{$modelSearch}%")
                            ->orWhere('serial_number', 'LIKE', "%{$modelSearch}%");
                    });
            });
        }

        if ($request->filled('customer_search')) {
            $statsQuery->where('customer_name', 'LIKE', "%{$request->customer_search}%");
        }

        if ($request->filled('purchase_date_search')) {
            $statsQuery->whereDate('purchase_date', $request->purchase_date_search);
        }
        
        $stats = [
            'total' => $statsQuery->count(),
            'active' => (clone $statsQuery)->where('status', 'active')
                ->where('warranty_end_date', '>=', now()->toDateString())
                ->count(),
            'expired' => (clone $statsQuery)->where('status', 'expired')->count(),
        ];

        $customerDayOrderCount = null;
        if ($request->filled('customer_search') && $request->filled('purchase_date_search')) {
            $customerDayOrderCount = Warranty::query()
                ->where('customer_name', 'LIKE', "%{$request->customer_search}%")
                ->whereDate('purchase_date', $request->purchase_date_search)
                ->whereNotNull('invoice_number')
                ->distinct()
                ->count('invoice_number');
        }

        return view('admin.warranties.index', compact('warranties', 'stats', 'customerDayOrderCount'));
    }

    public function create()
    {
        $products = Product::all();
        return view('admin.warranties.create', compact('products'));
    }

    public function store(Request $request)
    {
        $isBulk = $request->filled('serial_numbers');

        if (!$isBulk) {
            $data = $request->validate([
                'serial_number' => 'required|string|unique:warranties,serial_number|max:255',
                'product_id' => 'nullable|exists:products,id',
                'model_name' => 'nullable|string|max:255',
                'customer_name' => 'nullable|string|max:255',
                'customer_phone' => 'nullable|string|max:20',
                'customer_email' => 'nullable|email|max:255',
                'customer_address' => 'nullable|string|max:500',
                'customer_tax_id' => 'nullable|string|max:50',
                'purchase_date' => 'required|date|before_or_equal:today',
                'stock_in_date' => 'nullable|date|before_or_equal:today',
                'warranty_start_date' => 'required|date|after_or_equal:purchase_date|before_or_equal:today',
                'warranty_period_months' => 'required|integer|min:1|max:60',
                'invoice_number' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000'
            ]);

            // Chỉ cho phép chọn sản phẩm có sẵn (hoặc không chọn), KHÔNG tạo sản phẩm mới từ màn hình bảo hành
            $productId = $data['product_id'] ?? null;

            // Tự động sinh mã hóa đơn nếu không có
            if (empty($data['invoice_number'])) {
                $data['invoice_number'] = 'HD' . date('Ymd') . str_pad(Warranty::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            }

            // Tính ngày kết thúc bảo hành
            $data['warranty_end_date'] = \Carbon\Carbon::parse($data['warranty_start_date'])
                ->addMonths((int)$data['warranty_period_months'])
                ->toDateString();

            // Tự động xác định trạng thái bảo hành
            $data['status'] = $data['warranty_end_date'] >= now()->toDateString() ? 'active' : 'expired';

            // Cập nhật product_id (có thể null nếu không chọn sản phẩm)
            $data['product_id'] = $productId ?? null;

            $warranty = Warranty::create($data);

            ActivityLogger::log('warranty.create', $warranty, 'Thêm bảo hành: ' . ($warranty->serial_number ?? ''), [
                'serial_number' => $warranty->serial_number ?? null,
                'customer_name' => $warranty->customer_name ?? null,
                'purchase_date' => $warranty->purchase_date ?? null,
            ], $request);

            // Log status change
            $warranty->statuses()->create([
                'status' => 'created',
                'notes' => 'Tạo bảo hành mới',
                'changed_by' => 'admin'
            ]);

            return redirect()->route('admin.warranties.index')
                ->with('success', 'Bảo hành đã được tạo thành công!');
        }

        $data = $request->validate([
            'serial_numbers' => 'required|string',
            'product_id' => 'nullable|exists:products,id',
            'model_name' => 'nullable|string|max:255',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'nullable|string|max:500',
            'customer_tax_id' => 'nullable|string|max:50',
            'purchase_date' => 'required|date|before_or_equal:today',
            'stock_in_date' => 'nullable|date|before_or_equal:today',
            'warranty_start_date' => 'required|date|after_or_equal:purchase_date|before_or_equal:today',
            'warranty_period_months' => 'required|integer|min:1|max:60',
            'invoice_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000'
        ]);

        $raw = (string) $data['serial_numbers'];
        $raw = str_replace(["\r\n", "\r"], "\n", $raw);
        $parts = preg_split('/[\n,;\t ]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        $serials = array_values(array_unique(array_filter(array_map(function ($s) {
            $s = trim((string) $s);
            return $s;
        }, $parts))));

        if (empty($serials)) {
            return back()->withInput()->withErrors([
                'serial_numbers' => 'Vui lòng nhập ít nhất 1 số seri.'
            ]);
        }

        $tooLong = array_values(array_filter($serials, fn($sn) => mb_strlen($sn) > 255));
        if (!empty($tooLong)) {
            return back()->withInput()->withErrors([
                'serial_numbers' => 'Có số seri vượt quá 255 ký tự: ' . implode(', ', array_slice($tooLong, 0, 5))
            ]);
        }

        $existing = Warranty::query()->whereIn('serial_number', $serials)->pluck('serial_number')->all();
        if (!empty($existing)) {
            return back()->withInput()->withErrors([
                'serial_numbers' => 'Các số seri đã tồn tại: ' . implode(', ', array_slice($existing, 0, 10))
            ]);
        }

        $productId = $data['product_id'] ?? null;
        $invoiceNumber = $data['invoice_number'] ?? null;
        if (empty($invoiceNumber)) {
            $invoiceNumber = 'HD' . date('Ymd') . str_pad(Warranty::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
        }

        $warrantyEndDate = \Carbon\Carbon::parse($data['warranty_start_date'])
            ->addMonths((int)$data['warranty_period_months'])
            ->toDateString();
        $status = $warrantyEndDate >= now()->toDateString() ? 'active' : 'expired';

        $createdCount = 0;
        DB::transaction(function () use ($serials, $data, $productId, $invoiceNumber, $warrantyEndDate, $status, &$createdCount) {
            foreach ($serials as $sn) {
                $row = [
                    'serial_number' => $sn,
                    'product_id' => $productId,
                    'model_name' => $data['model_name'] ?? null,
                    'customer_name' => $data['customer_name'] ?? null,
                    'customer_phone' => $data['customer_phone'] ?? null,
                    'customer_email' => $data['customer_email'] ?? null,
                    'customer_address' => $data['customer_address'] ?? null,
                    'customer_tax_id' => $data['customer_tax_id'] ?? null,
                    'purchase_date' => $data['purchase_date'],
                    'stock_in_date' => $data['stock_in_date'] ?? null,
                    'warranty_start_date' => $data['warranty_start_date'],
                    'warranty_end_date' => $warrantyEndDate,
                    'warranty_period_months' => $data['warranty_period_months'],
                    'invoice_number' => $invoiceNumber,
                    'notes' => $data['notes'] ?? null,
                    'status' => $status,
                ];

                $warranty = Warranty::create($row);
                $warranty->statuses()->create([
                    'status' => 'created',
                    'notes' => 'Tạo bảo hành mới',
                    'changed_by' => 'admin'
                ]);
                $createdCount++;
            }
        });

        ActivityLogger::log('warranty.bulk_create', null, 'Thêm bảo hành hàng loạt', [
            'count' => $createdCount,
            'invoice_number' => $invoiceNumber,
            'purchase_date' => $data['purchase_date'] ?? null,
        ], $request);

        return redirect()->route('admin.warranties.index')
            ->with('success', 'Đã tạo ' . $createdCount . ' bảo hành thành công!');
    }

    public function show(Warranty $warranty)
    {
        $warranty->load(['product', 'claims' => function($query) {
            $query->orderByDesc('created_at');
        }, 'statuses' => function($query) {
            $query->orderByDesc('created_at');
        }]);
        
        return view('admin.warranties.show', compact('warranty'));
    }

    public function edit(Warranty $warranty)
    {
        $products = Product::all();
        return view('admin.warranties.edit', compact('warranty', 'products'));
    }

    public function update(Request $request, Warranty $warranty)
    {
        $before = $warranty->only([
            'serial_number',
            'product_id',
            'model_name',
            'customer_name',
            'customer_phone',
            'customer_email',
            'customer_address',
            'customer_tax_id',
            'purchase_date',
            'stock_in_date',
            'warranty_start_date',
            'warranty_end_date',
            'warranty_period_months',
            'invoice_number',
            'notes',
            'status',
        ]);

        $data = $request->validate([
            'serial_number' => 'required|string|max:255|unique:warranties,serial_number,' . $warranty->id,
            'product_id' => 'nullable|exists:products,id',
            'model_name' => 'nullable|string|max:255',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'nullable|string|max:500',
            'customer_tax_id' => 'nullable|string|max:50',
            'purchase_date' => 'required|date|before_or_equal:today',
            'stock_in_date' => 'nullable|date|before_or_equal:today',
            'warranty_start_date' => 'required|date|after_or_equal:purchase_date|before_or_equal:today',
            'warranty_period_months' => 'required|integer|min:1|max:60',
            'invoice_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000'
        ]);

        // Tự động sinh mã hóa đơn nếu không có
        if (empty($data['invoice_number'])) {
            $data['invoice_number'] = 'HD' . date('Ymd') . str_pad(Warranty::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
        }

        // Tính ngày kết thúc bảo hành
        $data['warranty_end_date'] = \Carbon\Carbon::parse($data['warranty_start_date'])
            ->addMonths((int)$data['warranty_period_months'])
            ->toDateString();

        // Tự động xác định trạng thái bảo hành
        $data['status'] = $data['warranty_end_date'] >= now()->toDateString() ? 'active' : 'expired';

        $oldStatus = $warranty->status;
        $warranty->update($data);

        // Log status change nếu có thay đổi
        if ($oldStatus !== $data['status']) {
            $warranty->statuses()->create([
                'status' => $data['status'],
                'notes' => 'Cập nhật trạng thái bảo hành',
                'changed_by' => 'admin'
            ]);
        }

        $after = $warranty->fresh()->only([
            'serial_number',
            'product_id',
            'model_name',
            'customer_name',
            'customer_phone',
            'customer_email',
            'customer_address',
            'customer_tax_id',
            'purchase_date',
            'stock_in_date',
            'warranty_start_date',
            'warranty_end_date',
            'warranty_period_months',
            'invoice_number',
            'notes',
            'status',
        ]);
        ActivityLogger::log('warranty.update', $warranty, 'Cập nhật bảo hành: ' . ($warranty->serial_number ?? ''), [
            'before' => $before,
            'after' => $after,
        ], $request);

        return redirect()->route('admin.warranties.index')
            ->with('success', 'Bảo hành đã được cập nhật thành công!');
    }

    public function destroy(Warranty $warranty)
    {
        ActivityLogger::log('warranty.delete', $warranty, 'Xóa bảo hành: ' . ($warranty->serial_number ?? ''), [
            'serial_number' => $warranty->serial_number ?? null,
        ]);
        $warranty->delete();
        return redirect()->route('admin.warranties.index')
            ->with('success', 'Bảo hành đã được xóa thành công!');
    }

    // Quản lý yêu cầu bảo hành
    public function claims()
    {
        try {
            $claims = WarrantyClaim::with(['warranty.product'])
                ->orderByDesc('created_at')
                ->paginate(20);
                
            return view('admin.warranties.claims', compact('claims'));
        } catch (\Exception $e) {
            return "Lỗi: " . $e->getMessage();
        }
    }

    public function updateClaimStatus(Request $request, WarrantyClaim $claim)
    {
        $before = $claim->only(['claim_status', 'admin_notes', 'estimated_completion_date', 'repair_cost', 'technician_name']);
        $request->validate([
            'claim_status' => 'required|in:pending,approved,rejected,in_progress,completed',
            'admin_notes' => 'nullable|string|max:1000',
            'estimated_completion_date' => 'nullable|date|after:today',
            'repair_cost' => 'nullable|numeric|min:0',
            'technician_name' => 'nullable|string|max:255'
        ]);

        $claim->update([
            'claim_status' => $request->claim_status,
            'admin_notes' => $request->admin_notes,
            'estimated_completion_date' => $request->estimated_completion_date,
            'repair_cost' => $request->repair_cost,
            'technician_name' => $request->technician_name
        ]);

        // Log status change
        $claim->updateStatus($request->claim_status, $request->admin_notes, 'admin');

        $after = $claim->fresh()->only(['claim_status', 'admin_notes', 'estimated_completion_date', 'repair_cost', 'technician_name']);
        ActivityLogger::log('warranty_claim.update', $claim, 'Cập nhật yêu cầu bảo hành', [
            'before' => $before,
            'after' => $after,
            'warranty_id' => $claim->warranty_id ?? null,
        ], $request);

        return back()->with('success', 'Trạng thái yêu cầu bảo hành đã được cập nhật!');
    }

    // Export danh sách bảo hành ra Excel (array exporter đơn giản)
    public function exportExcel()
    {
        $warranties = Warranty::with('product')->orderByDesc('created_at')->get();
        $data = [];
        $data[] = [
            'ID','Số seri','Tên sản phẩm','Tên khách hàng','Mã số thuế','Số điện thoại','Email','Địa chỉ','Ngày mua','Ngày nhập hàng','Ngày bắt đầu bảo hành','Ngày kết thúc bảo hành','Thời hạn (tháng)','Trạng thái','Số hóa đơn','Ghi chú','Ngày tạo'
        ];
        foreach ($warranties as $w) {
            $format = function ($dt, $fmt = 'd/m/Y') {
                return $dt ? \Carbon\Carbon::parse($dt)->format($fmt) : '';
            };
            $end = $w->warranty_end_date ? \Carbon\Carbon::parse($w->warranty_end_date) : null;
            $statusText = $end && $end->endOfDay()->gte(now()) ? 'Còn bảo hành' : 'Hết hạn';

            $data[] = [
                $w->id,
                $w->serial_number,
                optional($w->product)->name,
                $w->customer_name,
                $w->customer_tax_id,
                $w->customer_phone,
                $w->customer_email,
                $w->customer_address,
                $format($w->purchase_date),
                $format($w->stock_in_date),
                $format($w->warranty_start_date),
                $format($w->warranty_end_date),
                $w->warranty_period_months,
                $statusText,
                $w->invoice_number,
                $w->notes,
                $format($w->created_at, 'd/m/Y H:i'),
            ];
        }
        return Excel::download(new \App\Exports\SimpleArrayExport($data), 'danh_sach_bao_hanh.xlsx');
    }

    // Import Excel bảo hành
    public function importExcel(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        $import = new WarrantyImport();
        Excel::import($import, $request->file('file'));
        $msg = "Import xong. Thành công: {$import->getSuccessCount()}, Lỗi: {$import->getErrorCount()}";
        if ($import->getErrorCount() > 0) {
            $msg .= ' (xem log/lỗi chi tiết nếu cần)';
        }
        return back()->with('success', $msg);
    }

    // Xóa tất cả bảo hành
    public function destroyAll(Request $request)
    {
        // Xác nhận lại bằng text để đảm bảo an toàn
        $confirmText = $request->input('confirm_text');
        
        if ($confirmText !== 'XÓA TẤT CẢ') {
            return back()->with('error', 'Vui lòng nhập chính xác "XÓA TẤT CẢ" để xác nhận!');
        }

        try {
            $count = Warranty::count();
            
            // Xóa tất cả dữ liệu liên quan trước
            \DB::table('warranty_statuses')->delete();
            \DB::table('warranty_claims')->delete();
            
            // Xóa tất cả bảo hành
            Warranty::query()->delete();
            
            return redirect()->route('admin.warranties.index')
                ->with('success', "Đã xóa thành công {$count} bảo hành và tất cả dữ liệu liên quan!");
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }


}
