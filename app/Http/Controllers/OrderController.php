<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    private function guestOrderCodes(): array
    {
        return array_values(array_unique(array_filter((array) session()->get('guest_order_codes', []))));
    }

    private function authorizeOrderView(Order $order): void
    {
        return;
    }

    private function authorizeOrderConfirm(Order $order): void
    {
        $user = Auth::user();

        if ($user) {
            if (($user->role ?? null) === 'admin') {
                return;
            }
            if ((int) $order->user_id !== (int) $user->id) {
                abort(403);
            }
            return;
        }

        if ($order->user_id) {
            abort(403);
        }

        $codes = $this->guestOrderCodes();
        if (!in_array($order->order_code, $codes, true)) {
            abort(403);
        }
    }

    public function index()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)->orderByDesc('created_at')->get();
        $categories = \App\Models\Category::with(['children' => function($q) { $q->with('children'); }])->whereNull('parent_id')->ordered()->get();
        return view('orders.index', compact('orders', 'categories'));
    }

    public function show(Order $order)
    {
        $user = Auth::user();
        
        // Kiểm tra xem đơn hàng có tồn tại không
        if (!$order) {
            abort(404, 'Đơn hàng không tồn tại');
        }
        
        // Kiểm tra xem user có tồn tại không
        if (!$user) {
            abort(401, 'Vui lòng đăng nhập để xem đơn hàng');
        }
        
        // So sánh với kiểu dữ liệu chính xác và thêm debug
        $orderUserId = (int)$order->user_id;
        $currentUserId = (int)$user->id;
        
        if ($orderUserId !== $currentUserId) {
            // Log để debug
            \Log::info('Order access denied', [
                'order_id' => $order->id,
                'order_user_id' => $orderUserId,
                'current_user_id' => $currentUserId,
                'user_email' => $user->email
            ]);
            
            abort(403, 'Bạn không có quyền xem đơn hàng này. Order User ID: ' . $orderUserId . ', Current User ID: ' . $currentUserId);
        }
        
        $categories = \App\Models\Category::with(['children' => function($q) { $q->with('children'); }])->whereNull('parent_id')->ordered()->get();
        return view('orders.show', compact('order', 'categories'));
    }

    public function lookup(Request $request)
    {
        $categories = \App\Models\Category::with(['children' => function($q) { $q->with('children'); }])->whereNull('parent_id')->ordered()->get();
        return view('orders.lookup', [
            'categories' => $categories,
            'order' => null,
        ]);
    }

    public function lookupSearch(Request $request)
    {
        $validated = $request->validate([
            'order_code' => ['required', 'string', 'max:50'],
        ], [
            'order_code.required' => 'Vui lòng nhập mã đơn hàng',
        ]);

        $orderCode = trim($validated['order_code']);

        $order = Order::with(['items.product'])
            ->where('order_code', $orderCode)
            ->first();

        if (!$order) {
            return back()->withInput()->with('lookup_error', 'Không tìm thấy đơn hàng với mã đã nhập.');
        }

        if (!Auth::check()) {
            $codes = (array) session()->get('guest_order_codes', []);
            $codes[] = (string) ($order->order_code ?: '');
            $codes = array_values(array_unique(array_filter(array_map('trim', $codes))));
            session()->put('guest_order_codes', $codes);
        }

        $categories = \App\Models\Category::with(['children' => function($q) { $q->with('children'); }])->whereNull('parent_id')->ordered()->get();
        return view('orders.lookup', [
            'categories' => $categories,
            'order' => $order,
        ]);
    }

    public function history(Request $request)
    {
        $categories = \App\Models\Category::with(['children' => function($q) { $q->with('children'); }])->whereNull('parent_id')->ordered()->get();

        $user = Auth::user();
        if ($user) {
            $orders = Order::with(['items.product'])
                ->where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->get();

            return view('orders.history', [
                'categories' => $categories,
                'orders' => $orders,
                'isGuest' => false,
            ]);
        }

        $codes = (array) session()->get('guest_order_codes', []);
        $codes = array_values(array_unique(array_filter(array_map('trim', $codes))));

        $orders = collect();
        if (!empty($codes)) {
            $orders = Order::with(['items.product'])
                ->whereIn('order_code', $codes)
                ->orderByDesc('created_at')
                ->get();
        }

        return view('orders.history', [
            'categories' => $categories,
            'orders' => $orders,
            'isGuest' => true,
        ]);
    }

    public function quote(string $orderCode)
    {
        $quote = Quote::with(['items.product', 'user'])->where('quote_code', $orderCode)->first();
        if ($quote) {
            $categories = \App\Models\Category::with(['children' => function($q) { $q->with('children'); }])->whereNull('parent_id')->ordered()->get();
            return view('orders.quote', ['order' => $quote, 'categories' => $categories]);
        }

        $order = Order::with(['items.product', 'user'])->where('order_code', $orderCode)->first();
        if (!$order) {
            return redirect()->route('orders.lookup')->with('lookup_error', 'Đơn hàng đã bị xóa hoặc không tồn tại.');
        }
        $this->authorizeOrderView($order);

        $categories = \App\Models\Category::with(['children' => function($q) { $q->with('children'); }])->whereNull('parent_id')->ordered()->get();
        return view('orders.quote', compact('order', 'categories'));
    }

    public function confirmFromQuote(Request $request, string $orderCode)
    {
        $order = Order::where('order_code', $orderCode)->first();
        if (!$order) {
            return redirect()->route('orders.lookup')->with('lookup_error', 'Đơn hàng đã bị xóa hoặc không tồn tại.');
        }
        $this->authorizeOrderConfirm($order);

        if ($order->status !== 'pending') {
            return redirect()->route('orders.quote', ['orderCode' => $order->order_code])->with('error', 'Đơn hàng không ở trạng thái chờ xử lý.');
        }

        $order->status = 'processing';
        $order->save();

        return redirect()->route('orders.quote.success', ['orderCode' => $order->order_code]);
    }

    public function quoteSuccess(string $orderCode)
    {
        $order = Order::where('order_code', $orderCode)->first();
        if (!$order) {
            return redirect()->route('orders.lookup')->with('lookup_error', 'Đơn hàng đã bị xóa hoặc không tồn tại.');
        }
        $this->authorizeOrderView($order);

        $categories = \App\Models\Category::with(['children' => function($q) { $q->with('children'); }])->whereNull('parent_id')->ordered()->get();
        return view('orders.quote_success', compact('order', 'categories'));
    }

    public function quotePdf(string $orderCode)
    {
        $quote = Quote::with(['items.product', 'user'])->where('quote_code', $orderCode)->first();
        if ($quote) {
            $pdf = Pdf::loadView('orders.quote_pdf', [
                'order' => $quote,
            ])->setPaper('a4', 'portrait');

            $filename = 'bao-gia-' . ($quote->quote_code ?: ('quote-' . $quote->id)) . '-' . now()->format('YmdHis') . '.pdf';

            return $pdf->download($filename);
        }

        $order = Order::with(['items.product', 'user'])->where('order_code', $orderCode)->first();
        if (!$order) {
            return redirect()->route('orders.lookup')->with('lookup_error', 'Đơn hàng đã bị xóa hoặc không tồn tại.');
        }

        $this->authorizeOrderView($order);

        $pdf = Pdf::loadView('orders.quote_pdf', [
            'order' => $order,
        ])->setPaper('a4', 'portrait');

        $filename = 'bao-gia-' . ($order->order_code ?: ('order-' . $order->id)) . '-' . now()->format('YmdHis') . '.pdf';

        return $pdf->download($filename);
    }
}
