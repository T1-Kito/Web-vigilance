<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BorrowRequest;
use App\Models\BorrowRequestItem;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BorrowRequestController extends Controller
{
    private function statusOptions(): array
    {
        return [
            'proposed' => 'Đang đề xuất',
            'processing' => 'Đang xử lý',
            'borrowing' => 'Đang mượn',
            'returned' => 'Đã trả',
            'overdue' => 'Quá hạn',
        ];
    }

    public function index(Request $request)
    {
        $q = BorrowRequest::query()->withCount('items')->with(['requestedByAdmin']);

        $status = trim((string) $request->query('status', ''));
        if ($status !== '') {
            $q->where('status', $status);
        }

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $q->where(function ($sub) use ($search) {
                $sub->where('code', 'like', '%' . $search . '%')
                    ->orWhere('customer_name', 'like', '%' . $search . '%')
                    ->orWhere('purpose', 'like', '%' . $search . '%');
            });
        }

        $requests = $q->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('admin.borrow_requests.index', [
            'requests' => $requests,
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function create()
    {
        return view('admin.borrow_requests.create', [
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'requested_by_name' => 'nullable|string|max:255',
            'approved_by_name' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'customer_name' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'tax_code' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'contact_phone' => 'nullable|string|max:30',
            'purpose' => 'nullable|string|max:255',
            'current_project' => 'nullable|string|max:255',
            'borrow_from' => 'nullable|date',
            'borrow_to' => 'nullable|date',
            'deposit_text' => 'required|in:Có cọc',
            'deposit_amount' => 'required|numeric|min:0',
            'status' => 'required|in:proposed,processing,borrowing,returned,overdue',
            'note' => 'nullable|string',

            'items' => 'nullable|array',
            'items.*.item_name' => 'nullable|string|max:255',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.quantity' => 'nullable|numeric',
            'items.*.value' => 'nullable|numeric',
            'items.*.note' => 'nullable|string|max:255',
        ]);

        $items = is_array($validated['items'] ?? null) ? $validated['items'] : [];

        return DB::transaction(function () use ($validated, $items, $request) {
            $borrowFrom = isset($validated['borrow_from']) && $validated['borrow_from'] ? Carbon::parse($validated['borrow_from']) : null;
            $borrowTo = isset($validated['borrow_to']) && $validated['borrow_to'] ? Carbon::parse($validated['borrow_to']) : null;

            if ($borrowFrom && !$borrowTo) {
                $borrowTo = $borrowFrom->copy()->addDays(7);
            }

            $depositText = $validated['deposit_text'];
            $depositAmount = $validated['deposit_amount'];

            $br = BorrowRequest::create([
                'requested_by_admin_id' => Auth::id(),
                'requested_by_name' => $validated['requested_by_name'] ?? null,
                'approved_by_name' => $validated['approved_by_name'] ?? null,
                'department' => $validated['department'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'contact_name' => $validated['contact_name'] ?? null,
                'tax_code' => $validated['tax_code'] ?? null,
                'email' => $validated['email'] ?? null,
                'contact_phone' => $validated['contact_phone'] ?? null,
                'purpose' => $validated['purpose'] ?? null,
                'current_project' => $validated['current_project'] ?? null,
                'borrow_from' => $borrowFrom ? $borrowFrom->toDateString() : null,
                'borrow_to' => $borrowTo ? $borrowTo->toDateString() : null,
                'deposit_text' => $depositText,
                'deposit_amount' => $depositAmount,
                'status' => $validated['status'],
                'note' => $validated['note'] ?? null,
            ]);

            $code = 'PMH-' . Carbon::now()->format('ymd') . '-' . str_pad((string) $br->id, 5, '0', STR_PAD_LEFT);
            $br->code = $code;
            $br->save();

            $line = 1;
            foreach ($items as $item) {
                $name = trim((string) ($item['item_name'] ?? ''));
                $unit = trim((string) ($item['unit'] ?? ''));
                $qty = $item['quantity'] ?? null;
                $val = $item['value'] ?? null;
                $note = trim((string) ($item['note'] ?? ''));

                $hasAny = ($name !== '') || ($unit !== '') || ($qty !== null && $qty !== '') || ($val !== null && $val !== '') || ($note !== '');
                if (!$hasAny) {
                    continue;
                }

                BorrowRequestItem::create([
                    'borrow_request_id' => $br->id,
                    'line_no' => $line,
                    'item_name' => $name !== '' ? $name : null,
                    'unit' => $unit !== '' ? $unit : null,
                    'quantity' => ($qty !== null && $qty !== '') ? $qty : null,
                    'value' => ($val !== null && $val !== '') ? $val : null,
                    'note' => $note !== '' ? $note : null,
                ]);

                $line++;
            }

            ActivityLogger::log('borrow_request.create', $br, 'Tạo phiếu mượn hàng: ' . ($br->code ?? ''), [
                'status' => $br->status,
                'items_count' => $br->items()->count(),
            ], $request);

            return redirect()->route('admin.borrow-requests.show', $br)->with('success', 'Đã tạo phiếu mượn hàng!');
        });
    }

    public function show(BorrowRequest $borrowRequest)
    {
        $borrowRequest->load(['items', 'requestedByAdmin']);

        return view('admin.borrow_requests.show', [
            'borrowRequest' => $borrowRequest,
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function edit(BorrowRequest $borrowRequest)
    {
        $borrowRequest->load(['items']);

        return view('admin.borrow_requests.edit', [
            'borrowRequest' => $borrowRequest,
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function update(Request $request, BorrowRequest $borrowRequest)
    {
        $validated = $request->validate([
            'requested_by_name' => 'nullable|string|max:255',
            'approved_by_name' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'customer_name' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'tax_code' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'contact_phone' => 'nullable|string|max:30',
            'purpose' => 'nullable|string|max:255',
            'current_project' => 'nullable|string|max:255',
            'borrow_from' => 'nullable|date',
            'borrow_to' => 'nullable|date',
            'deposit_text' => 'required|in:Có cọc',
            'deposit_amount' => 'required|numeric|min:0',
            'status' => 'required|in:proposed,processing,borrowing,returned,overdue',
            'note' => 'nullable|string',

            'items' => 'nullable|array',
            'items.*.item_name' => 'nullable|string|max:255',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.quantity' => 'nullable|numeric',
            'items.*.value' => 'nullable|numeric',
            'items.*.note' => 'nullable|string|max:255',
        ]);

        $items = is_array($validated['items'] ?? null) ? $validated['items'] : [];

        return DB::transaction(function () use ($borrowRequest, $validated, $items, $request) {
            $before = $borrowRequest->only(['requested_by_name', 'approved_by_name', 'department', 'customer_name', 'contact_name', 'tax_code', 'email', 'contact_phone', 'purpose', 'current_project', 'borrow_from', 'borrow_to', 'deposit_text', 'deposit_amount', 'status', 'note']);

            $borrowFrom = isset($validated['borrow_from']) && $validated['borrow_from'] ? Carbon::parse($validated['borrow_from']) : null;
            $borrowTo = isset($validated['borrow_to']) && $validated['borrow_to'] ? Carbon::parse($validated['borrow_to']) : null;

            if ($borrowFrom && !$borrowTo) {
                $borrowTo = $borrowFrom->copy()->addDays(7);
            }

            $depositText = $validated['deposit_text'];
            $depositAmount = $validated['deposit_amount'];

            $borrowRequest->update([
                'requested_by_name' => $validated['requested_by_name'] ?? null,
                'approved_by_name' => $validated['approved_by_name'] ?? null,
                'department' => $validated['department'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'contact_name' => $validated['contact_name'] ?? null,
                'tax_code' => $validated['tax_code'] ?? null,
                'email' => $validated['email'] ?? null,
                'contact_phone' => $validated['contact_phone'] ?? null,
                'purpose' => $validated['purpose'] ?? null,
                'current_project' => $validated['current_project'] ?? null,
                'borrow_from' => $borrowFrom ? $borrowFrom->toDateString() : null,
                'borrow_to' => $borrowTo ? $borrowTo->toDateString() : null,
                'deposit_text' => $depositText,
                'deposit_amount' => $depositAmount,
                'status' => $validated['status'],
                'note' => $validated['note'] ?? null,
            ]);

            $borrowRequest->items()->delete();

            $line = 1;
            foreach ($items as $item) {
                $name = trim((string) ($item['item_name'] ?? ''));
                $unit = trim((string) ($item['unit'] ?? ''));
                $qty = $item['quantity'] ?? null;
                $val = $item['value'] ?? null;
                $note = trim((string) ($item['note'] ?? ''));

                $hasAny = ($name !== '') || ($unit !== '') || ($qty !== null && $qty !== '') || ($val !== null && $val !== '') || ($note !== '');
                if (!$hasAny) {
                    continue;
                }

                BorrowRequestItem::create([
                    'borrow_request_id' => $borrowRequest->id,
                    'line_no' => $line,
                    'item_name' => $name !== '' ? $name : null,
                    'unit' => $unit !== '' ? $unit : null,
                    'quantity' => ($qty !== null && $qty !== '') ? $qty : null,
                    'value' => ($val !== null && $val !== '') ? $val : null,
                    'note' => $note !== '' ? $note : null,
                ]);

                $line++;
            }

            $after = $borrowRequest->fresh()->only(['requested_by_name', 'approved_by_name', 'department', 'customer_name', 'contact_name', 'tax_code', 'email', 'contact_phone', 'purpose', 'current_project', 'borrow_from', 'borrow_to', 'deposit_text', 'deposit_amount', 'status', 'note']);

            ActivityLogger::log('borrow_request.update', $borrowRequest, 'Cập nhật phiếu mượn hàng: ' . ($borrowRequest->code ?? ''), [
                'before' => $before,
                'after' => $after,
                'items_count' => $borrowRequest->items()->count(),
            ], $request);

            return redirect()->route('admin.borrow-requests.show', $borrowRequest)->with('success', 'Đã cập nhật phiếu mượn hàng!');
        });
    }

    public function destroy(BorrowRequest $borrowRequest, Request $request)
    {
        try {
            ActivityLogger::log('borrow_request.delete', $borrowRequest, 'Xóa phiếu mượn hàng: ' . ($borrowRequest->code ?? ''), [], $request);
            $borrowRequest->delete();
            return redirect()->route('admin.borrow-requests.index')->with('success', 'Đã xóa phiếu mượn hàng!');
        } catch (\Throwable $e) {
            return redirect()->route('admin.borrow-requests.index')->with('error', 'Có lỗi xảy ra khi xóa phiếu!');
        }
    }
}
