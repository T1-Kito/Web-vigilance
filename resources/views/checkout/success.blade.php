@extends('layouts.user')

@section('title', 'Đặt hàng thành công')

@section('content')
@php
    $customerName = $order->receiver_name ?: 'quý khách';
    $phone = $order->receiver_phone ?: ($order->customer_phone ?: '');
    $hotline = config('app.hotline', '09xx.xxx.xxx');
@endphp

<div class="container py-4 py-lg-5">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <a href="{{ route('orders.history') }}" class="btn btn-outline-secondary">&larr; Quay lại</a>
        <div class="ck-steps">
            <div class="ck-steps__item">1. Thông tin</div>
            <div class="ck-steps__sep"></div>
            <div class="ck-steps__item">2. Thanh toán</div>
            <div class="ck-steps__sep"></div>
            <div class="ck-steps__item is-active">3. Hoàn tất</div>
        </div>
    </div>

    <div class="card success-card border-0 shadow-sm">
        <div class="card-body p-4 p-lg-5 text-center">
            <div class="success-icon mb-3">
                <i class="bi bi-check-circle-fill"></i>
            </div>

            <h2 class="fw-bold text-success mb-2">ĐẶT HÀNG THÀNH CÔNG!</h2>
            <p class="mb-1">Cảm ơn anh <strong>{{ $customerName }}</strong> đã tin tưởng Vigilance Việt Nam.</p>
            <p class="mb-4">Mã đơn hàng của bạn là: <strong>#{{ $order->order_code }}</strong></p>

            <div class="next-guide text-start mx-auto mb-4">
                <div class="fw-bold mb-2"><i class="bi bi-info-circle me-2"></i>Hướng dẫn tiếp theo</div>
                <ul class="mb-0 ps-3">
                    <li>Đơn hàng của bạn đã được chuyển đến bộ phận kinh doanh xử lý.</li>
                    <li>Chuyên viên của Vigilance sẽ liên hệ với anh qua số điện thoại {{ $phone ?: 'đã đăng ký' }} để chốt đơn.</li>
                    <li>Nếu cần hỗ trợ gấp, vui lòng gọi Hotline: {{ $hotline }}.</li>
                </ul>
            </div>

            <div class="d-flex flex-wrap justify-content-center gap-2">
                <a href="{{ url('/') }}" class="btn btn-success px-4">
                    <i class="bi bi-house-door me-1"></i> Về trang chủ
                </a>
                <a href="{{ route('orders.quote.pdf', ['orderCode' => $order->order_code]) }}" class="btn btn-outline-primary px-4">
                    <i class="bi bi-download me-1"></i> Tải xuống bản PDF báo giá
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .ck-steps { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
    .ck-steps__item { font-weight:500; font-size:.9rem; background:rgba(15,23,42,.04); border:1px solid rgba(15,23,42,.08); padding:6px 10px; border-radius:999px; }
    .ck-steps__item.is-active { color:#0f172a; background:rgba(37,99,235,.10); border-color:rgba(37,99,235,.25); }
    .ck-steps__sep { width:26px; height:2px; background:rgba(15,23,42,.12); border-radius:2px; }

    .success-card { border-radius:16px; border:1px solid rgba(15,23,42,.1); }
    .success-icon i { font-size:72px; color:#16a34a; }

    .next-guide {
        max-width: 760px;
        border:1px dashed #cbd5e1;
        border-radius:12px;
        padding:14px 16px;
        background:#f8fafc;
    }

    @media (max-width: 575.98px) {
        .ck-steps__sep { display:none; }
    }
</style>
@endsection
