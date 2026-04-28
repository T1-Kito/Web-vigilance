<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Order;
use App\Support\ActivityLogger;
use App\Support\DocumentCodeGenerator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryAdminController extends Controller
{
    public function index(Request $request)
    {
        $deliveriesQuery = Delivery::query()
            ->with(['order', 'salesOrder.invoices'])
            ->orderByDesc('created_at');

        $orderCode = trim((string) $request->query('order_code', ''));
        if ($orderCode !== '') {
            $deliveriesQuery->whereHas('order', function ($sub) use ($orderCode) {
                $sub->where('order_code', 'like', '%' . $orderCode . '%');
            });
        }

        $status = trim((string) $request->query('status', ''));
        if ($status !== '') {
            $deliveriesQuery->where('status', $status);
        }

        $deliveries = $deliveriesQuery->paginate(20)->withQueryString();

        return view('admin.deliveries.index', [
            'deliveries' => $deliveries,
            'filters' => [
                'order_code' => $orderCode,
                'status' => $status,
            ],
        ]);
    }

    public function createFromOrder(Order $order)
    {
        $order->load(['items.product']);

        $deliveredMap = DeliveryItem::query()
            ->whereIn('order_item_id', $order->items->pluck('id')->all())
            ->selectRaw('order_item_id, SUM(quantity) as qty')
            ->groupBy('order_item_id')
            ->pluck('qty', 'order_item_id');

        return view('admin.deliveries.create', [
            'order' => $order,
            'deliveredMap' => $deliveredMap,
        ]);
    }

    public function store(Request $request, Order $order)
    {
        $validated = $request->validate([
            'shipper_name' => ['nullable', 'string', 'max:255'],
            'shipper_phone' => ['nullable', 'string', 'max:50'],
            'note' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:0'],
        ]);

        $order->load('items');
        $orderItems = $order->items->keyBy('id');

        $deliveredMap = DeliveryItem::query()
            ->whereIn('order_item_id', $order->items->pluck('id')->all())
            ->selectRaw('order_item_id, SUM(quantity) as qty')
            ->groupBy('order_item_id')
            ->pluck('qty', 'order_item_id');

        $lines = [];
        foreach ($validated['items'] as $row) {
            $itemId = (int) $row['order_item_id'];
            $qty = (int) $row['quantity'];
            if ($qty <= 0) {
                continue;
            }

            $orderItem = $orderItems->get($itemId);
            if (!$orderItem) {
                continue;
            }

            $alreadyDelivered = (int) ($deliveredMap[$itemId] ?? 0);
            $remaining = max(0, (int) $orderItem->quantity - $alreadyDelivered);
            if ($remaining <= 0) {
                continue;
            }

            if ($qty > $remaining) {
                return back()->withInput()->with('error', 'Số lượng xuất vượt quá còn lại của sản phẩm.');
            }

            $lines[] = [
                'order_item_id' => $itemId,
                'product_id' => (int) $orderItem->product_id,
                'quantity' => $qty,
            ];
        }

        if (count($lines) === 0) {
            return back()->withInput()->with('error', 'Vui lòng nhập số lượng xuất hợp lệ.');
        }

        $delivery = DB::transaction(function () use ($request, $order, $validated, $lines) {
            $delivery = Delivery::create([
                'order_id' => $order->id,
                'delivery_code' => $this->nextDeliveryCode(),
                'status' => 'confirmed',
                'delivered_at' => now(),
                'shipper_name' => $validated['shipper_name'] ?? null,
                'shipper_phone' => $validated['shipper_phone'] ?? null,
                'note' => $validated['note'] ?? null,
            ]);

            foreach ($lines as $line) {
                DeliveryItem::create([
                    'delivery_id' => $delivery->id,
                    'order_item_id' => $line['order_item_id'],
                    'product_id' => $line['product_id'],
                    'quantity' => $line['quantity'],
                ]);
            }

            $totalOrdered = (int) $order->items()->sum('quantity');
            $totalDelivered = (int) DeliveryItem::query()
                ->whereIn('order_item_id', $order->items()->pluck('id')->all())
                ->sum('quantity');

            if ($totalDelivered >= $totalOrdered && $totalOrdered > 0) {
                $order->status = 'completed';
            } elseif ($totalDelivered > 0) {
                $order->status = 'processing';
            }
            $order->save();

            ActivityLogger::log(
                'delivery.create',
                $delivery,
                'Tạo phiếu xuất kho từ đơn hàng',
                [
                    'delivery_code' => $delivery->delivery_code,
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'items' => $lines,
                ],
                $request
            );

            return $delivery;
        });

        return redirect()->route('admin.deliveries.show', $delivery)->with('success', 'Đã tạo phiếu xuất kho thành công.');
    }

    public function show(Delivery $delivery)
    {
        $delivery->load([
            'order.items.product',
            'salesOrder.items.product',
            'salesOrder.quote',
            'items.product',
            'items.orderItem',
            'items.salesOrderItem',
        ]);

        return view('admin.deliveries.show', compact('delivery'));
    }

    public function print(Delivery $delivery)
    {
        $delivery->load(['order.items.product', 'salesOrder.items.product', 'items.product', 'items.orderItem', 'items.salesOrderItem']);

        return view('admin.deliveries.print', compact('delivery'));
    }

    public function destroy(Request $request, Delivery $delivery)
    {
        DB::transaction(function () use ($delivery) {
            $salesOrder = $delivery->salesOrder;
            $order = $delivery->order;

            $delivery->items()->delete();
            $delivery->delete();

            if ($salesOrder) {
                $totalOrdered = (int) $salesOrder->items()->sum('quantity');
                $totalDelivered = (int) DeliveryItem::query()
                    ->whereIn('sales_order_item_id', $salesOrder->items()->pluck('id')->all())
                    ->sum('quantity');

                if ($totalDelivered <= 0) {
                    $salesOrder->status = 'pending';
                } elseif ($totalDelivered < $totalOrdered) {
                    $salesOrder->status = 'processing';
                } else {
                    $salesOrder->status = 'completed';
                }
                $salesOrder->save();
            }

            if ($order) {
                $totalOrdered = (int) $order->items()->sum('quantity');
                $totalDelivered = (int) DeliveryItem::query()
                    ->whereIn('order_item_id', $order->items()->pluck('id')->all())
                    ->sum('quantity');

                if ($totalDelivered <= 0) {
                    $order->status = 'pending';
                } elseif ($totalDelivered < $totalOrdered) {
                    $order->status = 'processing';
                } else {
                    $order->status = 'completed';
                }
                $order->save();
            }
        });

        ActivityLogger::log(
            'delivery.delete',
            $delivery,
            'Xóa phiếu xuất kho',
            [
                'delivery_id' => $delivery->id,
                'delivery_code' => $delivery->delivery_code,
            ],
            $request
        );

        return redirect()->route('admin.deliveries.index')->with('success', 'Đã xóa phiếu xuất kho.');
    }

    private function nextDeliveryCode(): string
    {
        return DocumentCodeGenerator::next(Delivery::query(), 'delivery_code', 'PX');
    }
}
