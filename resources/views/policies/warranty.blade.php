@extends('layouts.user')

@section('title', 'Chính sách bảo hành - Vigilance')

@section('content')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Poppins:wght@500;600;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
    :root {
        --policy-blue: #2563eb;
        --policy-blue-2: #1d4ed8;
        --policy-ink: #0f172a;
        --policy-muted: #64748b;
        --policy-border: rgba(15, 23, 42, 0.10);
        --policy-shadow: 0 14px 40px rgba(2, 6, 23, 0.08);
    }

    .policy-wrap {
        font-family: Inter, Poppins, Roboto, system-ui, -apple-system, Segoe UI, Arial, sans-serif;
        color: var(--policy-ink);
    }

    .policy-mobile-header {
        position: sticky;
        top: 0;
        z-index: 1040;
        background: #fff;
        border-bottom: 1px solid var(--policy-border);
    }

    .policy-mobile-header .mh-btn {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
    }

    .policy-hero {
        background:
            radial-gradient(900px 220px at 0% 0%, rgba(37, 99, 235, 0.16) 0%, rgba(37, 99, 235, 0) 60%),
            radial-gradient(900px 220px at 100% 0%, rgba(59, 130, 246, 0.12) 0%, rgba(59, 130, 246, 0) 55%),
            linear-gradient(135deg, rgba(37, 99, 235, 0.10) 0%, rgba(255, 255, 255, 1) 60%);
        border: 1px solid var(--policy-border);
        border-radius: 18px;
        padding: 18px;
        box-shadow: var(--policy-shadow);
    }

    .policy-card {
        background: #fff;
        border: 1px solid var(--policy-border);
        border-radius: 16px;
        box-shadow: var(--policy-shadow);
    }

    .policy-chip {
        border-radius: 999px;
        padding: 6px 10px;
        font-weight: 800;
        font-size: 0.78rem;
        border: 1px solid rgba(37, 99, 235, 0.22);
        background: rgba(37, 99, 235, 0.10);
        color: var(--policy-blue-2);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .policy-icon {
        width: 38px;
        height: 38px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 99, 235, 0.12);
        color: var(--policy-blue-2);
        border: 1px solid rgba(37, 99, 235, 0.22);
        flex: 0 0 auto;
    }

    .policy-accordion .accordion-item {
        border: none;
        background: transparent;
        margin-bottom: 12px;
    }

    .policy-accordion .accordion-button {
        background: #fff;
        border: 1px solid var(--policy-border);
        border-radius: 16px !important;
        box-shadow: 0 10px 30px rgba(2, 6, 23, 0.06);
        padding: 14px 14px;
        color: var(--policy-ink);
        font-weight: 900;
        letter-spacing: -0.2px;
    }

    .policy-accordion .accordion-button:focus {
        box-shadow: 0 0 0 0.20rem rgba(37, 99, 235, 0.18);
        border-color: rgba(37, 99, 235, 0.30);
    }

    .policy-accordion .accordion-button:not(.collapsed) {
        color: var(--policy-blue-2);
        background: rgba(37, 99, 235, 0.06);
        border-color: rgba(37, 99, 235, 0.25);
    }

    .policy-accordion .accordion-body {
        background: #fff;
        border: 1px solid var(--policy-border);
        border-top: none;
        border-radius: 0 0 16px 16px;
        margin-top: -14px;
        padding: 16px 16px 14px 16px;
        color: #334155;
        line-height: 1.72;
    }

    .policy-row {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        min-width: 0;
    }

    .policy-row .t {
        margin: 0;
        font-size: 1.02rem;
        line-height: 1.25;
    }

    .policy-row .s {
        margin: 2px 0 0 0;
        color: var(--policy-muted);
        font-weight: 600;
        font-size: 0.84rem;
        line-height: 1.25;
    }

    @media (max-width: 767.98px) {
        .policy-hero { padding: 14px; }
        .policy-accordion .accordion-button { padding: 12px 12px; }
        .policy-accordion .accordion-body { padding: 14px 14px 12px 14px; }
    }
