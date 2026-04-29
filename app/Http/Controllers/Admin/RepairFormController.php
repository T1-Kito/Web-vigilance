<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RepairForm;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use App\Services\PrintService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Support\ActivityLogger;
use Illuminate\Support\Str;

class RepairFormController extends Controller
{
    public function index(Request $request)
    {
        $query = RepairForm::with(['warranty.product', 'warrantyClaim']);

        if ($request->filled('serial_search')) {
            $serialSearch = trim($request->serial_search);
            $query->where(function ($q) use ($serialSearch) {
                $q->where('serial_numbers', 'LIKE', "%{$serialSearch}%")
                    ->orWhere('customer_company', 'LIKE', "%{$serialSearch}%")
                    ->orWhere('contact_person', 'LIKE', "%{$serialSearch}%");
            });
        }

        if ($request->filled('status_filter')) {
            if (in_array($request->status_filter, ['not_returned', 'returned'], true)) {
                $query->where('status', $request->status_filter);
            }
        }

        $statsQuery = clone $query;

        $totalForms = (clone $statsQuery)->count();
        $notReturnedCount = (clone $statsQuery)->where('status', 'not_returned')->count();
        $returnedCount = (clone $statsQuery)->where('status', 'returned')->count();

        $repairForms = $query
            ->orderByDesc('received_date')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.repair_forms.index', compact('repairForms', 'totalForms', 'notReturnedCount', 'returnedCount'));
    }

