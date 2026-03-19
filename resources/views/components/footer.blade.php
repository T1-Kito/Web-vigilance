@php($variant = $variant ?? 'compact')
@php($companyTaxCode = env('COMPANY_TAX_CODE', '0318231312'))
@php($bctVerifyUrlRaw = trim((string) env('BCT_VERIFY_URL', 'https://online.gov.vn/Home/WebDetails/140880')))
@php($bctVerifyUrl = preg_match('~^https?://~i', $bctVerifyUrlRaw)
    ? $bctVerifyUrlRaw
    : (str_starts_with($bctVerifyUrlRaw, '//') ? $bctVerifyUrlRaw : '//' . ltrim($bctVerifyUrlRaw, '/')))
@php($bctBadgeLogoUrl = env('BCT_BADGE_LOGO_URL', asset('images/da-dang-ky.png')))

<footer style="background:#1a1b22; color:#e9edf3; position: relative; overflow:hidden;">
    <style>
        .vk-footer { position: relative; }
        
        .vk-footer::before {
            content: "";
            position: absolute;

            inset: 0;
            background:
                linear-gradient(0deg, rgba(14,15,20,0.40), rgba(14,15,20,0.40)),
                url('{{ asset('Group 1000002976.png') }}') center/cover no-repeat;
            pointer-events: none;
        }
        
        .vk-footer a { color: #cfd6e2; text-decoration: none; }
        .vk-footer a:hover { color: #ffffff; text-decoration: underline; }
        .vk-footer-title { color: #ffb020; font-weight: 700; font-size: 0.95rem; margin-bottom: 0.85rem; }
        .vk-footer-text { color: #cfd6e2; font-size: 0.9rem; line-height: 1.55; }
        .vk-footer-list { list-style: none; padding: 0; margin: 0; }
        .vk-footer-list li { margin-bottom: 0.5rem; }
        .vk-footer-bottom { border-top: 1px solid rgba(255,255,255,0.14); }
        .vk-social {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.22);
            background: rgba(255,255,255,0.06);
        }
        .vk-social i { font-size: 20px; line-height: 1; }
        .vk-zalo { font-weight: 800; font-size: 12px; letter-spacing: 0.2px; }
        .vk-logo-wrap { background:#ffffff; border-radius:12px; padding:6px 10px; border:1px solid rgba(255,255,255,0.25); }
        .vk-cta-badge { border-radius: 10px; padding: 6px 10px; font-weight: 700; font-size: 0.78rem; }
        .vk-card { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); border-radius: 16px; padding: 16px; }
        .vk-kpi { display:flex; gap:10px; flex-wrap:wrap; }
        .vk-kpi .k { padding:8px 10px; border-radius:12px; background: rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.14); }
        .vk-kpi .k .n { font-weight:800; color:#ffffff; line-height:1; }
        .vk-kpi .k .t { font-size:0.82em; color: rgba(255,255,255,0.85); line-height:1.1; }
        .vk-form-control { border-radius:12px; border:1px solid rgba(255,255,255,0.22); background: rgba(255,255,255,0.10); color:#ffffff; }
        .vk-form-control::placeholder { color: rgba(255,255,255,0.75); }
        .vk-form-control:focus { border-color: rgba(255,255,255,0.35); box-shadow: 0 0 0 0.18rem rgba(0,0,0,0.22); }
        .vk-bct-badge { display: inline-flex; align-items: center; justify-content: center; }
        .vk-bct-badge img { width: auto; max-width: 100%; display: block; object-fit: contain; }
        .vk-bct-box {  margin-top: 31px;}

        @media (max-width: 767.98px) {
            .vk-footer-title { margin-bottom: 0.5rem; }
            .vk-footer-text { font-size: 0.88rem; line-height: 1.45; }
            .vk-footer-list li { margin-bottom: 0.4rem; }
            .vk-bct-badge img { height: 40px; }
            .vk-bct-box { padding: 6px 10px; }
        }
    </style>

    <div class="vk-footer">
        <div class="container position-relative" style="padding: 46px 0 18px 0;">
            <div class="d-md-none">
                <div class="mb-3">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="bi bi-buildings" style="font-size:22px; color:#e11d2e;"></i>
                        <div style="font-weight:800; font-size:1.02rem; color:#ffffff; line-height:1.1;">CÔNG TY CỔ PHẦN VIGILANCE Việt Nam </div>
                    </div>

                    <div class="vk-footer-text">
                        <div class="d-flex gap-2 mb-2">
                            <div style="width:18px;"><i class="bi bi-geo-alt" style="color:#ffb020;"></i></div>
                            <div>ĐC: 96 Đường số 14, KDC Him Lam, Phường Tân Hưng,TP.HCM</div>
                        </div>
                        <div class="d-flex gap-2 mb-2">
                            <div style="width:18px;"><i class="bi bi-telephone" style="color:#ffb020;"></i></div>
                            <div>Hotline: <a href="tel:0982751075">0982751075</a></div>
                        </div>
                        <div class="d-flex gap-2 mb-2">
                            <div style="width:18px;"><i class="bi bi-headset" style="color:#ffb020;"></i></div>
                            <div>Tổng đài hỗ trợ, CSKH, hỗ trợ mua hàng: <a href="tel:0982751039">0982751039</a></div>
                        </div>
                        <div class="d-flex gap-2">
                            <div style="width:18px;"><i class="bi bi-envelope" style="color:#ffb020;"></i></div>
                            <div>Email: <a href="mailto:uancongly@gmail.com">uancongly@gmail.com</a></div>
                        </div>
                        @if(!empty($companyTaxCode))
                            <div class="d-flex gap-2 mt-2">
                                <div style="width:18px;"><i class="bi bi-receipt" style="color:#ffb020;"></i></div>
                                <div>Mã số thuế: {{ $companyTaxCode }}</div>
                            </div>
                        @endif

                        <div class="mt-3">
                            <div class="vk-bct-box">
                                <a class="vk-bct-badge" href="{{ $bctVerifyUrl }}" target="_blank" rel="nofollow noopener noreferrer" aria-label="Bộ Công Thương">
                                    <img src="{{ $bctBadgeLogoUrl }}" alt="Đã thông báo Bộ Công Thương">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="accordion" id="footerAccordion" style="--bs-accordion-bg: transparent; --bs-accordion-border-color: rgba(255,255,255,0.14); --bs-accordion-btn-color: #ffb020; --bs-accordion-btn-focus-box-shadow: none;">
                    <div class="accordion-item" style="background: transparent; border: 1px solid rgba(255,255,255,0.14); border-radius: 14px; overflow: hidden;">
                        <h2 class="accordion-header" id="footerHeading1">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#footerCollapse1" aria-expanded="false" aria-controls="footerCollapse1" style="background: rgba(255,255,255,0.06); color:#ffb020; font-weight:800;">
                                DỊCH VỤ & THÔNG TIN KHÁC
                            </button>
                        </h2>
                        <div id="footerCollapse1" class="accordion-collapse collapse" aria-labelledby="footerHeading1" data-bs-parent="#footerAccordion">
                            <div class="accordion-body" style="background: rgba(0,0,0,0.10);">
                                <ul class="vk-footer-list vk-footer-text">
                                    <li><a href="#">Khách hàng doanh nghiệp (B2B)</a></li>
                                    <li><a href="#">Dịch vụ lắp đặt - thi công</a></li>
                                    <li><a href="#">Hợp tác kinh doanh</a></li>
                                    <li><a href="#">Tuyển dụng</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item mt-3" style="background: transparent; border: 1px solid rgba(255,255,255,0.14); border-radius: 14px; overflow: hidden;">
                        <h2 class="accordion-header" id="footerHeading2">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#footerCollapse2" aria-expanded="false" aria-controls="footerCollapse2" style="background: rgba(255,255,255,0.06); color:#ffb020; font-weight:800;">
                                THÔNG TIN & CHÍNH SÁCH
                            </button>
                        </h2>
                        <div id="footerCollapse2" class="accordion-collapse collapse" aria-labelledby="footerHeading2" data-bs-parent="#footerAccordion">
                            <div class="accordion-body" style="background: rgba(0,0,0,0.10);">
                                <ul class="vk-footer-list vk-footer-text">
                                    <li><a href="{{ route('policies.warranty') }}">Chính sách bảo hành</a></li>
                                    <li><a href="{{ route('policies.shipping') }}">Chính sách giao hàng</a></li>
                                    <li><a href="{{ route('policies.returns') }}">Chính sách đổi trả</a></li>
                                    <li><a href="{{ route('policies.payment') }}">Phương thức thanh toán</a></li>
                                    <li><a href="{{ route('policies.privacy') }}">Chính sách bảo mật</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    @if($variant === 'compact')
                        <div class="accordion-item mt-3" style="background: transparent; border: 1px solid rgba(255,255,255,0.14); border-radius: 14px; overflow: hidden;">
                            <h2 class="accordion-header" id="footerHeading3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#footerCollapse3" aria-expanded="false" aria-controls="footerCollapse3" style="background: rgba(255,255,255,0.06); color:#ffb020; font-weight:800;">
                                    HỖ TRỢ
                                </button>
                            </h2>
                            <div id="footerCollapse3" class="accordion-collapse collapse" aria-labelledby="footerHeading3" data-bs-parent="#footerAccordion">
                                <div class="accordion-body" style="background: rgba(0,0,0,0.10);">
                                    <ul class="vk-footer-list vk-footer-text">
                                        <li><a href="{{ url('/dieu-khoan-su-dung') }}">Điều khoản sử dụng</a></li>
                                        <li><a href="https://vksoftware.vn/" target="_blank" rel="noopener noreferrer">Tính lương</a></li>
                                        <li><a href="https://vksoftware.vn/" target="_blank" rel="noopener noreferrer">Chấm công</a></li>
                                        <li><a href="https://vksoftware.vn/" target="_blank" rel="noopener noreferrer">Kiểm soát cửa</a></li>
                                        <li><a href="https://vksoftware.vn/" target="_blank" rel="noopener noreferrer">Viết phần mềm theo yêu cầu</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="d-none d-md-block">
                <div class="row g-4">
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <i class="bi bi-buildings" style="font-size:22px; color:#e11d2e;"></i>
                            <div style="font-weight:800; font-size:1.02rem; color:#ffffff; line-height:1.1;">CÔNG TY CỔ PHẦN VIGILANCE VIỆT NAM </div>
                        </div>

                        <div class="vk-footer-text">
                            <div class="d-flex gap-2 mb-2">
                                <div style="width:18px;"><i class="bi bi-geo-alt" style="color:#ffb020;"></i></div>
                                <div>ĐC: 96 Đường số 14, KDC Him Lam, Phường Tân Hưng,TP.HCM</div>
                            </div>
                            <div class="d-flex gap-2 mb-2">
                                <div style="width:18px;"><i class="bi bi-telephone" style="color:#ffb020;"></i></div>
                                <div>Hotline: <a href="tel:0982751075">0982751075</a></div>
                            </div>
                            <div class="d-flex gap-2 mb-2">
                                <div style="width:18px;"><i class="bi bi-headset" style="color:#ffb020;"></i></div>
                                <div>Tổng đài hỗ trợ, CSKH, hỗ trợ mua hàng: <a href="tel:0982751039">0982751039</a></div>
                            </div>
                            <div class="d-flex gap-2">
                                <div style="width:18px;"><i class="bi bi-envelope" style="color:#ffb020;"></i></div>
                                <div>Email: <a href="mailto:uancongly@gmail.com">uancongly@gmail.com</a></div>
                            </div>
                            @if(!empty($companyTaxCode))
                                <div class="d-flex gap-2 mt-2">
                                    <div style="width:18px;"><i class="bi bi-receipt" style="color:#ffb020;"></i></div>
                                    <div>Mã số thuế: {{ $companyTaxCode }}</div>
                                </div>
                            @endif
                        </div>

                        @if($variant === 'full')
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <span class="vk-cta-badge" style="background:rgba(59,130,246,0.20); border:1px solid rgba(59,130,246,0.35); color:#dbeafe;">Tư vấn giải pháp</span>
                                <span class="vk-cta-badge" style="background:rgba(245,158,11,0.18); border:1px solid rgba(245,158,11,0.32); color:#ffedd5;">Thi công lắp đặt</span>
                                <span class="vk-cta-badge" style="background:rgba(34,197,94,0.18); border:1px solid rgba(34,197,94,0.32); color:#dcfce7;">Bảo hành chính hãng</span>
                            </div>
                        @endif
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="vk-footer-title">DỊCH VỤ & THÔNG TIN KHÁC</div>
                        <ul class="vk-footer-list vk-footer-text">
                            <li><a href="#">Khách hàng doanh nghiệp (B2B)</a></li>
                            <li><a href="#">Dịch vụ lắp đặt - thi công</a></li>
                            <li><a href="#">Hợp tác kinh doanh</a></li>
                            <li><a href="#">Tuyển dụng</a></li>
                        </ul>

                        <div class="mt-3">
                            <div class="vk-bct-box">
                                <a class="vk-bct-badge" href="{{ $bctVerifyUrl }}" target="_blank" rel="nofollow noopener noreferrer" aria-label="Bộ Công Thương">
                                    <img src="{{ $bctBadgeLogoUrl }}" alt="Đã thông báo Bộ Công Thương">
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <div class="vk-footer-title">THÔNG TIN & CHÍNH SÁCH</div>
                        <ul class="vk-footer-list vk-footer-text">
                            <li><a href="{{ route('policies.warranty') }}">Chính sách bảo hành</a></li>
                            <li><a href="{{ route('policies.shipping') }}">Chính sách giao hàng</a></li>
                            <li><a href="{{ route('policies.returns') }}">Chính sách đổi trả</a></li>
                            <li><a href="{{ route('policies.payment') }}">Phương thức thanh toán</a></li>
                            <li><a href="{{ route('policies.privacy') }}">Chính sách bảo mật</a></li>
                        </ul>
                    </div>

                    @if($variant === 'compact')
                        <div class="col-lg-3 col-md-6">
                            <div class="vk-footer-title">HỖ TRỢ</div>
                            <ul class="vk-footer-list vk-footer-text">
                                <li><a href="{{ url('/dieu-khoan-su-dung') }}">Điều khoản sử dụng</a></li>
                                <li><a href="https://vksoftware.vn/" target="_blank" rel="noopener noreferrer">Tính lương</a></li>
                                <li><a href="https://vksoftware.vn/" target="_blank" rel="noopener noreferrer">Chấm công</a></li>
                                <li><a href="https://vksoftware.vn/" target="_blank" rel="noopener noreferrer">Kiểm soát cửa</a></li>
                                <li><a href="https://vksoftware.vn/" target="_blank" rel="noopener noreferrer">Viết phần mềm theo yêu cầu</a></li>
                            </ul>
                        </div>
                    @else
                        <div class="col-lg-4 col-md-6">
                            <div class="vk-footer-title">ĐĂNG KÝ NHẬN TIN KHUYẾN MÃI</div>
                            <div class="vk-footer-text" style="opacity:0.9; margin-bottom: 10px;">Nhận ưu đãi và thông tin sản phẩm mới mỗi tuần.</div>

                            <form action="{{ route('newsletter.subscribe') }}" method="post" class="mb-3">
                                @csrf
                                <div class="mb-2">
                                    <input type="email" name="email" class="form-control vk-form-control" placeholder="Email" required>
                                </div>
                                <div class="mb-2">
                                    <input type="tel" name="phone" class="form-control vk-form-control" placeholder="Số điện thoại">
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="accept" value="1" id="footerAccept" checked>
                                    <label class="form-check-label small" for="footerAccept" style="color: rgba(255,255,255,0.85);">Tôi đồng ý với điều khoản của công ty</label>
                                </div>
                                <button type="submit" class="btn" style="background:#e11d2e; color:#fff; border-radius:12px; padding:10px 14px; width:100%; font-weight:800;">Đăng ký ngay</button>
                            </form>

                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="vk-footer-title" style="margin:0; font-size:0.9rem;">KẾT NỐI</div>
                                <div class="d-flex gap-2">
                                    <span class="vk-social" aria-hidden="true" title="Kết nối" style="opacity:0.95;"></span>
                                    <span class="vk-social" aria-hidden="true" title="Kết nối" style="opacity:0.95;"></span>
                                    <span class="vk-social" aria-hidden="true" title="Kết nối" style="opacity:0.95;"></span>
                                </div>
                            </div>

                            <div class="vk-card">
                                <div class="d-flex align-items-start justify-content-between gap-3">
                                    <div>
                                        <div class="vk-footer-title" style="margin-bottom:6px;">HỖ TRỢ NHANH</div>
                                        <div class="vk-footer-text" style="opacity:0.9;">Gọi hotline hoặc nhắn Zalo để được tư vấn ngay.</div>
                                    </div>
                                    <div class="d-flex flex-column gap-2" style="min-width:120px;">
                                        <a class="btn btn-sm" href="tel:0982751075" style="border-radius:12px; background: rgba(255,255,255,0.10); color:#fff; border:1px solid rgba(255,255,255,0.18);"><i class="bi bi-telephone me-1"></i>Gọi</a>
                                        <a class="btn btn-sm" href="https://zalo.me/0982751039" target="_blank" rel="noopener noreferrer" style="border-radius:12px; background: rgba(34,197,94,0.22); color:#fff; border:1px solid rgba(34,197,94,0.35);"><i class="bi bi-chat-dots me-1"></i>Zalo</a>
                                    </div>
                                </div>

                                <div class="vk-kpi mt-3">
                                    <div class="k"><div class="n">24h</div><div class="t">phản hồi</div></div>
                                    <div class="k"><div class="n">100+</div><div class="t">dự án</div></div>
                                    <div class="k"><div class="n">5★</div><div class="t">đánh giá</div></div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                @if($variant === 'compact')
                    <div class="row g-3 mt-2">
                        <div class="col-md-12">
                            <div class="d-flex flex-wrap gap-2">
                                <span class="vk-cta-badge" style="background:rgba(59,130,246,0.20); border:1px solid rgba(59,130,246,0.35); color:#dbeafe;">Tư vấn giải pháp</span>
                                <span class="vk-cta-badge" style="background:rgba(245,158,11,0.18); border:1px solid rgba(245,158,11,0.32); color:#ffedd5;">Thi công lắp đặt</span>
                                <span class="vk-cta-badge" style="background:rgba(34,197,94,0.18); border:1px solid rgba(34,197,94,0.32); color:#dcfce7;">Bảo hành chính hãng</span>
                            </div>
                        </div>
                    </div>
                @endif

                @if($variant === 'full')
                    <div class="row g-3 mt-2">
                        <div class="col-md-4">
                            <div class="vk-card" style="height:100%;">
                                <div class="d-flex gap-3">
                                    <div style="width:42px; height:42px; border-radius:14px; display:flex; align-items:center; justify-content:center; background: rgba(255,255,255,0.10); border:1px solid rgba(255,255,255,0.16);">
                                        <i class="bi bi-stars" style="color:#ffb020;"></i>
                                    </div>
                                    <div>
                                        <div class="vk-footer-title" style="margin-bottom:4px;">CAM KẾT DỊCH VỤ</div>
                                        <div class="vk-footer-text" style="opacity:0.9;">Tư vấn đúng nhu cầu, báo giá minh bạch, hỗ trợ nhanh trong giờ hành chính.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="vk-card" style="height:100%;">
                                <div class="d-flex gap-3">
                                    <div style="width:42px; height:42px; border-radius:14px; display:flex; align-items:center; justify-content:center; background: rgba(255,255,255,0.10); border:1px solid rgba(255,255,255,0.16);">
                                        <i class="bi bi-clock" style="color:#60a5fa;"></i>
                                    </div>
                                    <div>
                                        <div class="vk-footer-title" style="margin-bottom:4px;">GIỜ LÀM VIỆC</div>
                                        <div class="vk-footer-text" style="opacity:0.9;">T2 - T7: 08:00 - 17:00<br>CN: Nghỉ (hẹn trước vẫn hỗ trợ)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="vk-card" style="height:100%;">
                                <div class="d-flex gap-3">
                                    <div style="width:42px; height:42px; border-radius:14px; display:flex; align-items:center; justify-content:center; background: rgba(255,255,255,0.10); border:1px solid rgba(255,255,255,0.16);">
                                        <i class="bi bi-lightning-charge" style="color:#f59e0b;"></i>
                                    </div>
                                    <div>
                                        <div class="vk-footer-title" style="margin-bottom:4px;">HỖ TRỢ NHANH</div>
                                        <div class="vk-footer-text" style="opacity:0.9;">Hotline và Zalo luôn sẵn sàng hỗ trợ.</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</footer>