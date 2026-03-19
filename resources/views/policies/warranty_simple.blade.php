@extends('layouts.user')

@section('title', 'Chính sách bảo hành - Vigilance')

@section('content')
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Chính sách bảo hành</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h1 class="h4 fw-bold mb-3">Chính sách bảo hành</h1>

                    <p class="mb-3">Chính sách bảo hành của Vigilance</p>

                    <h2 class="h6 fw-bold mt-3">1. Thời hạn bảo hành</h2>
                    <ul class="mb-3">
                        <li>Vigilance áp dụng chính sách bảo hành phần cứng có thời hạn 1 năm đối với tất cả sản phẩm do Vigilance phân phối.</li>
                        <li>Chính sách bảo hành chỉ áp dụng cho các lỗi phát sinh do vật liệu hoặc quy trình sản xuất không đúng theo tiêu chuẩn công bố.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">2. Quyền lợi bảo hành</h2>
                    <ul class="mb-3">
                        <li>Sản phẩm bị lỗi phần cứng sẽ được sửa chữa hoặc thay thế trong vòng 15 ngày làm việc kể từ khi nhận sản phẩm.</li>
                        <li>Phần cứng được sửa chữa hoặc thay thế sẽ tiếp tục được bảo hành trong thời gian còn lại của bảo hành gốc hoặc thêm 60 ngày (tùy theo thời hạn nào dài hơn).</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">3. Điều kiện áp dụng bảo hành</h2>
                    <ul class="mb-3">
                        <li>Bảo hành có hiệu lực từ ngày ghi trên hóa đơn thương mại/hóa đơn thuế hoặc sau 30 ngày kể từ ngày giao hàng (tùy ngày nào xảy ra trước).</li>
                        <li>Chỉ áp dụng nếu:</li>
                    </ul>
                    <ul class="mb-3">
                        <li>Sản phẩm được sử dụng đúng mục đích và theo hướng dẫn.</li>
                        <li>Số serial của sản phẩm còn nguyên vẹn, không bị xóa hoặc thay đổi.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">4. Các trường hợp không áp dụng bảo hành</h2>
                    <p class="mb-2">Sản phẩm bị hư hỏng do:</p>
                    <ul class="mb-3">
                        <li>Tai nạn, sử dụng sai cách, hoặc bỏ bê.</li>
                        <li>Tác động từ thiên nhiên như sét đánh, nước, hoặc điện áp bất thường.</li>
                        <li>Sửa đổi mà không có sự chấp thuận bằng văn bản từ Vigilance.</li>
                        <li>Sản phẩm đã ngừng sản xuất và không thể sửa chữa hoặc thay thế.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">5. Quy trình bảo hành</h2>
                    <ul class="mb-3">
                        <li><b>Chuẩn bị:</b> Khách hàng cần hoàn thành biểu mẫu “Báo cáo lỗi” trước khi gửi sản phẩm.</li>
                        <li><b>Gửi sản phẩm:</b> Sản phẩm phải được gửi đến địa chỉ của Vigilance theo hình thức có trả trước phí vận chuyển. Vigilance không chịu trách nhiệm về chi phí vận chuyển hay mất mát trong quá trình vận chuyển.</li>
                        <li><b>Phí kiểm tra:</b> Một khoản phí bổ sung có thể được áp dụng nếu sản phẩm không gửi kèm biểu mẫu lỗi hoặc lỗi không nằm trong phạm vi bảo hành.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">6. Sửa chữa ngoài bảo hành</h2>
                    <ul class="mb-3">
                        <li>Đối với sản phẩm hết hạn bảo hành hoặc không nằm trong phạm vi bảo hành, khách hàng sẽ được báo giá chi phí sửa chữa trước khi tiến hành.</li>
                        <li>Trong trường hợp không sửa chữa được, sản phẩm sẽ được trả lại theo yêu cầu của khách hàng với chi phí vận chuyển do khách hàng chịu.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">7. Thanh lý sản phẩm</h2>
                    <ul class="mb-3">
                        <li>Vigilance lưu giữ sản phẩm bảo hành tối đa 90 ngày kể từ ngày ký nhận.</li>
                        <li>Sau thời gian này, nếu khách hàng không đến nhận lại sản phẩm, Vigilance có quyền thanh lý hoặc hủy sản phẩm mà không cần thông báo thêm.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">8. Lưu ý quan trọng</h2>
                    <ul class="mb-3">
                        <li>Khách hàng nên đảm bảo bảo hiểm lô hàng khi vận chuyển đến Vigilance.</li>
                        <li>Vigilance không chịu trách nhiệm đối với mất mát hoặc thiệt hại trong quá trình vận chuyển.</li>
                    </ul>

                    <h2 class="h6 fw-bold mt-3">Thông tin liên hệ</h2>
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
                        <li><a class="is-active" href="{{ route('policies.warranty') }}">Chính sách bảo hành</a></li>
                        <li><a href="{{ route('policies.returns') }}">Chính sách đổi trả hàng hóa</a></li>
                        <li><a href="{{ route('policies.privacy') }}">Chính sách bảo mật thông tin</a></li>
                        <li><a href="{{ route('policies.shipping') }}">Chính sách vận chuyển và giao nhận</a></li>
                        <li><a href="{{ route('policies.payment') }}">Phương thức thanh toán</a></li>
                        <li><a href="{{ route('policies.terms') }}">Điều khoản sử dụng</a></li>
                    </ul>
                </div>
            </div>

            @php
                $warrantyPolicyImg = public_path('images/chinhsachbaohanh.png');
            @endphp

            @if (file_exists($warrantyPolicyImg))
                <div class="card shadow-sm">
                    <img src="{{ asset('images/chinhsachbaohanh.png') }}" alt="Chính sách bảo hành" class="img-fluid" style="border-radius: .375rem;">
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