    public function returnIndex(Request $request)
    {
        $query = RepairForm::with(['warranty.product', 'warrantyClaim'])
            ->where('status', 'returned');

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('form_number', 'LIKE', "%{$keyword}%")
                    ->orWhere('serial_numbers', 'LIKE', "%{$keyword}%")
                    ->orWhere('customer_company', 'LIKE', "%{$keyword}%")
                    ->orWhere('contact_person', 'LIKE', "%{$keyword}%");
            });
        }

        if ($request->filled('has_file')) {
            if ($request->has_file === 'yes') {
                $query->whereNotNull('return_file_path');
            } elseif ($request->has_file === 'no') {
                $query->whereNull('return_file_path');
            }
        }

        $returnForms = $query
            ->orderByDesc('actual_return_date')
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $totalReturned = (clone $query)->count();
        $withFileCount = (clone $query)->whereNotNull('return_file_path')->count();

        return view('admin.repair_forms.returns', compact('returnForms', 'totalReturned', 'withFileCount'));
    }

    public function create()
    {
        $warranties = Warranty::with('product')->get();

        $warrantyMap = [];
        foreach ($warranties as $w) {
            $computedWarrantyStatus = null;
            if ($w->warranty_end_date) {
                $computedWarrantyStatus = $w->warranty_end_date->endOfDay()->gte(now()) ? 'under_warranty' : 'out_of_warranty';
            }
            $warrantyMap[$w->serial_number] = [
                'customer' => $w->customer_name,
                'phone' => $w->customer_phone,
                'product' => optional($w->product)->name ?: ($w->model_name ?: null),
                'purchase_date' => $w->purchase_date ? $w->purchase_date->toDateString() : null,
                'warranty_status' => $computedWarrantyStatus,
            ];
        }

        return view('admin.repair_forms.create', compact('warranties', 'warrantyMap'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'serial_number' => 'nullable|string|max:5000',
            'serial_numbers' => 'nullable|string|max:5000',
            'warranty_id' => 'nullable|integer',
            'warranty_claim_id' => 'nullable|integer',
            'customer_company' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'alternate_contact' => 'nullable|string|max:255',
            'alternate_phone' => 'nullable|string|max:20',
            'purchase_date' => 'nullable|date',
            'purchase_date_unknown' => 'nullable|boolean',
            'company_phone' => 'nullable|string|max:20',
            'fax' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'equipment_name' => 'required|string|max:255',
            'error_status' => 'required|string',
            'received_date' => 'required|date',
            'warranty_status' => 'nullable|in:under_warranty,out_of_warranty',
            'includes_adapter' => 'nullable|boolean',
            'accessories' => 'nullable|string|max:1000',
            'employee_count' => 'nullable|integer|min:1',
            'repair_time_required' => 'nullable|string|max:100',
            'estimated_return_date' => 'nullable|date',
            'actual_return_date' => 'nullable|date',
            'estimated_warranty_time' => 'nullable|string|max:100',
            'received_by' => 'nullable|string|max:255',
            'received_by_phone' => 'nullable|string|max:20',
            'service_representative' => 'nullable|string|max:255',
            'handed_over_by' => 'nullable|string|max:255',
            'handed_over_by_phone' => 'nullable|string|max:20',
            'handover_notes' => 'nullable|string',
            'handover_repair_info' => 'nullable|string',
            'handover_check_info' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|max:20',
        ]);

        if (!array_key_exists('contact_person', $data) || $data['contact_person'] === null) {
            $data['contact_person'] = '';
        }

        if (!array_key_exists('contact_phone', $data) || $data['contact_phone'] === null) {
            $data['contact_phone'] = '';
        }

        if (empty($data['serial_number']) && empty($data['serial_numbers'])) {
            return back()
                ->withErrors(['serial_numbers' => 'Vui lòng nhập số seri (SN).'])
                ->withInput();
        }

        $rawSerialInput = trim((string) ($data['serial_numbers'] ?: $data['serial_number']));
        $rawSerialInput = str_replace(["\r\n", "\r"], "\n", $rawSerialInput);
        $serialParts = preg_split('/[\n,;\t ]+/', $rawSerialInput, -1, PREG_SPLIT_NO_EMPTY);
        $serialList = array_values(array_unique(array_map(function ($sn) {
            return strtoupper(trim((string) $sn));
        }, $serialParts ?? [])));

        if (empty($serialList)) {
            return back()
                ->withErrors(['serial_numbers' => 'Vui lòng nhập ít nhất 1 số seri hợp lệ.'])
                ->withInput();
        }

        $firstSerial = $serialList[0];
        $warranty = Warranty::where('serial_number', $firstSerial)->first();

        if (empty($data['warranty_id'])) {
            $data['warranty_id'] = $warranty?->id;
        }
        $data['serial_numbers'] = implode(', ', $serialList);
        unset($data['serial_number']);

        $purchaseDateUnknown = !empty($data['purchase_date_unknown']);
        if ($purchaseDateUnknown) {
            $data['purchase_date'] = null;
        } elseif (empty($data['purchase_date'])) {
            $data['purchase_date'] = $warranty?->purchase_date ? $warranty->purchase_date->toDateString() : now()->toDateString();
        }

        $incomingStatus = $data['status'] ?? null;
        if (in_array($incomingStatus, ['returned', 'completed'], true)) {
            $data['status'] = 'returned';
        } else {
            $data['status'] = 'not_returned';
        }

        if (empty($data['repair_time_required'])) {
            $data['repair_time_required'] = 'N/A';
        }

        if (empty($data['warranty_status'])) {
            if ($warranty && $warranty->warranty_end_date) {
                $isUnderWarranty = $warranty->warranty_end_date->endOfDay()->gte(now());
                $data['warranty_status'] = $isUnderWarranty ? 'under_warranty' : 'out_of_warranty';
            } else {
                $data['warranty_status'] = 'out_of_warranty';
            }
        }

        if (empty($data['received_by'])) {
            $data['received_by'] = 'Nguyễn Thị Hồng Vi';
        }

        if (!array_key_exists('service_representative', $data) || $data['service_representative'] === null) {
            $data['service_representative'] = '';
        }

        $repairForm = RepairForm::create($data);

        ActivityLogger::log('repair_form.create', $repairForm, 'Thêm phiếu nhận & trả bảo hành', [
            'serial_numbers' => $repairForm->serial_numbers ?? null,
            'customer_company' => $repairForm->customer_company ?? null,
            'received_date' => $repairForm->received_date ?? null,
        ], $request);

        return redirect()->route('admin.repair-forms.index')
            ->with('success', 'Phiếu bảo hành đã được tạo thành công!');
    }

    public function show(RepairForm $repairForm)
    {
        $repairForm->load(['warranty.product', 'warrantyClaim']);
        
        return view('admin.repair_forms.show', compact('repairForm'));
    }

    public function edit(RepairForm $repairForm)
    {
        $warranties = Warranty::with('product')->get();

        $warrantyMap = [];
        foreach ($warranties as $w) {
            $computedWarrantyStatus = null;
            if ($w->warranty_end_date) {
                $computedWarrantyStatus = $w->warranty_end_date->endOfDay()->gte(now()) ? 'under_warranty' : 'out_of_warranty';
            }
            $warrantyMap[$w->serial_number] = [
                'customer' => $w->customer_name,
                'phone' => $w->customer_phone,
                'product' => optional($w->product)->name ?: ($w->model_name ?: null),
                'purchase_date' => $w->purchase_date ? $w->purchase_date->toDateString() : null,
                'warranty_status' => $computedWarrantyStatus,
            ];
        }
        
        return view('admin.repair_forms.edit', compact('repairForm', 'warranties', 'warrantyMap'));
    }

    public function update(Request $request, RepairForm $repairForm)
    {
        $before = $repairForm->only([
            'serial_numbers',
            'customer_company',
            'contact_person',
            'contact_phone',
            'purchase_date',
            'equipment_name',
            'error_status',
            'received_date',
            'warranty_status',
            'includes_adapter',
            'accessories',
            'repair_time_required',
            'estimated_return_date',
            'actual_return_date',
            'received_by',
            'notes',
            'status',
        ]);

        $data = $request->validate([
            'serial_numbers' => 'required|string|max:255',
            'customer_company' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'purchase_date' => 'nullable|date',
            'purchase_date_unknown' => 'nullable|boolean',
            'equipment_name' => 'required|string|max:255',
            'error_status' => 'required|string',
            'received_date' => 'required|date',
            'warranty_status' => 'nullable|in:under_warranty,out_of_warranty',
            'includes_adapter' => 'nullable|boolean',
            'accessories' => 'nullable|string|max:1000',
            'repair_time_required' => 'nullable|string|max:100',
            'estimated_return_date' => 'nullable|date',
            'actual_return_date' => 'nullable|date',
            'estimated_warranty_time' => 'nullable|string|max:100',
            'received_by' => 'nullable|string|max:255',
            'received_by_phone' => 'nullable|string|max:20',
            'handed_over_by' => 'nullable|string|max:255',
            'handed_over_by_phone' => 'nullable|string|max:20',
            'handover_notes' => 'nullable|string',
            'handover_repair_info' => 'nullable|string',
            'handover_check_info' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:not_returned,returned'
        ]);

        if (!array_key_exists('contact_person', $data) || $data['contact_person'] === null) {
            $data['contact_person'] = '';
        }

        if (!array_key_exists('contact_phone', $data) || $data['contact_phone'] === null) {
            $data['contact_phone'] = '';
        }

        $serialNumber = trim($data['serial_numbers']);
        $warranty = Warranty::where('serial_number', $serialNumber)->first();

        $data['warranty_id'] = $warranty?->id;

        if (empty($data['warranty_status'])) {
            if ($warranty && $warranty->warranty_end_date) {
                $isUnderWarranty = $warranty->warranty_end_date->endOfDay()->gte(now());
                $data['warranty_status'] = $isUnderWarranty ? 'under_warranty' : 'out_of_warranty';
            } else {
                $data['warranty_status'] = 'out_of_warranty';
            }
        }

        if (empty($data['received_by'])) {
            $data['received_by'] = $repairForm->received_by ?: 'Vi Khang';
        }

        if (!array_key_exists('service_representative', $data) || $data['service_representative'] === null) {
            $data['service_representative'] = $repairForm->service_representative ?: '';
        }

        $purchaseDateUnknown = !empty($data['purchase_date_unknown']);
        if ($purchaseDateUnknown) {
            $data['purchase_date'] = null;
        } elseif (empty($data['purchase_date'])) {
            $data['purchase_date'] = $warranty?->purchase_date ? $warranty->purchase_date->toDateString() : ($repairForm->purchase_date ? $repairForm->purchase_date->toDateString() : now()->toDateString());
        }
        if (empty($data['repair_time_required'])) {
            $data['repair_time_required'] = $repairForm->repair_time_required ?: 'N/A';
        }

        $repairForm->update($data);

        $after = $repairForm->fresh()->only([
            'serial_numbers',
            'customer_company',
            'contact_person',
            'contact_phone',
            'purchase_date',
            'equipment_name',
            'error_status',
            'received_date',
            'warranty_status',
            'includes_adapter',
            'accessories',
            'repair_time_required',
            'estimated_return_date',
            'actual_return_date',
            'received_by',
            'notes',
            'status',
        ]);
        ActivityLogger::log('repair_form.update', $repairForm, 'Cập nhật phiếu nhận & trả bảo hành', [
            'before' => $before,
            'after' => $after,
        ], $request);

        return redirect()->route('admin.repair-forms.index')
            ->with('success', 'Phiếu bảo hành đã được cập nhật thành công!');
    }

    public function destroy(RepairForm $repairForm)
    {
        ActivityLogger::log('repair_form.delete', $repairForm, 'Xóa phiếu nhận & trả bảo hành', [
            'serial_numbers' => $repairForm->serial_numbers ?? null,
            'customer_company' => $repairForm->customer_company ?? null,
        ]);
        $repairForm->delete();
        
        return redirect()->route('admin.repair-forms.index')
            ->with('success', 'Phiếu bảo hành đã được xóa thành công!');
    }

    public function exportWord(RepairForm $repairForm)
    {
        $service = new PrintService();
        $data = $service->generatePrintData($repairForm);
        
        return response()
            ->view('admin.repair_forms.print_modern', compact('repairForm'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function printModern(RepairForm $repairForm)
    {
        return response()
            ->view('admin.repair_forms.print_modern', compact('repairForm'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function printReturn(RepairForm $repairForm)
    {
        return response()
            ->view('admin.repair_forms.print_return', compact('repairForm'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function savePrintReturnInfo(Request $request, RepairForm $repairForm)
    {
        $data = $request->validate([
            'handed_over_by' => 'nullable|string|max:255',
            'handed_over_by_phone' => 'nullable|string|max:20',
            'handover_repair_info' => 'nullable|string',
            'handover_check_info' => 'nullable|string|max:100',
            'actual_return_date' => 'nullable|date',
            'service_representative' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if (empty($data['actual_return_date'])) {
            $data['actual_return_date'] = $repairForm->actual_return_date
                ? $repairForm->actual_return_date->toDateString()
                : now()->toDateString();
        }

        $receivedDate = $repairForm->received_date;
        $returnDate = !empty($data['actual_return_date']) ? \Carbon\Carbon::parse($data['actual_return_date']) : null;
        if ($receivedDate && $returnDate) {
            $days = (int) $receivedDate->diffInDays($returnDate, false);
            if ($days < 0) {
                $data['handover_check_info'] = '0 ngày';
            } elseif ($days === 0) {
                $data['handover_check_info'] = 'Trong ngày';
            } else {
                $data['handover_check_info'] = $days . ' ngày';
            }
        }

        if (($repairForm->status ?? null) !== 'returned') {
            $data['status'] = 'returned';
        }

        $before = $repairForm->only([
            'handed_over_by',
            'handed_over_by_phone',
            'handover_repair_info',
            'handover_check_info',
            'actual_return_date',
            'service_representative',
            'notes',
            'status',
        ]);

        $repairForm->update($data);

        $after = $repairForm->fresh()->only([
            'handed_over_by',
            'handed_over_by_phone',
            'handover_repair_info',
            'handover_check_info',
            'actual_return_date',
            'service_representative',
            'notes',
            'status',
        ]);

        ActivityLogger::log('repair_form.print_return_update', $repairForm, 'Cập nhật thông tin phiếu trả khi in', [
            'before' => $before,
            'after' => $after,
        ], $request);

        return redirect()
            ->route('admin.repair-forms.printReturn', $repairForm)
            ->with('success', 'Đã lưu thông tin phiếu trả. Bạn có thể in ngay.');
    }

    public function uploadReturnFile(Request $request, RepairForm $repairForm)
    {
        $validated = $request->validate([
            'return_file' => 'required|file|mimes:pdf|max:10240',
        ]);

        if (!empty($repairForm->return_file_path) && Storage::disk('local')->exists($repairForm->return_file_path)) {
            Storage::disk('local')->delete($repairForm->return_file_path);
        }

        $file = $validated['return_file'];
        $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $safeName = $safeName !== '' ? $safeName : 'phieu-tra';
        $fileName = now()->format('YmdHis') . '-' . $safeName . '.pdf';
        $storedPath = $file->storeAs('private/repair_returns/' . $repairForm->id, $fileName, 'local');

        $repairForm->update([
            'return_file_path' => $storedPath,
            'return_file_original_name' => $file->getClientOriginalName(),
            'return_file_uploaded_at' => now(),
            'status' => 'returned',
        ]);

        return back()->with('success', 'Đã tải lên file phiếu trả (PDF).');
    }

    public function downloadReturnFile(RepairForm $repairForm)
    {
        if (empty($repairForm->return_file_path) || !Storage::disk('local')->exists($repairForm->return_file_path)) {
            return back()->with('error', 'Không tìm thấy file phiếu trả.');
        }

        $downloadName = $repairForm->return_file_original_name ?: ('phieu-tra-' . ($repairForm->form_number ?: $repairForm->id) . '.pdf');

        return Storage::disk('local')->download($repairForm->return_file_path, $downloadName);
    }

    public function deleteReturnFile(RepairForm $repairForm)
    {
        if (!empty($repairForm->return_file_path) && Storage::disk('local')->exists($repairForm->return_file_path)) {
            Storage::disk('local')->delete($repairForm->return_file_path);
        }

        $repairForm->update([
            'return_file_path' => null,
            'return_file_original_name' => null,
            'return_file_uploaded_at' => null,
        ]);

        return back()->with('success', 'Đã xóa file phiếu trả.');
    }

    public function printBack(RepairForm $repairForm)
    {
        return view('admin.repair_forms.print_back', compact('repairForm'));
    }

    public function createFromWarranty(Warranty $warranty)
    {
        $warrantyClaims = $warranty->claims;
        
        return view('admin.repair_forms.create_from_warranty', compact('warranty', 'warrantyClaims'));
    }

    public function createFromClaim(WarrantyClaim $warrantyClaim)
    {
        $warranty = $warrantyClaim->warranty;
        
        return view('admin.repair_forms.create_from_claim', compact('warranty', 'warrantyClaim'));
    }
}
