<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Support\ActivityLogger;
use App\Support\DocumentCodeGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class OrderAdminController extends Controller
{
    public function index(Request $request)
    {
        $type = trim((string) $request->query('type', 'orders'));
        $isQuote = $type === 'quote';

        $statusOptions = [
            '' => 'Tất cả trạng thái',
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];

        $ordersQuery = Order::with(['items'])->orderByDesc('created_at');

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $ordersQuery->where(function ($sub) use ($q) {
                $sub->where('order_code', 'like', '%' . $q . '%')
                    ->orWhere('receiver_name', 'like', '%' . $q . '%')
                    ->orWhere('customer_tax_code', 'like', '%' . $q . '%')
                    ->orWhere('receiver_phone', 'like', '%' . $q . '%')
                    ->orWhere('customer_phone', 'like', '%' . $q . '%');
            });
        }

        $customerName = trim((string) $request->query('customer_name', ''));
        if ($customerName !== '') {
            $ordersQuery->where('receiver_name', 'like', '%' . $customerName . '%');
        }

        $taxCode = trim((string) $request->query('tax_code', ''));
        if ($taxCode !== '') {
            $ordersQuery->where('customer_tax_code', 'like', '%' . $taxCode . '%');
        }

        $defaultStatus = $isQuote ? 'pending' : '';
        $status = trim((string) $request->query('status', $defaultStatus));
        if ($status !== '') {
            $ordersQuery->where('status', $status);
        }

        $orders = $ordersQuery->paginate(20)->withQueryString();

        return view('admin.orders.index', compact('orders', 'statusOptions', 'isQuote'));
    }

    public function create()
    {
        $paymentOptions = [
            'cod' => 'Thanh toán khi nhận hàng (COD)',
            'bank' => 'Chuyển khoản ngân hàng',
            'zalo' => 'ZaloPay / Zalo',
            'momo' => 'Ví MoMo',
        ];
        $statusOptions = [
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
        ];

        return view('admin.orders.create', compact('paymentOptions', 'statusOptions'));
    }

    /**
     * JSON cho dòng đơn: giá tham chiếu (admin có thể sửa đơn giá trên form).
     */
    public function lineOptions(Product $product)
    {
        return response()->json([
            'product_id' => $product->id,
            'name' => $product->name,
            'final_price' => (float) ($product->final_price ?? $product->price ?? 0),
        ]);
    }

    /**
     * Trả về JSON: thông tin khách + lịch sử mua hàng gần nhất
     * Dùng trên modal trong trang tạo đơn.
     */
    public function customerPurchaseHistory(Request $request)
    {
        $taxCode = trim((string) $request->query('tax_code', ''));
        $phone = trim((string) $request->query('phone', ''));

        $digits = preg_replace('/\D+/', '', $taxCode);
        if ($taxCode === '' && $phone === '') {
            return response()->json([
                'ok' => false,
                'message' => 'Thiếu MST hoặc SĐT để tra cứu lịch sử mua hàng.',
                'customer' => null,
                'orders' => [],
            ], 422);
        }

        $ordersQuery = Order::query()
            ->with(['items'])
            ->orderByDesc('created_at');

        $ordersQuery->where(function ($q) use ($taxCode, $phone, $digits) {
            if ($taxCode !== '') {
                $q->where('customer_tax_code', 'like', '%' . $taxCode . '%');
                if ($digits !== '') {
                    $q->orWhere('customer_tax_code', 'like', '%' . $digits . '%');
                }
                return;
            }

            if ($phone !== '') {
                $q->where('receiver_phone', 'like', '%' . $phone . '%');
                return;
            }
        });

        $orders = $ordersQuery->limit(6)->get();
        $first = $orders->first();

        $statusMap = [
            'pending' => ['label' => 'Chờ xử lý', 'badge' => 'warning'],
            'processing' => ['label' => 'Đang xử lý', 'badge' => 'warning'],
            'completed' => ['label' => 'Hoàn thành', 'badge' => 'success'],
            'cancelled' => ['label' => 'Đã hủy', 'badge' => 'danger'],
        ];

        $ordersPayload = $orders->map(function (Order $o) use ($statusMap) {
            $statusKey = (string) ($o->status ?? '');
            $meta = $statusMap[$statusKey] ?? ['label' => $statusKey, 'badge' => 'secondary'];
            $total = (float) $o->items->sum(function ($i) {
                return (float) ($i->price ?? 0) * (int) ($i->quantity ?? 0);
            });

            return [
                'id' => $o->id,
                'order_code' => (string) ($o->order_code ?? ''),
                'created_at' => $o->created_at?->format('d/m/Y H:i'),
                'status' => $statusKey,
                'status_label' => $meta['label'],
                'status_badge' => $meta['badge'],
                'items_count' => $o->items->count(),
                'total' => $total,
                'receiver_name' => (string) ($o->receiver_name ?? ''),
                'receiver_phone' => (string) ($o->receiver_phone ?? ''),
            ];
        })->values()->all();

        return response()->json([
            'ok' => true,
            'message' => 'OK',
            'customer' => $first ? [
                'name' => (string) ($first->receiver_name ?? ''),
                'phone' => (string) ($first->receiver_phone ?? ''),
                'tax_code' => (string) ($first->customer_tax_code ?? ''),
                'email' => (string) ($first->customer_email ?? ''),
                'invoice_company_name' => (string) ($first->invoice_company_name ?? ''),
                'invoice_address' => (string) ($first->invoice_address ?? ''),
                'receiver_address' => (string) ($first->receiver_address ?? ''),
            ] : null,
            'orders' => $ordersPayload,
        ]);
    }

    public function store(Request $request)
    {
        $quoteMode = (bool) $request->input('quote_mode', false);
        $validated = $request->validate([
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:50',
            'receiver_address' => 'required|string|max:2000',
            'invoice_company_name' => 'nullable|string|max:255',
            'invoice_address' => 'nullable|string|max:2000',
            'customer_tax_code' => 'nullable|string|max:50',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'customer_contact_person' => 'nullable|string|max:100',
            'note' => 'nullable|string|max:2000',
            'payment_method' => 'nullable|in:zalo,cod,bank,momo',
            'status' => 'required|in:pending,processing,completed,cancelled',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1|max:99999',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $lines = [];
        foreach ($validated['items'] as $idx => $row) {
            $product = Product::query()->find($row['product_id']);
            if (!$product) {
                throw ValidationException::withMessages([
                    "items.$idx.product_id" => 'Sản phẩm không tồn tại.',
                ]);
            }
            $lines[] = [
                'product' => $product,
                'quantity' => (int) $row['quantity'],
                'price' => round((float) $row['unit_price'], 2),
                'sale' => null,
                'color_id' => null,
            ];
        }

        $order = DB::transaction(function () use ($validated, $lines) {
            $orderCode = DocumentCodeGenerator::next(Order::query(), 'order_code', 'SO');

            $order = Order::create([
                'user_id' => null,
                'order_code' => $orderCode,
                'receiver_name' => $validated['receiver_name'],
                'receiver_phone' => $validated['receiver_phone'],
                'receiver_address' => $validated['receiver_address'],
                'invoice_company_name' => $validated['invoice_company_name'] ?? null,
                'invoice_address' => $validated['invoice_address'] ?? null,
                'customer_tax_code' => $validated['customer_tax_code'] ?? null,
                'customer_phone' => $validated['customer_phone'] ?? null,
                'customer_email' => $validated['customer_email'] ?? null,
                'customer_contact_person' => $validated['customer_contact_person'] ?? null,
                'note' => $validated['note'] ?? null,
                'payment_method' => $validated['payment_method'] ?? 'cod',
                'status' => $validated['status'],
            ]);

            foreach ($lines as $line) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $line['product']->id,
                    'quantity' => $line['quantity'],
                    'price' => $line['price'],
                    'sale' => $line['sale'],
                    'color_id' => $line['color_id'],
                    'parent_order_item_id' => null,
                ]);
            }

            return $order->fresh(['items']);
        });

        try {
            $adminsQuery = \App\Models\User::query()->where('role', 'admin');
            $superAdminEmail = trim(strtolower((string) env('SUPER_ADMIN_EMAIL', '')));
            if ($superAdminEmail !== '') {
                $adminsQuery->orWhereRaw('LOWER(email) = ?', [$superAdminEmail]);
            }
            foreach ($adminsQuery->get() as $admin) {
                $admin->notify(new \App\Notifications\OrderPlacedNotification($order, true));
            }
        } catch (\Throwable $e) {
            Log::error('Admin manual order notification failed', [
                'order_id' => $order->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }

        ActivityLogger::log('order.create', $order, 'Tạo đơn hàng thủ công: ' . ($order->order_code ?? ''), [
            'order_code' => $order->order_code,
            'status' => $order->status,
            'items' => $order->items()->count(),
        ], $request);

        $url = route('admin.orders.show', $order);
        if ($quoteMode) {
            $url .= '?type=quote';
        }

        return redirect($url)->with('success', $quoteMode ? 'Đã tạo báo giá thành công!' : 'Đã tạo đơn hàng thành công!');
    }

    public function show($order)
    {
        $orderModel = Order::with(['items', 'user'])->find($order);
        if (!$orderModel) {
            return redirect()->route('admin.orders.index')->with('error', 'Đơn hàng đã bị xóa hoặc không tồn tại!');
        }

        return view('admin.orders.show', ['order' => $orderModel]);
    }

    public function workflow(Order $order)
    {
        $order->load(['items.product']);

        $deliveries = \App\Models\Delivery::query()
            ->where('order_id', $order->id)
            ->orderByDesc('created_at')
            ->get();

        $invoices = Invoice::query()
            ->where('order_id', $order->id)
            ->orderByDesc('created_at')
            ->get();

        $totalOrdered = (int) $order->items->sum('quantity');
        $totalDelivered = (int) \App\Models\DeliveryItem::query()
            ->whereIn('order_item_id', $order->items->pluck('id')->all())
            ->sum('quantity');

        $hasDelivery = $deliveries->count() > 0;
        $hasInvoice = $invoices->count() > 0;

        $steps = [
            ['key' => 'order', 'label' => 'Đơn hàng', 'done' => true],
            ['key' => 'delivery', 'label' => 'Phiếu xuất kho', 'done' => $hasDelivery],
            ['key' => 'invoice', 'label' => 'Hóa đơn', 'done' => $hasInvoice],
            ['key' => 'payment', 'label' => 'Thu tiền/Công nợ', 'done' => false],
        ];

        return view('admin.orders.workflow', [
            'order' => $order,
            'deliveries' => $deliveries,
            'invoices' => $invoices,
            'totalOrdered' => $totalOrdered,
            'totalDelivered' => $totalDelivered,
            'steps' => $steps,
        ]);
    }

    public function update(Request $request, Order $order)
    {
        if (Schema::hasTable('deliveries') && \App\Models\Delivery::query()->where('order_id', $order->id)->exists()) {
            return back()->with('error', 'Đơn hàng đã có phiếu xuất kho, không được cập nhật trạng thái thủ công. Vui lòng xử lý qua quy trình xuất kho.');
        }

        $validated = $request->validate([
            'status' => 'nullable|in:pending,completed,cancelled,processing',

            'note' => 'nullable|string|max:255',

            'customer_tax_code' => 'nullable|string|max:50',
            'customer_phone' => 'nullable|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'customer_contact_person' => 'nullable|string|max:100',
            'staff_code' => 'nullable|string|max:100',
            'sales_name' => 'nullable|string|max:150',

            'items' => 'nullable|array',
            'items.*.unit' => 'nullable|string|max:50',
        ]);

        $before = [
            'status' => $order->status,
        ];

        $statusChanged = false;
        $oldStatus = (string) ($order->status ?? '');

        if (array_key_exists('status', $validated) && $validated['status'] !== null) {
            $order->status = $validated['status'];
            $order->save();
            $statusChanged = $oldStatus !== (string) $order->status;
        }

        $orderFill = collect($validated)->only([
            'note',
            'customer_tax_code',
            'customer_phone',
            'customer_email',
            'customer_contact_person',
            'staff_code',
            'sales_name',
        ])->toArray();

        if (!empty($orderFill)) {
            $order->forceFill($orderFill);
            $order->save();
        }

        if (!empty($validated['items']) && is_array($validated['items'])) {
            $order->loadMissing('items');
            foreach ($validated['items'] as $itemId => $itemData) {
                $item = $order->items->firstWhere('id', (int) $itemId);
                if (!$item) {
                    continue;
                }

                $unit = isset($itemData['unit']) ? trim((string) $itemData['unit']) : '';
                $item->unit = $unit !== '' ? $unit : null;
                $item->save();
            }
        }

        $after = [
            'status' => $order->fresh()->status,
        ];

        if ($statusChanged) {
            try {
                $order->loadMissing('user');
                if ($order->user) {
                    $order->user->notify(new \App\Notifications\OrderStatusUpdatedNotification(
                        $order,
                        $oldStatus,
                        (string) ($after['status'] ?? '')
                    ));
                } else {
                    $email = trim((string) ($order->customer_email ?? ''));
                    if ($email !== '') {
                        Notification::route('mail', $email)->notify(new \App\Notifications\OrderStatusUpdatedNotification(
                            $order,
                            $oldStatus,
                            (string) ($after['status'] ?? '')
                        ));
                    }
                }
            } catch (\Throwable $e) {
                // ignore notification failure
            }
        }
        ActivityLogger::log('order.update', $order, 'Cập nhật đơn hàng: ' . ($order->order_code ?? ''), [
            'before' => $before,
            'after' => $after,
        ], $request);

        $redirectTo = $request->input('redirect_to');
        if (is_string($redirectTo) && $redirectTo !== '') {
            return redirect($redirectTo)->with('success', 'Cập nhật đơn hàng thành công!');
        }

        return redirect()->route('admin.orders.show', $order)->with('success', 'Cập nhật đơn hàng thành công!');
    }

    public function destroy(Order $order)
    {
        try {
            ActivityLogger::log('order.delete', $order, 'Xóa đơn hàng: ' . ($order->order_code ?? ''), [
                'order_code' => $order->order_code ?? null,
                'status' => $order->status ?? null,
                'total_amount' => $order->total_amount ?? null,
            ]);
            // Xóa các order items trước
            $order->items()->delete();
            
            // Sau đó xóa order
            $order->delete();
            
            return redirect()->route('admin.orders.index')->with('success', 'Đơn hàng đã được xóa thành công!');
        } catch (\Exception $e) {
            return redirect()->route('admin.orders.index')->with('error', 'Có lỗi xảy ra khi xóa đơn hàng!');
        }
    }
}
