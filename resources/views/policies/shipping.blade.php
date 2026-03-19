@extends('layouts.user')

@section('title', 'Chính sách giao hàng - Vigilance')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chính sách giao hàng</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 fw-bold mb-3">Chính sách vận chuyển và giao nhận</h1>

                    <h2 class="h6 fw-bold mt-3">I. Hình Thức Giao Hàng</h2>

                    <h3 class="h6 fw-bold mt-3">1. Khách hàng nhận tại Văn phòng Vigilance</h3>
                    <div class="mb-3">
                        96 Đường số 14, KDC Him Lam, Phường Tân Hưng,TP.HCM
                    </div>

                    <h3 class="h6 fw-bold mt-3">2. Giao hàng tận nơi</h3>

                    <div class="fw-bold mt-2">Nội/ngoại thành TP.HCM:</div>
                    <ul class="mb-3">
                        <li>Thời gian giao hàng: Dự kiến trong vòng 6 tiếng (giờ hành chính).</li>
                        <li>Phương thức giao hàng: Nhân viên Vigilance thực hiện.</li>
                    </ul>

                    <div class="fw-bold mt-2">Các tỉnh và thành phố khác:</div>
                    <ul class="mb-3">
                        <li>Thời gian giao hàng: Từ 1-3 ngày.</li>
                        <li>Phương thức giao hàng: Đơn vị vận chuyển thứ 3 thực hiện.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">II. Bảng Giá Dịch Vụ Vận Chuyển</h2>

                    <div class="fw-bold mt-2">Đối với đơn hàng có giá trị dưới 3,000,000 VNĐ:</div>
                    <div class="mb-1 fw-bold">Phí giao hàng:</div>
                    <ul class="mb-3">
                        <li>Nội thành TP.HCM: 50,000 VNĐ.</li>
                        <li>Ngoại thành TP.HCM: 100,000 VNĐ.</li>
                    </ul>

                    <div class="fw-bold mt-2">Đối với đơn hàng có giá trị từ 3,000,000 VNĐ trở lên:</div>
                    <div class="mb-3">Giao hàng miễn phí trong bán kính 5Km.</div>

                    <h2 class="h6 fw-bold mt-3">III. Quy Định Giao Hàng</h2>

                    <div class="fw-bold mt-2">Thời gian giao hàng:</div>
                    <ul class="mb-3">
                        <li>Từ 8:30 - 17:30 (giờ hành chính).</li>
                        <li>Đối với yêu cầu giao hàng ngoài giờ, vui lòng thông báo tại thời điểm đặt hàng.</li>
                    </ul>

                    <div class="fw-bold mt-2">Trường hợp bất khả kháng:</div>
                    <ul class="mb-3">
                        <li>Nếu không thể giao hàng đúng thời gian dự kiến, Vigilance sẽ liên hệ khách hàng để thỏa thuận lại.</li>
                    </ul>

                    <div class="fw-bold mt-2">Khu vực ngoài TP.HCM:</div>
                    <ul class="mb-3">
                        <li>Hàng hóa sẽ được chuyển qua đơn vị vận chuyển thứ 3.</li>
                        <li>Khách hàng thanh toán phí vận chuyển (nếu có) trực tiếp cho đơn vị vận chuyển và thông báo lại cho Vigilance.</li>
                    </ul>

                    <div class="fw-bold mt-2">Hàng hóa cồng kềnh và chi phí bốc vác:</div>
                    <ul class="mb-3">
                        <li>Với hàng hóa cồng kềnh, Vigilance sẽ giao hàng tại kho.</li>
                        <li>Nếu yêu cầu giao hàng tận nơi, các chi phí phát sinh như bốc vác hoặc khênh hàng sẽ được thông báo trước khi thực hiện.</li>
                    </ul>

                    <div class="fw-bold mt-2">Chính sách bảo đảm hàng hóa:</div>
                    <ul class="mb-3">
                        <li>Tất cả sản phẩm đều là hàng chính hãng mới 100%, có đầy đủ hóa đơn, CO, CQ và được bảo hành chính thức.</li>
                        <li>Vui lòng kiểm tra hàng hóa kỹ trước khi nhận. Vigilance không chịu trách nhiệm với các sai lệch hình thức sau khi khách hàng đã ký nhận hàng.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">Thông Tin Liên Hệ và Thanh Toán</h2>

                    <div class="fw-bold mt-2">Hotline:</div>
                    <div class="mb-3"><a href="tel:0982751075">0982 751 075</a></div>
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
                        <li><a class="is-active" href="{{ route('policies.shipping') }}">Chính sách vận chuyển và giao nhận</a></li>
                        <li><a href="{{ route('policies.payment') }}">Phương thức thanh toán</a></li>
                        <li><a href="{{ route('policies.terms') }}">Điều khoản sử dụng</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
