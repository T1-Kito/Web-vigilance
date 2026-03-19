@extends('layouts.user')

@section('title', 'Đặt hàng thành công')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-lg border-0">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill" style="font-size:3.5rem; color:#00B894;"></i>
                    </div>
                    <h2 class="fw-bold mb-3 text-success">Đặt hàng thành công!</h2>
                    <div class="mb-3">Cảm ơn <b>{{ $order->receiver_name }}</b> đã đặt hàng tại <b>Vigilance</b>.</div>
                    <div class="mb-3">Mã đơn hàng: <b>#{{ $order->id }}</b></div>
                    <div class="mb-3">Chúng tôi sẽ liên hệ xác nhận và giao hàng sớm nhất.</div>
                    <div class="mb-3">Nếu cần hỗ trợ, vui lòng gọi <a href="tel:0901234567" class="fw-bold text-primary">0901 234 567</a> hoặc chat với CSKH.</div>
                    <a href="/" class="btn btn-success fw-bold px-4">Về trang chủ</a>
                    <a href="/" class="btn btn-outline-primary fw-bold ms-2 px-4">Xem đơn hàng</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 