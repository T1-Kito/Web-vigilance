@extends('layouts.user')

@section('title', 'Điều khoản sử dụng - Vigilance')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Điều khoản sử dụng</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 fw-bold mb-3">Điều khoản sử dụng</h1>

                    <p class="mb-3">Khi truy cập và sử dụng website của Vigilance, Quý khách đồng ý tuân thủ các điều khoản dưới đây. Vui lòng đọc kỹ trước khi mua hàng hoặc sử dụng các dịch vụ trên website.</p>

                    <h2 class="h6 fw-bold mt-3">Thông tin doanh nghiệp</h2>
                    <ul class="mb-3">
                        <li><b>Tên công ty:</b> CÔNG TY CỔ PHẦN VIGILANCE VIỆT NAM</li>
                        <li><b>Mã số thuế:</b> {{ env('COMPANY_TAX_CODE', '0318231312') }}</li>
                        <li><b>Địa chỉ:</b> 96 Đường số 14, KDC Him Lam, Phường Tân Hưng, TP.HCM</li>
                        <li><b>Số điện thoại:</b> <a href="tel:0982751075">0982 751 075</a></li>
                        <li><b>Email:</b> <a href="mailto:uancongly@gmail.com">uancongly@gmail.com</a></li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">1. Phạm vi áp dụng</h2>
                    <ul class="mb-3">
                        <li>Điều khoản này áp dụng cho tất cả khách hàng truy cập website, đặt hàng, sử dụng dịch vụ tư vấn, liên hệ và các tính năng liên quan.</li>
                        <li>Trong trường hợp có thỏa thuận riêng bằng văn bản giữa Vigilance và khách hàng (ví dụ hợp đồng dự án/B2B), nội dung thỏa thuận riêng sẽ được ưu tiên áp dụng.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">2. Giải thích thuật ngữ</h2>
                    <ul class="mb-3">
                        <li><b>Vigilance</b>: đơn vị sở hữu và vận hành website, cung cấp sản phẩm/dịch vụ được giới thiệu trên website.</li>
                        <li><b>Khách hàng/Quý khách</b>: cá nhân/tổ chức truy cập website, mua hàng hoặc sử dụng dịch vụ.</li>
                        <li><b>Đơn hàng</b>: yêu cầu mua sản phẩm/dịch vụ được khách hàng tạo qua website hoặc các kênh được Vigilance hỗ trợ.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">3. Quy định về tài khoản và thông tin cung cấp</h2>
                    <ul class="mb-3">
                        <li>Khách hàng chịu trách nhiệm về tính chính xác của thông tin cung cấp (họ tên, số điện thoại, địa chỉ nhận hàng, email...).</li>
                        <li>Trường hợp thông tin không chính xác dẫn đến giao hàng thất bại, chậm trễ hoặc phát sinh chi phí, Vigilance có quyền từ chối xử lý hoặc tính phí phát sinh (nếu có).</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">4. Giá bán và thông tin sản phẩm</h2>
                    <ul class="mb-3">
                        <li>Giá sản phẩm hiển thị trên website có thể thay đổi theo thời điểm, chương trình khuyến mãi hoặc chính sách giá.</li>
                        <li>Vigilance nỗ lực đảm bảo thông tin sản phẩm (hình ảnh, thông số, mô tả) là chính xác; tuy nhiên có thể có sai sót ngoài ý muốn. Trong trường hợp sai sót ảnh hưởng đến việc đặt hàng, Vigilance sẽ liên hệ để xác nhận/điều chỉnh hoặc hủy đơn theo yêu cầu.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">5. Quy trình đặt hàng và xác nhận đơn hàng</h2>
                    <ul class="mb-3">
                        <li>Khách hàng đặt hàng qua website và nhận thông báo tiếp nhận đơn. Việc tiếp nhận không đồng nghĩa với việc đơn hàng đã được xác nhận thành công.</li>
                        <li>Đơn hàng được xác nhận khi Vigilance liên hệ xác minh thông tin và/hoặc xác nhận tình trạng hàng hóa, phương thức thanh toán, thời gian giao hàng.</li>
                        <li>Vigilance có quyền từ chối hoặc hủy đơn trong các trường hợp: thông tin đặt hàng không đầy đủ/không xác thực, hết hàng, lỗi hệ thống, nghi ngờ gian lận, hoặc các trường hợp bất khả kháng.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">6. Thanh toán</h2>
                    <ul class="mb-3">
                        <li>Khách hàng có thể thanh toán theo các phương thức được công bố tại trang <a href="{{ route('policies.payment') }}">Phương thức thanh toán</a>.</li>
                        <li>Với các đơn hàng chuyển khoản, khách hàng cần ghi rõ nội dung chuyển khoản theo hướng dẫn (nếu có) để việc đối soát được nhanh chóng.</li>
                        <li>Trong trường hợp thanh toán không thành công hoặc không xác nhận được giao dịch, Vigilance có quyền tạm dừng xử lý đơn hàng cho đến khi được xác minh.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">7. Giao hàng, lắp đặt và nghiệm thu</h2>
                    <ul class="mb-3">
                        <li>Thời gian giao hàng dự kiến phụ thuộc vào khu vực, tình trạng hàng và các yếu tố khách quan (thời tiết, sự cố vận chuyển...).</li>
                        <li>Khách hàng có trách nhiệm kiểm tra tình trạng sản phẩm, số lượng và phụ kiện khi nhận hàng. Nếu phát hiện bất thường, vui lòng phản hồi ngay tại thời điểm nhận hàng hoặc liên hệ sớm nhất để được hỗ trợ.</li>
                        <li>Đối với đơn hàng có lắp đặt/thi công, việc nghiệm thu có thể thực hiện theo biên bản nghiệm thu hoặc xác nhận hoàn thành giữa hai bên.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">8. Đổi trả và hoàn tiền</h2>
                    <ul class="mb-3">
                        <li>Chính sách đổi trả được áp dụng theo nội dung tại trang <a href="{{ route('policies.returns') }}">Chính sách đổi trả</a>.</li>
                        <li>Thời gian xử lý đổi trả/hoàn tiền phụ thuộc vào tình trạng hàng hóa, quy trình kiểm tra và phương thức thanh toán.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">9. Bảo hành</h2>
                    <ul class="mb-3">
                        <li>Chính sách bảo hành được áp dụng theo nội dung tại trang <a href="{{ route('policies.warranty') }}">Chính sách bảo hành</a>.</li>
                        <li>Khách hàng cần cung cấp hóa đơn/chứng từ mua hàng và/hoặc thông tin serial (nếu có) để được hỗ trợ nhanh chóng.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">10. Bảo mật thông tin</h2>
                    <ul class="mb-3">
                        <li>Việc thu thập và sử dụng thông tin cá nhân được thực hiện theo <a href="{{ route('policies.privacy') }}">Chính sách bảo mật</a>.</li>
                        <li>Khách hàng có trách nhiệm bảo mật thông tin tài khoản (nếu có) và các thông tin liên quan đến giao dịch.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">11. Quyền sở hữu trí tuệ</h2>
                    <ul class="mb-3">
                        <li>Toàn bộ nội dung hiển thị trên website (hình ảnh, thiết kế, logo, bài viết, mã nguồn...) thuộc quyền sở hữu của Vigilance hoặc bên thứ ba cấp phép (nếu có).</li>
                        <li>Nghiêm cấm sao chép, sử dụng, phát tán nội dung khi chưa có sự đồng ý bằng văn bản từ Vigilance.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">12. Giới hạn trách nhiệm</h2>
                    <ul class="mb-3">
                        <li>Vigilance không chịu trách nhiệm đối với các thiệt hại gián tiếp, ngẫu nhiên phát sinh do việc sử dụng hoặc không thể sử dụng website vì lý do kỹ thuật, đường truyền, hoặc sự kiện bất khả kháng.</li>
                        <li>Trong mọi trường hợp, trách nhiệm (nếu có) của Vigilance sẽ không vượt quá giá trị của đơn hàng liên quan đến khiếu nại.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">13. Giải quyết khiếu nại</h2>
                    <ul class="mb-3">
                        <li>Khi có tranh chấp phát sinh, hai bên sẽ ưu tiên giải quyết bằng thương lượng.</li>
                        <li>Nếu không đạt được thỏa thuận, vụ việc sẽ được giải quyết theo quy định pháp luật Việt Nam.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">14. Hiệu lực điều khoản</h2>
                    <p class="mb-3">Điều khoản này có hiệu lực kể từ ngày đăng tải trên website và có thể được cập nhật mà không cần báo trước.</p>

                    <h2 class="h6 fw-bold mt-3">15. Sửa đổi điều khoản</h2>
                    <ul class="mb-3">
                        <li>Vigilance có quyền cập nhật/điều chỉnh điều khoản sử dụng để phù hợp với quy định pháp luật và hoạt động kinh doanh.</li>
                        <li>Phiên bản cập nhật sẽ được đăng tải trên website và có hiệu lực từ thời điểm đăng tải.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">16. Thông tin liên hệ</h2>
                    <ul class="mb-0">
                        <li>Địa chỉ: 96 Đường số 14, KDC Him Lam, Phường Tân Hưng, TP.HCM</li>
                        <li>Điện thoại: <a href="tel:0982751075">0982 751 075</a></li>
                        <li>Email: <a href="mailto:uancongly@gmail.com">uancongly@gmail.com</a></li>
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
                        <li><a href="{{ route('policies.privacy') }}">Chính sách bảo mật thông tin</a></li>
                        <li><a href="{{ route('policies.shipping') }}">Chính sách vận chuyển và giao nhận</a></li>
                        <li><a href="{{ route('policies.payment') }}">Phương thức thanh toán</a></li>
                        <li><a class="is-active" href="{{ route('policies.terms') }}">Điều khoản sử dụng</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
