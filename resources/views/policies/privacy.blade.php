@extends('layouts.user')

@section('title', 'Chính sách bảo mật thông tin - Vigilance')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chính sách bảo mật thông tin</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 fw-bold mb-3">Chính sách bảo mật thông tin</h1>

                    <h2 class="h6 fw-bold mt-3">a. Mục đích và phạm vi thu thập thông tin cá nhân</h2>
                    <p class="mb-2">Website thu thập các thông tin cá nhân như: Họ tên, Email, Số điện thoại, Địa chỉ khách hàng nhằm phục vụ quá trình đặt hàng trực tiếp trên website. Các thông tin này được sử dụng để xác nhận và đảm bảo quyền lợi cho khách hàng khi mua hàng.</p>
                    <p class="mb-3">Khách hàng có trách nhiệm tự bảo mật thông tin cá nhân và các hoạt động liên quan đến tài khoản hoặc hộp thư điện tử mà mình cung cấp. Nếu phát hiện hành vi sử dụng trái phép, vi phạm bảo mật hoặc thông tin bị lạm dụng, khách hàng cần thông báo ngay cho Vigilance để được hỗ trợ xử lý.</p>

                    <h2 class="h6 fw-bold mt-3">b. Phạm vi sử dụng thông tin</h2>
                    <p class="mb-2">Thông tin cá nhân của khách hàng được sử dụng với các mục đích sau:</p>
                    <ul class="mb-3">
                        <li>Cung cấp thông tin về sản phẩm, dịch vụ khi khách hàng yêu cầu.</li>
                        <li>Giải đáp, hỗ trợ các thắc mắc của khách hàng.</li>
                        <li>Không sử dụng thông tin cá nhân ngoài các mục đích đã nêu trên, ngoại trừ trường hợp có yêu cầu từ cơ quan pháp luật như Viện kiểm sát, Tòa án, Cơ quan công an liên quan đến hành vi vi phạm pháp luật.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">c. Thời gian lưu trữ thông tin</h2>
                    <p class="mb-3">Thông tin cá nhân của khách hàng sẽ được lưu trữ đến khi có yêu cầu hủy bỏ từ khách hàng. Trong mọi trường hợp, thông tin này sẽ được bảo mật trên máy chủ của website.</p>

                    <h2 class="h6 fw-bold mt-3">d. Các cá nhân hoặc tổ chức có quyền tiếp cận thông tin</h2>
                    <p class="mb-2">Khách hàng đồng ý rằng, trong những trường hợp cần thiết, các cá nhân hoặc tổ chức sau đây có thể tiếp cận thông tin cá nhân:</p>
                    <ul class="mb-3">
                        <li>Ban quản trị website.</li>
                        <li>Bên thứ ba có tích hợp dịch vụ với website.</li>
                        <li>Cơ quan nhà nước có thẩm quyền khi có yêu cầu.</li>
                        <li>Cố vấn tài chính, pháp lý, công ty kiểm toán.</li>
                        <li>Bên khiếu nại cung cấp bằng chứng về hành vi vi phạm của khách hàng.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">đ. Thông tin liên hệ đơn vị thu thập và quản lý thông tin</h2>
                    <p class="mb-2 fw-bold">CÔNG TY CỔ PHẦN VIGILANCE VIỆT NAM</p>
                    <ul class="mb-3">
                        <li>Địa chỉ: 96 Đường số 14, KDC Him Lam, Phường Tân Hưng,TP.HCM</li>
                        <li>Email: <a href="mailto:uancongly@gmail.com">uancongly@gmail.com</a></li>
                        <li>Điện thoại: <a href="tel:0982751075">0982751075</a></li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">e. Phương thức tiếp cận và chỉnh sửa thông tin cá nhân</h2>
                    <p class="mb-3">Khách hàng có thể tự kiểm tra, cập nhật, điều chỉnh hoặc yêu cầu hủy bỏ thông tin cá nhân của mình bằng cách liên hệ với Ban quản trị website. Nếu khách hàng phát hiện thông tin bị lộ hoặc bị sử dụng trái phép, cần gửi phản hồi đến Vigilance. Công ty sẽ xác minh và xử lý kịp thời tùy theo mức độ nghiêm trọng của sự việc.</p>

                    <h2 class="h6 fw-bold mt-3">f. Cam kết bảo mật thông tin cá nhân</h2>
                    <p class="mb-2">Vigilance cam kết bảo mật thông tin cá nhân khách hàng trên website theo các nguyên tắc sau:</p>
                    <ul class="mb-3">
                        <li>Chỉ thu thập và sử dụng thông tin khi có sự đồng ý của khách hàng, trừ trường hợp pháp luật yêu cầu khác.</li>
                        <li>Không chuyển giao, cung cấp hoặc tiết lộ thông tin cho bên thứ ba khi chưa có sự đồng ý của khách hàng.</li>
                        <li>Nếu máy chủ lưu trữ thông tin bị tấn công dẫn đến mất dữ liệu, Công ty sẽ thông báo cho cơ quan chức năng để xử lý và thông báo tình hình tới khách hàng.</li>
                    </ul>
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
                        <li><a class="is-active" href="{{ route('policies.privacy') }}">Chính sách bảo mật thông tin</a></li>
                        <li><a href="{{ route('policies.shipping') }}">Chính sách vận chuyển và giao nhận</a></li>
                        <li><a href="{{ route('policies.payment') }}">Phương thức thanh toán</a></li>
                        <li><a href="{{ route('policies.terms') }}">Điều khoản sử dụng</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
