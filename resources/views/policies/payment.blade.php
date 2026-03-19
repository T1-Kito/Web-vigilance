@extends('layouts.user')

@section('title', 'Phương thức thanh toán - Vigilance')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Phương thức thanh toán</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 fw-bold mb-3">Phương thức thanh toán</h1>

                    <h2 class="h6 fw-bold mt-3">CÁC HÌNH THỨC THANH TOÁN TẠI VIGILANCE</h2>
                    <p class="mb-3">Tại website Vigilance, chúng tôi cung cấp đến quý khách hàng 2 hình thức thanh toán như sau:</p>

                    <h2 class="h6 fw-bold mt-3">1. Thanh toán trực tiếp tại cửa hàng</h2>
                    <p class="mb-2">Quý khách có thể thanh toán trực tiếp tại địa chỉ công ty sau khi hoàn tất việc mua hàng:</p>
                    <ul class="mb-3">
                        <li>Địa chỉ: 96 Đường số 14, KDC Him Lam, Phường Tân Hưng, TP.HCM</li>
                        <li>Thời gian làm việc:</li>
                    </ul>
                    <ul class="mb-3">
                        <li>Thứ 2 đến Thứ 7: 8h00 - 17h.</li>
                        <li>Chủ nhật và ngày lễ: Nghỉ.</li>
                    </ul>
                    <p class="mb-3">Khi thanh toán trực tiếp, quý khách sẽ nhận hàng ngay tại cửa hàng.</p>

                    <h2 class="h6 fw-bold mt-3">2. Thanh toán qua tài khoản ngân hàng</h2>
                    <p class="mb-2">Quý khách có thể thanh toán từ xa qua tài khoản ngân hàng, đảm bảo an toàn và tiện lợi:</p>
                    <ul class="mb-3">
                        <li>Số tài khoản ACB (ACB Account) : 261223888 - Ngân hàng TMCP Á Châu - Chi nhánh Sài Gòn</li>
                        <li>Chủ tài khoản: CÔNG TY CỔ PHẦN VIGILANCE VIỆT NAM</li>
                        <li>Lưu ý: Trước khi thanh toán online, vui lòng liên hệ <a href="tel:0982751039">0982751039</a> để được xác nhận.</li>
                    </ul>

                    <p class="mb-2 fw-bold">Lưu ý khi thanh toán qua ngân hàng:</p>
                    <ul class="mb-3">
                        <li>Vui lòng ghi rõ Họ tên và Nội dung thanh toán (Ví dụ: "Nguyễn Văn A – Thanh toán đơn hàng số 12345").</li>
                        <li>Sau khi chuyển khoản, quý khách vui lòng thông báo với chúng tôi qua:</li>
                    </ul>
                    <ul class="mb-3">
                        <li>Điện thoại: <a href="tel:0982751075">0982 751 075</a></li>
                        <li>Email: <a href="mailto:uancongly@gmail.com">uancongly@gmail.com</a></li>
                    </ul>
                    <p class="mb-0">để được xác nhận thanh toán và xử lý đơn hàng nhanh chóng.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="fw-bold text-uppercase small mb-2 text-center">Danh mục trang</div>
                    <hr class="my-2">
                    <style>
                        .policy-page-nav { font-size: 0.95rem; line-height: 1.5; }
                        .policy-page-nav li { margin: 4px 0; }
                        .policy-page-nav a { color: #0f172a; text-decoration: none; display: block; padding: 8px 10px; border-radius: 10px; }
                        .policy-page-nav a:hover { background: #f1f5f9; color: #0f172a; }
                        .policy-page-nav a.is-active { background: #eef2ff; border: 1px solid rgba(59, 130, 246, 0.25); font-weight: 700; }
                    </style>
                    <ul class="list-unstyled mb-0 policy-page-nav">
                        <li><a href="{{ route('policies.warranty') }}">Chính sách bảo hành</a></li>
                        <li><a href="{{ route('policies.returns') }}">Chính sách đổi trả hàng hóa</a></li>
                        <li><a href="{{ route('policies.privacy') }}">Chính sách bảo mật thông tin</a></li>
                        <li><a href="{{ route('policies.shipping') }}">Chính sách vận chuyển và giao nhận</a></li>
                        <li><a class="is-active" href="{{ route('policies.payment') }}">Phương thức thanh toán</a></li>
                        <li><a href="{{ route('policies.terms') }}">Điều khoản sử dụng</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
