@extends('layouts.user')

@section('title', 'Chính sách đổi trả - Vigilance')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chính sách đổi trả hàng hóa</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 fw-bold mb-3">Chính sách đổi trả hàng hóa</h1>

                    <h2 class="h6 fw-bold mt-3">CAM KẾT ĐỔI / TRẢ HÀNG</h2>
                    <p class="mb-2">Kính gửi Quý khách hàng,</p>
                    <p class="mb-2">Nếu Quý khách không hài lòng với sản phẩm đã mua tại Vigilance, vui lòng thông báo cho chúng tôi bằng cách điền thông tin vào Mẫu yêu cầu đổi / trả hàng (liên hệ nhân viên tư vấn bán hàng để nhận mẫu) và gửi kèm hóa đơn hoặc phiếu xuất kho trong vòng 10 ngày kể từ ngày nhận hàng.</p>
                    <p class="mb-2">Trước khi quyết định đổi / trả hàng, xin Quý khách vui lòng đọc kỹ điều kiện đổi trả dưới đây. Nếu không đáp ứng đầy đủ các điều kiện này, Vigilance xin phép từ chối yêu cầu đổi / trả hàng.</p>
                    <p class="mb-3">Trong trường hợp Quý khách vẫn yêu cầu đổi / trả và chúng tôi có thể hỗ trợ, một khoản phí xử lý tối thiểu 20% giá trị sản phẩm sẽ được áp dụng cho tháng sử dụng đầu tiên. Phí này có thể tăng theo thời gian sử dụng sản phẩm.</p>

                    <h2 class="h6 fw-bold mt-3">ĐIỀU KIỆN ĐỔI / TRẢ HÀNG</h2>
                    <p class="mb-2">Quý khách được đổi / trả hàng khi đáp ứng đầy đủ các điều kiện sau:</p>
                    <ul class="mb-3">
                        <li>Sản phẩm không thuộc danh sách không chấp nhận đổi / trả.</li>
                        <li>Sản phẩm còn nguyên trạng: chưa sử dụng, không bị hư hỏng, và giữ nguyên tình trạng ban đầu khi nhận hàng.</li>
                        <li>Trường hợp sản phẩm không còn nguyên trạng:</li>
                    </ul>
                    <ul class="mb-3">
                        <li>Sản phẩm bị hư hỏng trong quá trình vận chuyển.</li>
                        <li>Sản phẩm giao sai hoặc không đúng với đơn hàng đã đặt.</li>
                        <li>Sản phẩm bị lỗi kỹ thuật do nhà sản xuất trong thời gian quy định đổi / trả.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">HƯỚNG DẪN ĐỔI / TRẢ HÀNG</h2>
                    <p class="mb-2 fw-bold">Đóng gói sản phẩm đúng cách:</p>
                    <ul class="mb-3">
                        <li>Đặt sản phẩm cùng các phụ kiện, tài liệu hướng dẫn, thẻ bảo hành, quà tặng kèm (nếu có) vào thùng / hộp ban đầu của nhà sản xuất (nếu không còn, có thể sử dụng vật liệu thay thế).</li>
                        <li>Kiện hàng phải đảm bảo như trạng thái ban đầu, không bị móp méo, trầy xước.</li>
                    </ul>
                    <p class="mb-2 fw-bold">Gửi hàng đến địa chỉ:</p>
                    <div class="mb-3">
                        B.15.08, tầng 15, Toà nhà, Khối tháp B, Trung tâm thương mại, văn phòng, Officetel và Căn hộ (RiverGate Residence), 151-155 Bến Vân Đồn, Phường Khánh Hội, Thành phố Hồ Chí Minh, Việt Nam
                    </div>
                    <ul class="mb-3">
                        <li>Kèm theo hóa đơn mua hàng hoặc phiếu xuất kho và mẫu yêu cầu đổi / trả đã điền đầy đủ thông tin.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">NHỮNG MẶT HÀNG KHÔNG ĐƯỢC ĐỔI / TRẢ</h2>
                    <ul class="mb-3">
                        <li>Sản phẩm không đáp ứng quy định đổi / trả.</li>
                        <li>Phần mềm bản quyền gửi qua đường điện tử.</li>
                        <li>Sản phẩm trưng bày, hàng giải phóng tồn kho, hoặc hàng mẫu.</li>
                        <li>Thiết bị chấm công, kiểm soát cửa đã lắp ráp hoặc cấu hình theo yêu cầu.</li>
                        <li>Sản phẩm đặt hàng theo dự án hoặc theo yêu cầu riêng.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">GIỚI HẠN TRÁCH NHIỆM</h2>
                    <p class="mb-3">Vigilance chỉ cung cấp thông tin và tư vấn sản phẩm. Quý khách tự chịu trách nhiệm về quyết định mua hàng.</p>

                    <h2 class="h6 fw-bold mt-3">CHÍNH SÁCH HOÀN TIỀN</h2>
                    <ul class="mb-3">
                        <li>Hoàn tiền theo phương thức thanh toán ban đầu (trừ trường hợp đặc biệt).</li>
                        <li>Thời gian xử lý yêu cầu: 3 – 5 ngày làm việc.</li>
                        <li>Thời gian hoàn tiền vào tài khoản: 7 – 10 ngày làm việc (tùy ngân hàng).</li>
                    </ul>
                    <p class="mb-2 fw-bold">Lưu ý:</p>
                    <ul class="mb-3">
                        <li>Quý khách không nhận được tiền sau 10 ngày làm việc, vui lòng liên hệ tổng đài 0982.751075.</li>
                        <li>Chỉ hoàn trả giá trị sản phẩm. Các chi phí phát sinh như giao hàng, cài đặt hoặc hỗ trợ kỹ thuật sẽ không được hoàn.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">CHÍNH SÁCH BẢO HÀNH</h2>
                    <ul class="mb-3">
                        <li>Sản phẩm được bảo hành theo chính sách của nhà sản xuất.</li>
                        <li>Hàng hư hỏng ngoài điều kiện đổi trả sẽ được gửi về nhà sản xuất hoặc đơn vị ủy quyền để sửa chữa.</li>
                        <li>Lưu ý: Vigilance không chịu trách nhiệm nếu nhà sản xuất hoặc đơn vị ủy quyền từ chối bảo hành.</li>
                    </ul>

                    <div class="mt-4 p-3" style="background: rgba(227,0,25,0.06); border: 1px solid rgba(227,0,25,0.18); border-radius: 12px;">
                        <div class="fw-bold mb-1">Hỗ trợ</div>
                        <div>Mọi thắc mắc vui lòng liên hệ tổng đài CSKH: <a href="tel:0982751075">0982.751075</a>.</div>
                    </div>
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
                        <li><a class="is-active" href="{{ route('policies.returns') }}">Chính sách đổi trả hàng hóa</a></li>
                        <li><a href="{{ route('policies.privacy') }}">Chính sách bảo mật thông tin</a></li>
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
