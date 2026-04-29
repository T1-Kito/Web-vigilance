<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Debt;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Support\ActivityLogger;
use App\Support\DocumentCodeGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WebOrderIntakeController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(\App\Support\Permission::allows(auth()->user(), 'orders.view'), 403);

        $statusOptions = [
            '' => 'Tất cả trạng thái',
            'pending' => 'Chờ xử lý',
            'processing' => 'Đã duyệt',
            'cancelled' => 'Đã hủy',
        ];

        $query = Order::query()
            ->with(['items'])
            ->where('order_code', 'like', 'OD%')
            ->orderByDesc('created_at');

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('order_code', 'like', '%' . $q . '%')
                    ->orWhere('receiver_name', 'like', '%' . $q . '%')
                    ->orWhere('invoice_company_name', 'like', '%' . $q . '%')
                    ->orWhere('customer_tax_code', 'like', '%' . $q . '%')
                    ->orWhere('receiver_phone', 'like', '%' . $q . '%')
                    ->orWhere('customer_phone', 'like', '%' . $q . '%');
            });
        }

        $customerName = trim((string) $request->query('customer_name', ''));
        if ($customerName !== '') {
            $query->where(function ($sub) use ($customerName) {
                $sub->where('invoice_company_name', 'like', '%' . $customerName . '%')
                    ->orWhere('receiver_name', 'like', '%' . $customerName . '%');
            });
        }

        $taxCode = trim((string) $request->query('tax_code', ''));
        if ($taxCode !== '') {
            $query->where('customer_tax_code', 'like', '%' . $taxCode . '%');
        }

        $status = trim((string) $request->query('status', ''));
        if ($status !== '') {
            $query->where('status', $status);
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('admin.web_orders.index', [
            'orders' => $orders,
            'statusOptions' => $statusOptions,
        ]);
    }

    public function show(Order $order)
    {
        abort_unless(\App\Support\Permission::allows(auth()->user(), 'orders.view'), 403);

        if (!$this->isWebOrder($order)) {
            abort(404);
        }

        return view('admin.web_orders.show', [
            'order' => $order->load(['items.product', 'user']),
        ]);
    }

    public function edit(Order $order)
    {
        abort_unless(\App\Support\Permission::allows(auth()->user(), 'orders.edit'), 403);

        if (!$this->isWebOrder($order)) {
            abort(404);
        }

        if ((string) $order->status !== 'pending') {
            return redirect()->route('admin.web-orders.show', $order)
                ->with('error', 'Đơn web đã duyệt hoặc đã kết thúc, không thể chỉnh sửa.');
        }

        $statusOptions = [
            'pending' => 'Chờ xử lý',
            'processing' => 'Đã duyệt',
            'cancelled' => 'Đã hủy',
        ];

        return view('admin.quotes.edit', [
            'order' => $order->load(['items.product']),
            'statusOptions' => $statusOptions,
            'pageMode' => 'web_order',
            'pageTitle' => 'Xử lý đơn từ Web',
            'pageHeading' => 'Sửa đơn web: ' . ($order->order_code ?? ('OD' . str_pad((string) $order->id, 6, '0', STR_PAD_LEFT))),
            'backRoute' => 'admin.web-orders.index',
            'formAction' => route('admin.web-orders.update', $order),
            'submitLabel' => 'Lưu xử lý đơn web',
        ]);
    }

    public function update(Request $request, Order $order)
    {
        abort_unless(\App\Support\Permission::allows(auth()->user(), 'orders.edit'), 403);

        if (!$this->isWebOrder($order)) {
            abort(404);
        }

        if ((string) $order->status !== 'pending') {
            return back()->with('error', 'Đơn web đã duyệt hoặc đã kết thúc, không thể chỉnh sửa.');
        }

        $validated = $request->validate([
            'receiver_name' => ['required', 'string', 'max:255'],
            'receiver_phone' => ['nullable', 'string', 'max:50'],
            'receiver_address' => ['required', 'string', 'max:2000'],

            'invoice_company_name' => ['nullable', 'string', 'max:255'],
            'invoice_address' => ['nullable', 'string', 'max:2000'],
            'customer_tax_code' => ['nullable', 'string', 'max:50'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'customer_contact_person' => ['nullable', 'string', 'max:100'],
            'customer_type' => ['nullable', 'in:retail,agent,factory,enterprise'],

            'staff_code' => ['nullable', 'string', 'max:100'],
            'sales_name' => ['nullable', 'string', 'max:150'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'vat_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_term' => ['required', 'in:full_advance,debt,deposit'],
            'payment_due_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'deposit_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_note' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:pending,processing,cancelled'],
            'note' => ['nullable', 'string', 'max:2000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'integer'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99999'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $oldStatus = (string) ($order->status ?? 'pending');
        $newStatus = (string) ($validated['status'] ?? $oldStatus);
        if (!$this->isAllowedTransition($oldStatus, $newStatus)) {
            return back()->withInput()->with('error', "Không thể chuyển trạng thái từ '{$oldStatus}' sang '{$newStatus}'.");
        }

        $before = [
            'status' => $order->status,
            'discount_percent' => $order->discount_percent,
            'vat_percent' => $order->vat_percent,
            'payment_term' => $order->payment_term,
            'payment_due_days' => $order->payment_due_days,
            'deposit_percent' => $order->deposit_percent,
        ];

        DB::transaction(function () use ($order, $validated) {
            $order->update([
                'receiver_name' => $validated['receiver_name'],
                'receiver_phone' => $validated['receiver_phone'],
                'receiver_address' => $validated['receiver_address'],
                'invoice_company_name' => $validated['invoice_company_name'] ?? null,
                'invoice_address' => $validated['invoice_address'] ?? null,
                'customer_tax_code' => $validated['customer_tax_code'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_contact_person' => $validated['customer_contact_person'] ?? null,
                'customer_type' => $validated['customer_type'] ?? null,
                'staff_code' => $validated['staff_code'] ?? null,
                'sales_name' => $validated['sales_name'] ?? null,
                'discount_percent' => $validated['discount_percent'] ?? 0,
                'vat_percent' => $validated['vat_percent'] ?? 8,
                'payment_term' => $validated['payment_term'],
                'payment_due_days' => $validated['payment_term'] === 'debt' ? (int) ($validated['payment_due_days'] ?? 0) : null,
                'deposit_percent' => $validated['payment_term'] === 'deposit' ? (float) ($validated['deposit_percent'] ?? 0) : null,
                'payment_note' => $validated['payment_note'] ?? null,
                'status' => $validated['status'],
                'note' => $validated['note'] ?? null,
            ]);

            $existingItems = $order->items()->get()->keyBy('id');
            $keptItemIds = [];

            foreach ($validated['items'] as $row) {
                $itemId = isset($row['id']) ? (int) $row['id'] : 0;

                if ($itemId > 0) {
                    $item = $existingItems->get($itemId);
                    if ($item) {
                        $item->update([
                            'product_id' => (int) $row['product_id'],
                            'quantity' => (int) $row['quantity'],
                            'price' => (float) $row['unit_price'],
                            'unit' => $row['unit'] ?? null,
                        ]);
                        $keptItemIds[] = $item->id;
                    }
                    continue;
                }

                $created = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => (int) $row['product_id'],
                    'quantity' => (int) $row['quantity'],
                    'price' => (float) $row['unit_price'],
                    'unit' => $row['unit'] ?? null,
                    'parent_order_item_id' => null,
                ]);
                $keptItemIds[] = $created->id;
            }

            if (!empty($keptItemIds)) {
                $order->items()->whereNotIn('id', $keptItemIds)->delete();
            }
        });

        ActivityLogger::log(
            'web_order.update',
            $order,
            'Cập nhật đơn web: ' . ($order->order_code ?? ''),
            [
                'order_code' => $order->order_code,
                'before' => $before,
                'after' => [
                    'status' => $order->status,
                    'discount_percent' => $order->discount_percent,
                    'vat_percent' => $order->vat_percent,
                    'payment_term' => $order->payment_term,
                    'payment_due_days' => $order->payment_due_days,
                    'deposit_percent' => $order->deposit_percent,
                ],
            ],
            $request
        );

        return redirect()->route('admin.web-orders.edit', $order)->with('success', 'Đã cập nhật đơn web thành công.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        abort_unless(\App\Support\Permission::allows(auth()->user(), 'orders.edit'), 403);

        if (!$this->isWebOrder($order)) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:pending,processing,completed,cancelled'],
        ]);

        $oldStatus = (string) ($order->status ?? 'pending');
        $newStatus = (string) ($validated['status'] ?? $oldStatus);

        if (!$this->isAllowedTransition($oldStatus, $newStatus)) {
            return back()->with('error', "Không thể chuyển trạng thái từ '{$oldStatus}' sang '{$newStatus}'.");
        }

        $order->update(['status' => $newStatus]);

        ActivityLogger::log(
            'web_order.update_status',
            $order,
            'Cập nhật trạng thái đơn web',
            [
                'order_code' => $order->order_code,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ],
            $request
        );

        return back()->with('success', 'Đã cập nhật trạng thái đơn web.');
    }

    public function approve(Request $request, Order $order)
    {
        abort_unless(\App\Support\Permission::allows(auth()->user(), 'orders.edit'), 403);

        if (!$this->isWebOrder($order)) {
            abort(404);
        }

        if (!$this->isAllowedTransition((string) $order->status, 'processing')) {
            return back()->with('error', 'Đơn web không ở trạng thái cho phép duyệt sang Đang xử lý.');
        }

        $oldStatus = (string) ($order->status ?? 'pending');
        $order->update(['status' => 'processing']);

        ActivityLogger::log(
            'web_order.approve',
            $order,
            'Duyệt đơn web',
            [
                'order_code' => $order->order_code,
                'old_status' => $oldStatus,
                'new_status' => 'processing',
            ],
            $request
        );

        return back()->with('success', 'Đã duyệt đơn web sang trạng thái Đang xử lý.');
    }

    public function pdf(Order $order)
    {
        abort_unless(\App\Support\Permission::allows(auth()->user(), 'orders.view'), 403);

        if (!$this->isWebOrder($order)) {
            abort(404);
        }

        return redirect()->route('orders.quote.pdf', ['orderCode' => $order->order_code]);
    }

    public function convertToSalesOrder(Request $request, Order $order)
    {
        abort_unless(\App\Support\Permission::allows(auth()->user(), 'orders.edit'), 403);

        if (!$this->isWebOrder($order)) {
            abort(404);
        }

        if ((string) $order->status !== 'processing') {
            return back()->with('error', 'Chỉ sinh đơn hàng tự động khi đơn web ở trạng thái Đang xử lý.');
        }

        $existing = SalesOrder::query()->where('source_order_id', $order->id)->first();
        if ($existing) {
            return redirect()->route('admin.sales-orders.show', $existing)->with('success', 'Đơn web đã được sinh đơn hàng trước đó.');
        }

        $validated = $request->validate([
            'delivery_due_date' => ['nullable', 'date'],
            'payment_due_date' => ['nullable', 'date'],
        ]);

        $salesOrder = DB::transaction(function () use ($order, $validated) {
            $salesOrderCode = DocumentCodeGenerator::next(SalesOrder::query(), 'sales_order_code', 'SO');

            $salesOrder = SalesOrder::create([
                'user_id' => $order->user_id,
                'source_order_id' => $order->id,
                'sales_order_code' => $salesOrderCode,
                'receiver_name' => $order->receiver_name,
                'receiver_phone' => $order->receiver_phone,
                'receiver_address' => $order->receiver_address,
                'invoice_company_name' => $order->invoice_company_name,
                'invoice_address' => $order->invoice_address,
                'customer_tax_code' => $order->customer_tax_code,
                'customer_phone' => $order->customer_phone,
                'customer_email' => $order->customer_email,
                'customer_contact_person' => $order->customer_contact_person,
                'staff_code' => $order->staff_code,
                'sales_name' => $order->sales_name,
                'discount_percent' => $order->discount_percent,
                'vat_percent' => $order->vat_percent,
                'payment_term' => $order->payment_term ?: 'full_advance',
                'payment_due_days' => $order->payment_due_days,
                'deposit_percent' => $order->deposit_percent,
                'payment_note' => $order->payment_note,
                'payment_due_date' => $validated['payment_due_date'] ?? (($order->payment_term === 'debt' && !empty($order->payment_due_days)) ? now()->addDays((int) $order->payment_due_days)->toDateString() : null),
                'delivery_due_date' => $validated['delivery_due_date'] ?? null,
                'note' => $order->note,
                'status' => 'pending',
            ]);

            foreach ($order->items as $oi) {
                SalesOrderItem::create([
                    'sales_order_id' => $salesOrder->id,
                    'product_id' => $oi->product_id,
                    'quantity' => $oi->quantity,
                    'unit_price' => $oi->price,
                    'unit' => $oi->unit,
                ]);
            }

            $subTotal = (float) $order->items->sum(function ($i) {
                return (float) ($i->price ?? 0) * (int) ($i->quantity ?? 0);
            });
            $discountPercent = (float) ($order->discount_percent ?? 0);
            $vatPercent = (float) ($order->vat_percent ?? 8);
            $afterDiscount = max(0, $subTotal * (1 - ($discountPercent / 100)));
            $vatAmount = $afterDiscount * ($vatPercent / 100);
            $totalAmount = $afterDiscount + $vatAmount;

            if (Schema::hasTable('debts')) {
                Debt::create([
                    'sales_order_id' => $salesOrder->id,
                    'debt_code' => DocumentCodeGenerator::next(Debt::query(), 'debt_code', 'CN'),
                    'total_amount' => $totalAmount,
                    'paid_amount' => 0,
                    'remaining_amount' => $totalAmount,
                    'status' => 'unpaid',
                    'due_date' => ($order->payment_term === 'debt' && !empty($order->payment_due_days))
                        ? now()->addDays((int) $order->payment_due_days)->toDateString()
                        : null,
                    'last_paid_at' => null,
                    'note' => $order->payment_note,
                ]);
            }

            return $salesOrder;
        });

        ActivityLogger::log(
            'web_order.convert_to_sales_order',
            $order,
            'Sinh đơn hàng tự động từ đơn web',
            [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'sales_order_id' => $salesOrder->id,
                'sales_order_code' => $salesOrder->sales_order_code,
            ],
            $request
        );

        return redirect()->route('admin.sales-orders.show', $salesOrder)->with('success', 'Đã sinh đơn hàng tự động từ đơn web.');
    }

    private function isWebOrder(Order $order): bool
    {
        return str_starts_with((string) ($order->order_code ?? ''), 'OD');
    }

    private function isAllowedTransition(string $oldStatus, string $newStatus): bool
    {
        $allowedTransitions = [
            'pending' => ['pending', 'processing', 'cancelled'],
            'processing' => ['processing'],
            'cancelled' => ['cancelled'],
        ];

        return in_array($newStatus, $allowedTransitions[$oldStatus] ?? [$oldStatus], true);
    }
}
