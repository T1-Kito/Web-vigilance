<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Order;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        if ($user) {
            $notifications = $user->notifications()->latest()->paginate(20);
            $guestOrders = collect();

            return view('notifications.index', compact('notifications', 'guestOrders'));
        }

        $codes = (array) $request->session()->get('guest_order_codes', []);
        $codes = array_values(array_unique(array_filter(array_map('trim', $codes))));

        $guestOrders = collect();
        if (!empty($codes)) {
            $guestOrders = Order::query()
                ->whereIn('order_code', $codes)
                ->orderByDesc('created_at')
                ->get();
        }

        $notifications = null;
        return view('notifications.index', compact('notifications', 'guestOrders'));
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
        if (is_string($url) && $url !== '') {
            return redirect($url);
        }

        return back();
    }
}
