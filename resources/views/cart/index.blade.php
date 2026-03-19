@extends('layouts.user')

@section('title', 'Giỏ hàng của bạn')

@section('content')
<div class="cart-mobile-topbar d-md-none">
    <div class="cart-mobile-topbar__inner">
        <button type="button" class="cart-mobile-topbar__btn" onclick="(function(){ if (window.history && window.history.length > 1) { window.history.back(); } else { window.location.href = '/'; } })()">
            <span class="cart-mobile-topbar__icon">←</span>
            <span>Quay lại</span>
        </button>
        <div class="cart-mobile-topbar__title">Giỏ hàng</div>
        <a class="cart-mobile-topbar__btn" href="/" style="text-decoration:none;">
            <span>Trang chủ</span>
        </a>
    </div>
</div>

<div class="container py-4 cart-page">
    <h2 class="fw-bold mb-4" style="color:#007BFF;">Giỏ hàng của bạn</h2>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($cartItems->isEmpty())
        <div class="alert alert-info">Giỏ hàng của bạn đang trống.</div>
    @else
    <div class="row justify-content-center">
        <div class="col-lg-10">
                @php $total = 0; @endphp
            @foreach($cartItems->where('parent_cart_item_id', null) as $item)
                    @php
                        $product = $item->product;
                    $finalPrice = $item->price;
                        $subtotal = $finalPrice * $item->quantity;
                        $total += $subtotal;
                    $addons = $cartItems->where('parent_cart_item_id', $item->id);
                    @endphp
                <div class="card mb-4 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center cart-item-row">
                            <a href="{{ route('product.show', $product->slug) }}" style="text-decoration:none;">
                                <img src="{{ asset('images/products/' . $product->image) }}" class="cart-item-image" style="width:70px; height:70px; object-fit:cover; border-radius:8px; transition:transform 0.3s ease; cursor:pointer;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                            </a>
                            <div class="ms-md-3 mt-2 mt-md-0 flex-grow-1 w-100 cart-item-info">
                                <div class="fw-bold" style="font-size:1.5em; font-weight:800;">
                                    <a href="{{ route('product.show', $product->slug) }}" style="text-decoration:none; color:inherit; transition:color 0.3s ease;" onmouseover="this.style.color='#007BFF'" onmouseout="this.style.color='inherit'">
                                        {{ $product->name }}
                                    </a>
                                </div>
                                <div style="color:#e53935; font-weight:700; font-size:1.1em;">{{ number_format($finalPrice, 0, ',', '.') }}đ</div>
                                <div class="mt-1" style="font-size:0.98em; color:#888;">Số lượng: {{ $item->quantity }}</div>
                                <div class="mt-1 fw-bold text-success">Thành tiền: {{ number_format($subtotal, 0, ',', '.') }}đ</div>
                            </div>
                            <div class="mt-3 mt-md-0 ms-md-3 cart-item-actions">
                                <div class="d-flex gap-2 justify-content-start justify-content-md-end flex-wrap">
                                <form method="post" action="{{ route('cart.update', $item->id) }}" class="d-flex align-items-center gap-2 cart-item-update">
                                    @csrf
                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" class="form-control cart-qty-input" style="width:70px; min-width:70px;">
                                    <button type="submit" class="btn btn-sm btn-outline-primary" style="white-space:nowrap; padding:6px 10px;">Cập nhật</button>
                                </form>
                                <form method="post" action="{{ route('cart.remove', $item->id) }}" onsubmit="return confirm('Xóa sản phẩm này khỏi giỏ hàng?')" class="cart-item-remove">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger cart-remove-btn" style="white-space:nowrap;">Xóa</button>
                                </form>
                                </div>
                            </div>
                        </div>
                        {{-- Block khuyến mãi --}}
                        <div class="mt-2" style="background:#f8fafc; border-radius:8px; padding:10px;">
                            <span class="text-danger"><i class="bi bi-gift"></i> Khuyến mãi hấp dẫn</span>
                            <ul class="mb-0" style="font-size:0.98em;">
                                <li>Khách hàng thân thiết: Tặng voucher 100.000đ</li>
                                <li>Khách hàng doanh nghiệp: Hỗ trợ xuất hóa đơn VAT</li>
                            </ul>
                        </div>
                        {{-- Block sản phẩm mua kèm --}}
                        @if($addons->count())
                            <div class="mt-3 p-3" style="background:#fffbe9; border-radius:8px;">
                                <div class="fw-bold mb-2" style="color:#e67e22;">Bạn đang mua kèm {{ $addons->count() }} sản phẩm:</div>
                                @foreach($addons as $addon)
                                    @php
                                        $addonProduct = $addon->addonProduct ?? $addon->product;
                                        $addonPrice = $addon->price;
                                        $addonSubtotal = $addonPrice * $addon->quantity;
                                        $total += $addonSubtotal;
                                    @endphp
                                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center mb-2 cart-item-row" style="border-bottom:1px dashed #ffe082; padding-bottom: 10px;">
                                        <a href="{{ route('product.show', $addonProduct->slug ?? '') }}" style="text-decoration:none;">
                                            <img src="{{ asset('images/products/' . ($addonProduct->image ?? '')) }}" class="cart-item-image" style="width:48px; height:48px; object-fit:cover; border-radius:8px; transition:transform 0.3s ease; cursor:pointer;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                        </a>
                                        <div class="ms-md-2 mt-2 mt-md-0 flex-grow-1 cart-item-info" style="min-width: 0;">
                                            <span class="badge bg-warning text-dark">Mua kèm</span>
                                            <a href="{{ route('product.show', $addonProduct->slug ?? '') }}" style="text-decoration:none; color:inherit; transition:color 0.3s ease;" onmouseover="this.style.color='#007BFF'" onmouseout="this.style.color='inherit'">
                                                {{ $addonProduct->name ?? '' }}
                                            </a>
                                            <span style="color:#e53935; font-weight:700;">{{ number_format($addonPrice, 0, ',', '.') }}đ</span>
                                            <span style="font-size:0.98em; color:#888;">x {{ $addon->quantity }}</span>
                                            <span class="fw-bold text-success ms-2">{{ number_format($addonSubtotal, 0, ',', '.') }}đ</span>
                                        </div>
                                        <div class="mt-3 mt-md-0 ms-md-2 cart-item-actions">
                                            <div class="d-flex gap-2 justify-content-start justify-content-md-end flex-wrap">
                                            <form method="post" action="{{ route('cart.update', $addon->id) }}" class="d-flex align-items-center gap-2 cart-item-update">
                                                @csrf
                                                <input type="number" name="quantity" value="{{ $addon->quantity }}" min="1" class="form-control cart-qty-input" style="width:60px; min-width:60px;">
                                                <button type="submit" class="btn btn-sm btn-outline-primary" style="white-space:nowrap; padding:6px 10px;">Cập nhật</button>
                                            </form>
                                            <form method="post" action="{{ route('cart.remove', $addon->id) }}" onsubmit="return confirm('Xóa sản phẩm này khỏi giỏ hàng?')" class="cart-item-remove">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger cart-remove-btn" style="white-space:nowrap;">Xóa</button>
                                            </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            <div class="text-end mt-4">
                <div class="fw-bold" style="font-size:1.2em;">Tổng cộng: <span class="text-success">{{ number_format($total, 0, ',', '.') }}đ</span></div>
                <form action="{{ route('checkout.show') }}" method="get" style="display:inline;">
                    <button type="submit" class="btn btn-lg fw-bold mt-2 cart-checkout-btn" style="color:#fff; background:#00B894; border:1.5px solid #00B894;">Tiến hành đặt hàng</button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
