<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\Notification;

class OrderAdminController extends Controller
{
    public function index()
    {
        $orders = Order::with(['items'])->orderByDesc('created_at')->paginate(20);
        return view('admin.orders.index', compact('orders'));
    }

    public function show($order)
    {
        $orderModel = Order::with(['items', 'user'])->find($order);
        if (!$orderModel) {
            return redirect()->route('admin.orders.index')->with('error', 'Đơn hàng đã bị xóa hoặc không tồn tại!');
        }

        return view('admin.orders.show', ['order' => $orderModel]);
    }

    public function update(Request $request, Order $order)
    {
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
