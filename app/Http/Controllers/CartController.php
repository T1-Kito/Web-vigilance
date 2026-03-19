<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CartController extends Controller
{
    private function guestCartKey(int $productId, ?int $colorId, bool $isAddon, ?string $parentKey): string
    {
        return implode(':', [
            $isAddon ? 'a' : 'm',
            $productId,
            $colorId ?: 0,
            $parentKey ?: '-',
        ]);
    }

    private function getGuestCart(): array
    {
        return (array) session()->get('guest_cart', []);
    }

    private function putGuestCart(array $cart): void
    {
        session()->put('guest_cart', $cart);
    }

    private function guestCartToCollection(array $cart)
    {
        $productIds = [];
        foreach ($cart as $row) {
            if (!empty($row['product_id'])) {
                $productIds[] = (int) $row['product_id'];
            }
        }
        $products = Product::query()->whereIn('id', array_values(array_unique($productIds)))->get()->keyBy('id');

        $items = [];
        foreach ($cart as $key => $row) {
            $product = $products->get((int) ($row['product_id'] ?? 0));
            if (!$product) {
                continue;
            }

            $item = new \stdClass();
            $item->id = $key;
            $item->user_id = null;
            $item->product_id = (int) $row['product_id'];
            $item->color_id = !empty($row['color_id']) ? (int) $row['color_id'] : null;
            $item->quantity = (int) ($row['quantity'] ?? 1);
            $item->price = (float) ($row['price'] ?? $product->price);
            $item->sale = isset($row['sale']) ? (float) $row['sale'] : null;
            $item->is_addon = (bool) ($row['is_addon'] ?? false);
            $item->parent_cart_item_id = $row['parent_cart_item_id'] ?? null;
            $item->addon_product_id = !empty($row['addon_product_id']) ? (int) $row['addon_product_id'] : null;
            $item->product = $product;
            $item->addonProduct = $product;
            $items[] = $item;
        }

        return collect($items);
    }

    public function addToCart(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);
        $quantity = $request->input('quantity', 1);
        $addons = $request->input('addons', []);
        $colorId = $request->input('color_id');
        $colorId = $colorId !== null && $colorId !== '' ? (int) $colorId : null;

        $colorPrice = null;
        $colorSale = null;
        if ($colorId) {
            $color = ProductColor::where('id', $colorId)->where('product_id', $productId)->first();
            if ($color && $color->price !== null) {
                // Nếu màu có giá riêng, không áp dụng giảm giá sản phẩm gốc
                $colorPrice = $color->price;
                $colorSale = null;
            } else {
                // Nếu màu không có giá riêng, áp dụng giảm giá sản phẩm gốc
                $colorPrice = $product->final_price;
                $colorSale = $product->sale;
            }
        }

        $user = Auth::user();
        if ($user) {
            // Thêm sản phẩm chính (DB cart)
            $cartItem = CartItem::where('user_id', $user->id)
                ->where('product_id', $productId)
                ->where('color_id', $colorId)
                ->first();
            if ($cartItem) {
                $cartItem->quantity += $quantity;
                if ($colorPrice !== null) {
                    $cartItem->price = $colorPrice;
                    $cartItem->sale = $colorSale;
                }
                $cartItem->save();
            } else {
                $cartItem = CartItem::create([
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'color_id' => $colorId,
                    'quantity' => $quantity,
                    'price' => $colorPrice !== null ? $colorPrice : $product->price,
                    'sale' => $colorSale !== null ? $colorSale : $product->sale,
                ]);
            }

            // Thêm sản phẩm mua kèm (DB cart)
            if (!empty($addons)) {
                foreach ($addons as $addonId) {
                    $addon = \App\Models\ProductAddon::with('addonProduct')->find($addonId);
                    if ($addon && $addon->addonProduct) {
                        $addonProduct = $addon->addonProduct;
                        $addonCartItem = CartItem::where('user_id', $user->id)
                            ->where('product_id', $addonProduct->id)
                            ->where('is_addon', true)
                            ->where('parent_cart_item_id', $cartItem->id)
                            ->first();

                        if ($addonCartItem) {
                            $addonCartItem->quantity += $quantity;
                            $addonCartItem->save();
                        } else {
                            CartItem::create([
                                'user_id' => $user->id,
                                'product_id' => $addonProduct->id,
                                'quantity' => $quantity,
                                'price' => $addon->addon_price,
                                'sale' => 0,
                                'is_addon' => true,
                                'parent_cart_item_id' => $cartItem->id,
                                'addon_product_id' => $addonProduct->id,
                            ]);
                        }
                    }
                }
            }
        } else {
            // Guest cart in session
            $cart = $this->getGuestCart();

            $mainKey = $this->guestCartKey((int) $productId, $colorId, false, null);
            if (!isset($cart[$mainKey])) {
                $cart[$mainKey] = [
                    'product_id' => (int) $productId,
                    'color_id' => $colorId,
                    'quantity' => 0,
                    'price' => $colorPrice !== null ? $colorPrice : $product->price,
                    'sale' => $colorSale !== null ? $colorSale : $product->sale,
                    'is_addon' => false,
                    'parent_cart_item_id' => null,
                    'addon_product_id' => null,
                ];
            }
            $cart[$mainKey]['quantity'] = (int) ($cart[$mainKey]['quantity'] ?? 0) + (int) $quantity;

            if (!empty($addons)) {
                foreach ($addons as $addonId) {
                    $addon = \App\Models\ProductAddon::with('addonProduct')->find($addonId);
                    if (!$addon || !$addon->addonProduct) {
                        continue;
                    }
                    $addonProduct = $addon->addonProduct;
                    $addonKey = $this->guestCartKey((int) $addonProduct->id, null, true, $mainKey);

                    if (!isset($cart[$addonKey])) {
                        $cart[$addonKey] = [
                            'product_id' => (int) $addonProduct->id,
                            'color_id' => null,
                            'quantity' => 0,
                            'price' => $addon->addon_price,
                            'sale' => 0,
                            'is_addon' => true,
                            'parent_cart_item_id' => $mainKey,
                            'addon_product_id' => (int) $addonProduct->id,
                        ];
                    }
                    $cart[$addonKey]['quantity'] = (int) ($cart[$addonKey]['quantity'] ?? 0) + (int) $quantity;
                }
            }

            $this->putGuestCart($cart);
        }
        
        $message = 'Đã thêm vào giỏ hàng!';
        if (!empty($addons)) {
            $message .= ' (bao gồm ' . count($addons) . 'sản phẩm mua kèm)';
        }

        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout.show')->with('success', $message);
        }

        return redirect()->route('cart.view')->with('success', $message);
    }

    public function viewCart()
    {
        $user = Auth::user();
        if ($user) {
            $cartItems = CartItem::where('user_id', $user->id)->get();
        } else {
            $cartItems = $this->guestCartToCollection($this->getGuestCart());
        }
        $categories = \App\Models\Category::with(['children' => function($q) { $q->with('children'); }])->whereNull('parent_id')->ordered()->get();
        return view('cart.index', compact('cartItems', 'categories'));
    }

    public function showCheckout()
    {
        $user = Auth::user();
        if ($user) {
            $cartItems = CartItem::where('user_id', $user->id)->get();
        } else {
            $cartItems = $this->guestCartToCollection($this->getGuestCart());
        }
        $categories = \App\Models\Category::with(['children' => function($q) { $q->with('children'); }])->whereNull('parent_id')->ordered()->get();
        return view('checkout.show', compact('cartItems', 'categories'));
    }

    public function postCheckoutInfo(Request $request)
    {
        $validated = $request->validate([
            'receiver_name' => 'required|string|max:100',
            'receiver_phone' => 'required|string|max:20',
            'receiver_city' => 'required|string|max:100',
            'receiver_district' => 'required|string|max:100',
            'receiver_ward' => 'required|string|max:100',
            'receiver_address_detail' => 'required|string|max:255',
            'receiver_address' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:255',
            'customer_tax_code' => 'nullable|string|max:50',
            'invoice_company_name' => 'nullable|string|max:255',
            'invoice_address' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'customer_contact_person' => 'nullable|string|max:100',
        ]);

        $receiverAddress = trim((string) ($validated['receiver_address'] ?? ''));
        if ($receiverAddress === '') {
            $parts = [
                $validated['receiver_address_detail'] ?? null,
                $validated['receiver_ward'] ?? null,
                $validated['receiver_district'] ?? null,
                $validated['receiver_city'] ?? null,
            ];
            $parts = array_values(array_filter(array_map(function ($v) {
                $v = is_string($v) ? trim($v) : '';
                return $v !== '' ? $v : null;
            }, $parts)));
            $receiverAddress = implode(', ', $parts);
        }

        $validated['receiver_address'] = $receiverAddress;
        session(['checkout_info' => $validated]);

        return redirect()->route('checkout.payment')
            ->with('success', 'Đã lưu thông tin giao hàng. Vui lòng xác nhận đặt hàng ở bước tiếp theo.');
    }

    public function showCheckoutPayment()
    {
        $user = Auth::user();
        if ($user) {
            $cartItems = CartItem::where('user_id', $user->id)->get();
        } else {
            $cartItems = $this->guestCartToCollection($this->getGuestCart());
        }

        $validated = session('checkout_info');
        if (!$validated || $cartItems->isEmpty()) {
            return redirect()->route('cart.view')->with('error', 'Thông tin đơn hàng không hợp lệ.');
        }

        $categories = \App\Models\Category::with(['children' => function($q) { $q->with('children'); }])->whereNull('parent_id')->ordered()->get();
        return view('checkout.payment', compact('validated', 'cartItems', 'categories'));
    }

    public function updateCart(Request $request, $itemId)
    {
        $user = Auth::user();
        if ($user) {
            $cartItem = CartItem::where('id', $itemId)->where('user_id', $user->id)->firstOrFail();
            $cartItem->quantity = max(1, (int)$request->input('quantity', 1));
            $cartItem->save();
            return back()->with('success', 'Cập nhật số lượng thành công!');
        }

        $cart = $this->getGuestCart();
        if (!isset($cart[$itemId])) {
            return back()->with('error', 'Sản phẩm không tồn tại trong giỏ hàng.');
        }
        $cart[$itemId]['quantity'] = max(1, (int) $request->input('quantity', 1));
        $this->putGuestCart($cart);
        return back()->with('success', 'Cập nhật số lượng thành công!');
    }

    public function removeFromCart($itemId)
    {
        $user = Auth::user();
        if ($user) {
            $cartItem = CartItem::where('id', $itemId)->where('user_id', $user->id)->firstOrFail();
            $cartItem->delete();
            return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng!');
        }

        $cart = $this->getGuestCart();
        if (!isset($cart[$itemId])) {
            return back()->with('error', 'Sản phẩm không tồn tại trong giỏ hàng.');
        }

        $parentKey = $cart[$itemId]['parent_cart_item_id'] ?? null;
        $isAddon = (bool) ($cart[$itemId]['is_addon'] ?? false);

        unset($cart[$itemId]);

        if (!$isAddon) {
            foreach ($cart as $key => $row) {
                if (($row['parent_cart_item_id'] ?? null) === $itemId) {
                    unset($cart[$key]);
                }
            }
        }

        if ($isAddon && $parentKey && !isset($cart[$parentKey])) {
            // parent was removed already, ok
        }

        $this->putGuestCart($cart);
        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng!');
    }

    public function confirmOrder(Request $request)
    {
        $request->validate([
            'payment_method' => 'nullable|in:zalo,cod,bank,momo',
        ]);
        $paymentMethod = $request->input('payment_method', 'zalo');
        $user = Auth::user();
        if ($user) {
            $cartItems = CartItem::where('user_id', $user->id)->get();
        } else {
            $cartItems = $this->guestCartToCollection($this->getGuestCart());
        }
        $checkoutInfo = session('checkout_info');
        if (!$checkoutInfo || $cartItems->isEmpty()) {
            return redirect()->route('cart.view')->with('error', 'Thông tin đơn hàng không hợp lệ.');
        }
        $orderCode = 'OD' . now()->format('ymd') . Str::upper(Str::random(6));

        // Lưu đơn hàng vào DB
        $order = \App\Models\Order::create([
            'user_id' => $user ? $user->id : null,
            'receiver_name' => $checkoutInfo['receiver_name'],
            'receiver_phone' => $checkoutInfo['receiver_phone'],
            'receiver_address' => $checkoutInfo['receiver_address'],
            'invoice_company_name' => $checkoutInfo['invoice_company_name'] ?? null,
            'invoice_address' => $checkoutInfo['invoice_address'] ?? null,
            'customer_tax_code' => $checkoutInfo['customer_tax_code'] ?? null,
            'customer_phone' => $checkoutInfo['customer_phone'] ?? null,
            'customer_email' => $checkoutInfo['customer_email'] ?? null,
            'customer_contact_person' => $checkoutInfo['customer_contact_person'] ?? null,
            'note' => $checkoutInfo['note'] ?? null,
            'payment_method' => $paymentMethod,
            'status' => 'pending',
            'order_code' => $orderCode,
        ]);

        foreach ($cartItems as $item) {
            \App\Models\OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'sale' => $item->sale,
                'color_id' => $item->color_id,
                'parent_order_item_id' => null, // Xử lý parent/child nếu có
            ]);
        }

        try {
            $adminsQuery = \App\Models\User::query()->where('role', 'admin');
            $superAdminEmail = trim(strtolower((string) env('SUPER_ADMIN_EMAIL', '')));
            if ($superAdminEmail !== '') {
                $adminsQuery->orWhereRaw('LOWER(email) = ?', [$superAdminEmail]);
            }

            $admins = $adminsQuery->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\OrderPlacedNotification($order, true));
            }
            if ($user) {
                $user->notify(new \App\Notifications\OrderPlacedNotification($order, false));
            }
        } catch (\Throwable $e) {
            Log::error('Order notification failed', [
                'order_id' => $order->id ?? null,
                'order_code' => $order->order_code ?? null,
                'user_id' => $user?->id,
                'error' => $e->getMessage(),
            ]);
        }
        
        if ($user) {
            CartItem::where('user_id', $user->id)->delete();
        } else {
            session()->forget('guest_cart');
        }
        session()->forget('checkout_info');

        // Guest needs session access to view quote page
        if (!$user) {
            $codes = (array) session()->get('guest_order_codes', []);
            $codes[] = (string) ($order->order_code ?: '');
            $codes = array_values(array_unique(array_filter(array_map('trim', $codes))));
            session()->put('guest_order_codes', $codes);
        }

        return redirect()->route('orders.quote', ['orderCode' => $order->order_code])
            ->with('success', 'Đặt hàng thành công! Admin sẽ liên hệ xác nhận đơn hàng của bạn trong 3-4 phút tới.');
    }
}