.cart-mobile-topbar {
    position: sticky;
    top: 0;
    z-index: 1030;
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
}
.cart-mobile-topbar__inner {
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 0 12px;
}

.cart-mobile-topbar__btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: 1px solid #e5e7eb;
    background: #f8fafc;
    color: #111827;
    padding: 8px 10px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 0.95rem;
    white-space: nowrap;
}

.cart-mobile-topbar__icon {
    font-size: 1.1rem;
    line-height: 1;
}

.cart-mobile-topbar__title {
    font-weight: 900;
    color: #111827;
    font-size: 1rem;
}

@media (max-width: 575.98px) {
    .cart-page {
        padding-top: 16px !important;
    }
}

@media (max-width: 575.98px) {
    .cart-checkout-btn {
        width: 100%;
    }
}

.cart-item-image {
    flex: 0 0 auto;
}

.cart-item-actions {
    flex: 0 0 auto;
}

.cart-item-info {
    min-width: 0;
}

@media (max-width: 767.98px) {
    .cart-item-actions {
        width: 100%;
    }
}

@media (min-width: 768px) {
    .cart-item-actions {
        min-width: 220px;
        width: auto;
    }
    .cart-item-update {
        flex: 0 0 auto;
    }
    .cart-item-remove {
        flex: 0 0 auto;
    }
    .cart-remove-btn {
        min-width: 64px;
    }
}
</style>
@endsection