</style>

<div class="d-md-none policy-mobile-header">
    <div class="container py-2">
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary mh-btn" aria-label="Quay lại" onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = '{{ route('home') }}'; }">
                <i class="bi bi-arrow-left"></i>
            </button>
            <div class="fw-bold" style="color:var(--policy-ink);">Chính sách bảo hành</div>
        </div>
    </div>
</div>

<div class="policy-wrap">
    <div class="policy-hero mb-3">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
            <div style="min-width:0;">
                <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                    <span class="policy-chip"><i class="bi bi-shield-check"></i>Bảo hành chính hãng</span>
                    <span class="policy-chip"><i class="bi bi-camera-video"></i>Thiết bị an ninh</span>
                    <span class="policy-chip"><i class="bi bi-fingerprint"></i>Máy chấm công</span>
                </div>
                <h1 class="mb-2" style="font-weight: 900; letter-spacing:-0.6px;">Chính sách bảo hành</h1>
                <div style="color: var(--policy-muted); line-height: 1.65; max-width: 760px;">
                    Áp dụng cho camera giám sát, kiểm soát ra vào, máy chấm công, cổng phân làn do Vigilance phân phối.
                    Nội dung được chia theo từng mục để dễ theo dõi.
                </div>
                <div class="small mt-2" style="color: var(--policy-muted);">CÔNG TY VIGILANCE • Cập nhật: {{ now()->format('d/m/Y') }}</div>
            </div>
            <div class="policy-card p-3" style="min-width: 280px;">
                <div class="d-flex align-items-start gap-2">
                    <span class="policy-icon"><i class="bi bi-headset"></i></span>
                    <div style="min-width:0;">
                        <div class="fw-bold">Hỗ trợ nhanh</div>
                        <div class="small" style="color: var(--policy-muted);">Liên hệ trước để được hướng dẫn gửi thiết bị.</div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <a class="btn btn-primary" href="tel:0982751075" style="background: var(--policy-blue); border-color: var(--policy-blue); border-radius: 999px; font-weight: 900;"><i class="bi bi-telephone me-1"></i>Gọi</a>
                    <a class="btn btn-outline-primary" href="mailto:uancongly@gmail.com" style="border-radius: 999px; border-color: rgba(37,99,235,0.45); color: var(--policy-blue-2); font-weight: 900;"><i class="bi bi-envelope me-1"></i>Email</a>
                    <a class="btn" href="https://zalo.me/0982751075" onclick="event.preventDefault(); if (typeof openZalo === 'function') { openZalo('0982751075'); } else { window.open('https://zalo.me/0982751075', '_blank'); }" style="border-radius: 999px; border: 1px solid rgba(34,197,94,0.45); background: rgba(34,197,94,0.10); color: #166534; font-weight: 900;"><i class="bi bi-chat-dots me-1"></i>Zalo</a>
                </div>
            </div>
        </div>
    </div>

    <div class="policy-card p-3">
        <div class="d-flex align-items-center justify-content-between gap-2 mb-2 flex-wrap">
            <div>
                <div class="fw-bold">Nội dung chính</div>
                <div class="small" style="color: var(--policy-muted);">Bấm để mở/đóng từng mục</div>
            </div>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('warranty.check') }}" style="border-radius:999px; border-color: rgba(37,99,235,0.45); color: var(--policy-blue-2); font-weight: 900;">
                <i class="bi bi-search me-1"></i>Tra cứu bảo hành
            </a>
        </div>

        <div class="accordion policy-accordion" id="warrantyPolicyAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="wpa-1">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#wpc-1" aria-expanded="true" aria-controls="wpc-1">
                        <div class="policy-row">
                            <span class="policy-icon"><i class="bi bi-clock"></i></span>
                            <div style="min-width:0;">
                                <p class="t">Thời gian bảo hành</p>
                                <p class="s">Thời hạn & phạm vi lỗi được hỗ trợ</p>
                            </div>
                        </div>
                    </button>
                </h2>
                <div id="wpc-1" class="accordion-collapse collapse show" aria-labelledby="wpa-1" data-bs-parent="#warrantyPolicyAccordion">
                    <div class="accordion-body">
                        <div class="mb-2" style="color: var(--policy-muted);">
                            Thời gian bảo hành được tính từ <b>ngày mua hàng</b> trên hóa đơn (hoặc từ <b>ngày giao hàng</b> nếu không có hóa đơn hợp lệ).
                            Vui lòng giữ lại <b>hóa đơn</b> và <b>tem/serial</b> để được hỗ trợ nhanh.
                        </div>

                        <div class="mb-3">
                            <div class="fw-bold mb-1">Camera giám sát</div>
                            <ul class="mb-0">
                                <li><b>12 tháng</b> cho <b>thân máy</b> (mainboard, cảm biến, nguồn nội bộ).</li>
                                <li><b>06 tháng</b> cho <b>phụ kiện</b> đi kèm (adapter, dây nguồn, jack, phụ kiện lắp đặt theo bộ).</li>
                                <li>Không áp dụng với lỗi do <b>ngấm nước</b>, <b>oxy hóa</b>, hoặc <b>cháy nổ</b> do điện áp bất thường/sét đánh.</li>
                            </ul>
                        </div>

                        <div class="mb-3">
                            <div class="fw-bold mb-1">Máy chấm công</div>
                            <ul class="mb-0">
                                <li><b>12 tháng</b> cho thiết bị.</li>
                                <li>Áp dụng cho <b>lỗi kỹ thuật</b> (không nhận vân tay/khuôn mặt do lỗi phần cứng, lỗi nguồn, lỗi bo mạch).</li>
                                <li>Không bao gồm hao mòn tự nhiên của <b>phím bấm</b>, <b>màn hình</b>, hoặc hư hỏng do <b>rơi vỡ</b>, <b>tác động ngoại lực</b>.</li>
                            </ul>
                        </div>

                        <div class="mb-2">
                            <div class="fw-bold mb-1">Cổng phân làn</div>
                            <ul class="mb-0">
                                <li><b>12 tháng</b> cho hệ thống điều khiển, bo mạch, mô-đun nguồn theo cấu hình cung cấp.</li>
                                <li>Không bao gồm <b>hao mòn cơ học</b> (đầu chốt, trục, lò xo, tay gạt, bánh răng) do tần suất vận hành và môi trường lắp đặt.</li>
                                <li>Khuyến nghị <b>bảo trì định kỳ</b> để hệ thống vận hành ổn định và giảm rủi ro phát sinh lỗi.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="wpa-2">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#wpc-2" aria-expanded="false" aria-controls="wpc-2">
                        <div class="policy-row">
                            <span class="policy-icon"><i class="bi bi-patch-check"></i></span>
                            <div style="min-width:0;">
                                <p class="t">Điều kiện bảo hành</p>
                                <p class="s">Hóa đơn, thời điểm hiệu lực, serial</p>
                            </div>
                        </div>
                    </button>
                </h2>
                <div id="wpc-2" class="accordion-collapse collapse" aria-labelledby="wpa-2" data-bs-parent="#warrantyPolicyAccordion">
                    <div class="accordion-body">
                        <ul class="mb-0">
                            <li>Hiệu lực từ ngày ghi trên hóa đơn thương mại/hóa đơn thuế hoặc sau <b>30 ngày</b> kể từ ngày giao hàng (tùy ngày nào xảy ra trước).</li>
                            <li>Sản phẩm được sử dụng đúng mục đích và theo hướng dẫn.</li>
                            <li>Số serial còn nguyên vẹn, không bị xóa hoặc thay đổi.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="wpa-3">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#wpc-3" aria-expanded="false" aria-controls="wpc-3">
                        <div class="policy-row">
                            <span class="policy-icon" style="background: rgba(245,158,11,0.14); border-color: rgba(245,158,11,0.28); color:#b45309;"><i class="bi bi-exclamation-triangle"></i></span>
                            <div style="min-width:0;">
                                <p class="t">Trường hợp không bảo hành</p>
                                <p class="s">Tác động ngoại lực, can thiệp sửa đổi</p>
                            </div>
                        </div>
                    </button>
                </h2>
                <div id="wpc-3" class="accordion-collapse collapse" aria-labelledby="wpa-3" data-bs-parent="#warrantyPolicyAccordion">
                    <div class="accordion-body">
                        <ul class="mb-0">
                            <li>Tai nạn, sử dụng sai cách, hoặc bỏ bê.</li>
                            <li>Tác động từ thiên nhiên như sét đánh, nước, hoặc điện áp bất thường.</li>
                            <li>Sửa đổi mà không có sự chấp thuận bằng văn bản từ Vigilance.</li>
                            <li>Sản phẩm đã ngừng sản xuất và không thể sửa chữa hoặc thay thế.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="wpa-4">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#wpc-4" aria-expanded="false" aria-controls="wpc-4">
                        <div class="policy-row">
                            <span class="policy-icon"><i class="bi bi-diagram-3"></i></span>
                            <div style="min-width:0;">
                                <p class="t">Quy trình bảo hành</p>
                                <p class="s">Chuẩn bị hồ sơ, gửi thiết bị, kiểm tra</p>
                            </div>
                        </div>
                    </button>
                </h2>
                <div id="wpc-4" class="accordion-collapse collapse" aria-labelledby="wpa-4" data-bs-parent="#warrantyPolicyAccordion">
                    <div class="accordion-body">
                        <ol class="mb-2">
                            <li><b>Chuẩn bị:</b> Hoàn thành biểu mẫu “Báo cáo lỗi” trước khi gửi sản phẩm.</li>
                            <li><b>Gửi sản phẩm:</b> Gửi đến địa chỉ của Vigilance theo hình thức trả trước phí vận chuyển.</li>
                            <li><b>Phí kiểm tra:</b> Có thể áp dụng nếu thiếu biểu mẫu lỗi hoặc lỗi không thuộc phạm vi bảo hành.</li>
                        </ol>
                        <div class="small" style="color: var(--policy-muted);">
                            Thời gian xử lý: sửa chữa hoặc thay thế trong vòng <b>15 ngày làm việc</b> kể từ khi nhận sản phẩm.
                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="wpa-5">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#wpc-5" aria-expanded="false" aria-controls="wpc-5">
                        <div class="policy-row">
                            <span class="policy-icon"><i class="bi bi-telephone-inbound"></i></span>
                            <div style="min-width:0;">
                                <p class="t">Thông tin liên hệ hỗ trợ</p>
                                <p class="s">Địa chỉ, điện thoại, email</p>
                            </div>
                        </div>
                    </button>
                </h2>
                <div id="wpc-5" class="accordion-collapse collapse" aria-labelledby="wpa-5" data-bs-parent="#warrantyPolicyAccordion">
                    <div class="accordion-body">
                        <div class="row g-2">
                            <div class="col-12">
                                <div class="d-flex gap-2"><i class="bi bi-geo-alt" style="color: var(--policy-blue-2);"></i><div><b>Địa chỉ:</b> 96 Đường số 14, KDC Him Lam, Phường Tân Hưng, TP.HCM</div></div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="d-flex gap-2"><i class="bi bi-telephone" style="color: var(--policy-blue-2);"></i><div><b>Điện thoại:</b> <a href="tel:0982751075" style="text-decoration:none; color:inherit; font-weight:800;">0982 751 075</a></div></div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="d-flex gap-2"><i class="bi bi-envelope" style="color: var(--policy-blue-2);"></i><div><b>Email:</b> <a href="mailto:uancongly@gmail.com" style="text-decoration:none; color:inherit; font-weight:800;">uancongly@gmail.com</a></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
