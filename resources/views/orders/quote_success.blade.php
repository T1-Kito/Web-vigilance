@extends('layouts.user')

@section('title', 'Đặt hàng thành công')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="fw-bold mb-2" style="color:#00B894;">Đặt hàng thành công</h4>
                    <div class="text-muted mb-3">Cảm ơn bạn đã xác nhận đơn hàng.</div>

                    <div class="mb-2"><b>Mã đơn:</b> {{ $order->order_code ?? ("VK" . str_pad($order->id, 6, '0', STR_PAD_LEFT)) }}</div>
                    <div class="mb-2"><b>Người nhận:</b> {{ $order->receiver_name }}</div>
                    <div class="mb-2"><b>SĐT:</b> {{ $order->receiver_phone }}</div>
                    <div class="mb-3"><b>Địa chỉ:</b> {{ $order->receiver_address }}</div>

                    <div class="d-flex flex-wrap gap-2">
                        <a class="btn btn-outline-primary" href="{{ route('orders.quote', ['orderCode' => $order->order_code]) }}" target="_blank" rel="noopener">Xem lại báo giá</a>
                        <a class="btn btn-outline-secondary" href="{{ route('orders.lookup') }}">Tra cứu đơn hàng</a>
                        <a class="btn btn-primary" href="{{ route('home') }}">Về trang chủ</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
