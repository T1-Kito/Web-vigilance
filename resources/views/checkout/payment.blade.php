@extends('layouts.user')

@section('title', 'Chọn phương thức thanh toán')

@section('content')
<div class="container py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <a href="{{ route('checkout.show') }}" class="btn btn-outline-secondary">&larr; Quay lại</a>
        <div class="ck-steps">
            <div class="ck-steps__item">1. Thông tin</div>
            <div class="ck-steps__sep"></div>
            <div class="ck-steps__item is-active">2. Thanh toán</div>
            <div class="ck-steps__sep"></div>
            <div class="ck-steps__item">3. Hoàn tất</div>
        </div>
    </div>

    <div class="mb-4">
        <h2 class="fw-bold mb-1" style="color:#0f172a;">Chọn phương thức thanh toán</h2>
        <div class="text-muted">Kiểm tra lại thông tin và xác nhận đặt hàng</div>
    </div>

    @php $total = 0; @endphp

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card ck-card mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Thông tin nhận hàng</h5>
                    <div class="ck-kv">
                        <div class="ck-k">Họ tên</div>
                        <div class="ck-v">{{ $validated['receiver_name'] }}</div>
                        <div class="ck-k">Số điện thoại</div>
                        <div class="ck-v">{{ $validated['receiver_phone'] }}</div>
                        <div class="ck-k">Địa chỉ</div>
                        <div class="ck-v">{{ $validated['receiver_address'] }}</div>
                        @if(!empty($validated['note']))
                            <div class="ck-k">Ghi chú</div>
                            <div class="ck-v">{{ $validated['note'] }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card ck-card mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Xác nhận đơn hàng qua Zalo</h5>
                    <div class="ck-zalo">
                        <div class="ck-zalo__title">Bước tiếp theo</div>
                        <div class="ck-zalo__desc">Sau khi bạn bấm <b>Xác nhận đơn hàng</b>, Admin sẽ liên hệ qua Zalo để xác nhận thông tin và chốt đơn.</div>
                        <div class="ck-zalo__note">Nếu cần gấp, bạn có thể nhắn Zalo cho shop để được xác nhận nhanh hơn.</div>
                        @php
                            $zaloPhone = config('app.zalo_phone');
                        @endphp
                        @if(!empty($zaloPhone))
                            <a class="btn btn-outline-primary fw-bold" target="_blank" rel="noopener" href="https://zalo.me/{{ preg_replace('/\D+/', '', (string) $zaloPhone) }}">Nhắn Zalo ngay</a>
                        @endif
                    </div>
                </div>
            </div>

            <form action="{{ route('checkout.confirm') }}" method="post" id="checkout-payment-form">
                @csrf
                <input type="hidden" name="payment_method" value="zalo">
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success fw-bold px-4 ck-btn-next">Xác nhận đơn hàng</button>
                </div>
            </form>
        </div>

        <div class="col-12 col-lg-5">
            <div class="ck-sticky">
                <div class="card ck-card">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">Tóm tắt đơn hàng</h5>
                            <span class="text-muted" style="font-weight:600;">{{ $cartItems->where('parent_cart_item_id', null)->count() }} sản phẩm</span>
                        </div>

                        <div class="ck-items">
                            @foreach($cartItems->where('parent_cart_item_id', null) as $item)
                                @php
                                    $product = $item->product;
                                    $finalPrice = $item->price;
                                    $subtotal = $finalPrice * $item->quantity;
                                    $total += $subtotal;
                                    $addons = $cartItems->where('parent_cart_item_id', $item->id);
                                @endphp
                                <div class="ck-item">
                                    <div class="ck-item__left">
                                        <img class="ck-item__img" src="{{ asset('images/products/' . $product->image) }}" alt="{{ $product->name }}">
                                        <div>
                                            <div class="ck-item__name">{{ $product->name }}</div>
                                            <div class="ck-item__meta">SL: {{ $item->quantity }}</div>
                                        </div>
                                    </div>
                                    <div class="ck-item__price">{{ number_format($subtotal, 0, ',', '.') }}đ</div>
                                </div>

                                @if($addons->count())
                                    @foreach($addons as $addon)
                                        @php
                                            $addonProduct = $addon->addonProduct ?? $addon->product;
                                            $addonPrice = $addon->price;
                                            $addonSubtotal = $addonPrice * $addon->quantity;
                                            $total += $addonSubtotal;
                                        @endphp
                                        <div class="ck-item ck-item--addon">
                                            <div class="ck-item__left">
                                                <img class="ck-item__img" src="{{ asset('images/products/' . ($addonProduct->image ?? '')) }}" alt="{{ $addonProduct->name ?? '' }}">
                                                <div>
                                                    <div class="ck-item__name">{{ $addonProduct->name ?? '' }} <span class="badge bg-warning text-dark">Mua kèm</span></div>
                                                    <div class="ck-item__meta">SL: {{ $addon->quantity }}</div>
                                                </div>
                                            </div>
                                            <div class="ck-item__price">{{ number_format($addonSubtotal, 0, ',', '.') }}đ</div>
                                        </div>
                                    @endforeach
                                @endif
                            @endforeach
                        </div>

                        <hr class="my-3">

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">Tạm tính</div>
                            <div class="fw-bold">{{ number_format($total, 0, ',', '.') }}đ</div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <div class="text-muted">Vận chuyển</div>
                            <div class="fw-bold text-success">Miễn phí</div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <div class="fw-bold">Tổng cộng</div>
                            <div class="fw-bold" style="font-size:1.15rem; color:#16a34a;">{{ number_format($total, 0, ',', '.') }}đ</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .ck-card { border: 1px solid rgba(15, 23, 42, 0.10); border-radius: 16px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05); }
    .ck-btn-next { border-radius: 12px; padding-top: 10px; padding-bottom: 10px; }

    .ck-steps { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .ck-steps__item { font-weight: 500; font-size: 0.9rem;background: rgba(15,23,42,0.04); border: 1px solid rgba(15,23,42,0.08); padding: 6px 10px; border-radius: 999px; }
    .ck-steps__item.is-active { color: #0f172a; background: rgba(37,99,235,0.10); border-color: rgba(37,99,235,0.25); }
    .ck-steps__sep { width: 26px; height: 2px; background: rgba(15,23,42,0.12); border-radius: 2px; }

    .ck-sticky { position: sticky; top: 86px; }
    .ck-items { display: grid; gap: 10px; max-height: 340px; overflow: auto; padding-right: 4px; }
    .ck-item { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 12px; border: 1px solid rgba(15,23,42,0.08); border-radius: 14px; background: #fff; }
    .ck-item--addon { background: #fffbe9; }
    .ck-item__left { display: flex; align-items: center; gap: 10px; min-width: 0; }
    .ck-item__img { width: 42px; height: 42px; object-fit: cover; border-radius: 10px; border: 1px solid rgba(15,23,42,0.10); background: #fff; }
    .ck-item__name { font-weight: 500; color: #0f172a; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 240px; }
    .ck-item__meta { font-size: 0.85rem; color: rgba(15,23,42,0.60); font-weight: 700; }
    .ck-item__price { font-weight: 900; color: #16a34a; }

    .ck-kv { display: grid; grid-template-columns: 140px 1fr; gap: 6px 14px; }
    .ck-k { font-weight: 500; }

    .ck-zalo { display: grid; gap: 8px; border: 1px solid rgba(15,23,42,0.10); border-radius: 14px; padding: 14px 14px; background: #fff; }
    .ck-zalo__title { font-weight: 900; color: #0f172a; }
    .ck-zalo__note { font-size: 0.92rem; color: rgba(15,23,42,0.60); font-weight: 700; }

    @media (max-width: 991.98px) {
        .ck-sticky { position: static; top: auto; }
        .ck-items { max-height: none; overflow: visible; }
    }

    @media (max-width: 575.98px) {
        .ck-steps__sep { display: none; }
        .ck-item__name { max-width: 190px; }
        .ck-btn-next { width: 100%; }
        .ck-kv { grid-template-columns: 1fr; }
    }
</style>
@endsection