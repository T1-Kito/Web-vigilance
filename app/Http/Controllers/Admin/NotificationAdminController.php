<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationAdminController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest()->paginate(30);

        $orderIds = $notifications
            ->getCollection()
            ->map(fn($n) => data_get($n->data, 'order_id'))
            ->filter(fn($id) => is_int($id) || (is_string($id) && ctype_digit($id)))
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $existingOrderIds = [];
        if (!empty($orderIds)) {
            $existingOrderIds = Order::query()->whereIn('id', $orderIds)->pluck('id')->all();
        }
        $existingOrderIds = array_fill_keys($existingOrderIds, true);

        return view('admin.notifications.index', compact('notifications', 'existingOrderIds'));
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }

    public function markRead(Request $request, string $id): RedirectResponse
    {
        $user = $request->user();
        $n = $user->notifications()->whereKey($id)->firstOrFail();
        if ($n->read_at === null) {
            $n->markAsRead();
        }

        $url = data_get($n->data, 'url');
        $orderId = data_get($n->data, 'order_id');
        $orderCode = data_get($n->data, 'order_code');

        if (is_string($url) && $url !== '' && is_string($orderId) && ctype_digit($orderId)) {
            $orderId = (int) $orderId;
        }

        if (is_string($orderCode)) {
            $orderCode = trim($orderCode);
            if ($orderCode === '') {
                $orderCode = null;
            }
        } else {
            $orderCode = null;
        }

        if (is_string($url) && $url !== '' && is_int($orderId) && str_contains($url, '/cp-admin/orders/')) {
            $exists = Order::query()->whereKey($orderId)->exists();
            if (!$exists) {
                return redirect()->route('admin.notifications.index')->with('error', 'Đơn hàng đã bị xóa hoặc không tồn tại!');
            }
        }

        if (is_string($url) && $url !== '' && (str_contains($url, '/bao-gia/') || $orderCode !== null)) {
            $query = Order::query();
            if ($orderCode !== null) {
                $query->where('order_code', $orderCode);
            } elseif (is_int($orderId)) {
                $query->whereKey($orderId);
            }

            if (!$query->exists()) {
                return redirect()->route('admin.notifications.index')->with('error', 'Đơn hàng đã bị xóa hoặc không tồn tại!');
            }
        }

        if (is_string($url) && $url !== '') {
            return redirect($url);
        }

        return back();
    }
}
