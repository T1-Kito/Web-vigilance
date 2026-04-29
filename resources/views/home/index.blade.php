@extends('layouts.user')

@section('title', 'Vigilance Việt Nam JSC')

@section('meta_description', 'Vigilance Việt Nam JSC - Cung cấp thiết bị kiểm soát ra vào, camera giám sát, máy chấm công, khóa thông minh chính hãng, giao hàng nhanh toàn quốc.')
@section('canonical', url('/'))

@section('content')
@php
    $isAgentUser = auth()->check() && (string) auth()->user()->role === 'agent';
@endphp

<!-- Thông báo lỗi từ middleware -->
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin: 20px; border-radius: 12px;">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Lỗi:</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Mobile Header (chỉ hiển thị trên mobile) -->
<div class="d-none mb-3">
    <div class="d-flex align-items-center justify-content-between bg-white p-3 rounded-3 shadow-sm" style="position: sticky; top: 0; z-index: 1000;">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-primary rounded-circle" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" style="width: 40px; height: 40px;">
                <i class="bi bi-list"></i>
            </button>
            <img src="{{ asset('logovigilance.jpg') }}" alt="Logo" style="height: 35px; max-height: 35px; display: block;">
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('cart.view') }}" class="btn btn-outline-primary position-relative rounded-circle" style="width: 40px; height: 40px;">
                <i class="bi bi-cart3"></i>
                @if(isset($cartCount) && $cartCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.7em;">
                        {{ $cartCount }}
                    </span>
                @endif
            </a>
            @auth
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle rounded-circle" type="button" data-bs-toggle="dropdown" style="width: 40px; height: 40px;">
                        <i class="bi bi-person-circle"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('orders.index') }}">Đơn hàng của tôi</a></li>
                        <li><a class="dropdown-item" href="{{ route('wishlist.index') }}">Yêu thích</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();">Đăng xuất</a>
                        </li>
                    </ul>
                </div>
                <form id="logout-form-mobile" action="{{ route('logout') }}" method="POST" style="display:none;">
                    @csrf
                </form>
            @else
                <button class="btn btn-outline-primary rounded-circle mobile-login-btn" type="button" style="width: 40px; height: 40px;">
                    <i class="bi bi-person-circle"></i>
                </button>
            @endauth
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        function initHeroHeightSync() {
            var heroRow = document.querySelector('.home-hero-row');
            if (!heroRow) return false;

            var middleCol = heroRow.querySelector('.home-hero-middle');
            if (!middleCol) return false;

            var mql = window.matchMedia('(min-width: 768px)');

            function syncHeroHeights() {
                if (!mql.matches) {
                    heroRow.style.removeProperty('--home-hero-height');
                    return;
                }
                var h = Math.ceil(middleCol.getBoundingClientRect().height);
                if (!h || h < 50) return;
                heroRow.style.setProperty('--home-hero-height', h + 'px');
            }

            var raf = null;
            function schedule() {
                if (raf) cancelAnimationFrame(raf);
                raf = requestAnimationFrame(function () {
                    raf = null;
                    syncHeroHeights();
                });
            }

            schedule();
            window.addEventListener('resize', schedule);
            if (mql.addEventListener) mql.addEventListener('change', schedule);
            window.addEventListener('load', schedule);
            return true;
        }

        if (initHeroHeightSync()) return;

        var tries = 0;
        var timer = setInterval(function () {
            tries++;
            if (initHeroHeightSync() || tries > 50) {
                clearInterval(timer);
            }
        }, 100);
    });
</script>

<!-- Mobile Search Bar -->
<div class="d-lg-none mb-3">
    <form action="{{ route('search') }}" method="GET" class="d-flex gap-2">
        <input type="text" name="q" class="form-control rounded-pill" placeholder="Tìm kiếm sản phẩm..." value="{{ request('q') }}" style="border: 2px solid var(--brand-secondary);">
        <button type="submit" class="btn btn-primary rounded-pill" style="width: 50px;">
            <i class="bi bi-search"></i>
        </button>
    </form>
</div>



<!-- Desktop Layout -->
<style>
    .home-hero-row .banner-swiper {
        margin-bottom: 0 !important;
    }
    .home-hero-row .home-promo-row {
        width: 100%;
    }
    .home-hero-row .home-promo-card {
        border-radius: 12px;
        overflow: hidden;
        background: #ffffff;
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
    }
    .home-hero-row .home-promo-image {
        width: 100%;
        height: 92px;
        overflow: hidden;
    }
    .home-hero-row .home-promo-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
    }
    .home-hero-row .home-category-sidebar > aside {
        height: auto;
        margin-bottom: 0 !important;
    }
    @media (min-width: 768px) {
        .home-hero-row {
            --home-hero-height: none;
        }
        .home-hero-row .home-category-sidebar > aside {
            height: var(--home-hero-height);
            overflow: hidden;
        }
        .home-hero-row .home-category-sidebar .category-sidebar-scroll {
            max-height: 100%;
            overflow-y: auto;
            overflow-x: visible;
        }
        .home-hero-row .home-hero-right {
            max-height: var(--home-hero-height);
            overflow-y: auto;
        }
    }
    @media (max-width: 991px) {
        .home-hero-row .home-promo-image {
            height: 78px;
        }
    }
</style>
<div class="row align-items-start mb-4 d-none d-md-flex home-hero-row">
    <!-- Menu dọc bên trái -->
    <div class="col-md-2">
        <div class="w-100 home-category-sidebar">
            @include('components.sidebar', ['categories' => $categories, 'overlay' => true])
        </div>
    </div>
    <!-- Banner lớn ở giữa + dải banner nhỏ phía dưới -->
    <div class="col-md-8 home-hero-middle">
        <div class="w-100 d-flex flex-column">
            <div class="flex-grow-1 d-flex align-items-center justify-content-center">
                @include('components.banner-side')
            </div>

            {{-- Dải banner nhỏ ngay dưới hero (giống PhongVu) --}}
            @if(isset($homePromoBanners) && $homePromoBanners->count())
                @php
                    $promoCount = $homePromoBanners->count();
                    // Tự chọn độ rộng cột cho đều hàng
                    $promoColClass = match(true) {
                        $promoCount === 1 => 'col-12',
                        $promoCount === 2 => 'col-12 col-md-6',
                        $promoCount === 3 => 'col-12 col-md-4',
                        default => 'col-12 col-md-3',
                    };
                @endphp
                <div class="home-promo-row mt-2">
                    <div class="row g-3">
                        @foreach($homePromoBanners as $promo)
                            <div class="{{ $promoColClass }}">
                                <a href="{{ $promo->link_url ?: '#' }}" class="text-decoration-none d-block h-100">
                                    <div class="home-promo-card h-100">
                                        <div class="home-promo-image">
                                            <img src="{{ $promo->image_url }}" alt="{{ $promo->title ?? 'Khuyến mãi' }}" loading="lazy" decoding="async">
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
    <!-- Demo Product Spotlight bên phải -->
    <div class="col-md-2 d-flex flex-column gap-3 justify-content-start home-hero-right">
        <!-- Live Chat Widget -->
        <div class="border-0 rounded-3 p-3 bg-white shadow-sm" style="border:1px solid #dbe7ff; background: linear-gradient(145deg, #ffffff 0%, #f5f9ff 100%);">
            <div class="text-center mb-3">
                <div class="badge mb-2" style="font-size:0.70em; background-color: var(--brand-primary); color:white;">CHAT TRỰC TUYẾN</div>
                <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:8px; height:8px; background-color: #28a745;">
                        <div class="rounded-circle bg-white" style="width:4px; height:4px;"></div>
                    </div>
                    <span style="font-size:0.75em; color:#6c757d;">Online</span>
                </div>
            </div>
            <div class="text-center mb-3">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-size:1.3em; background-color: var(--brand-secondary);">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div class="fw-bold mb-1" style="font-size:0.82em; color:#333;">Chuyên viên tư vấn</div>
                <div class="text-muted" style="font-size:0.75em;">Sẵn sàng hỗ trợ 24/7</div>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                    <i class="bi bi-star-fill text-warning"></i>
                </div>
            </div>
            <div class="testimonial-carousel">
                <div class="testimonial-item text-center mb-3" data-index="0">
                    <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">NT</div>
                    <div class="fw-bold mb-1" style="font-size:0.82em;">Nguyễn Thị Kiều Trang</div>
                    <div class="text-muted" style="font-size:0.74em; line-height:1.4;">"Sản phẩm chất lượng cao, nhân viên tư vấn nhiệt tình, giao hàng đúng hẹn!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="1" style="display:none;">
                    <div class="rounded-circle bg-success d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">NV</div>
                    <div class="fw-bold mb-1" style="font-size:0.82em;">Nguyễn Văn Thành</div>
                    <div class="text-muted" style="font-size:0.74em; line-height:1.4;">"Máy chấm công hoạt động ổn định, bảo hành tốt, giá cả hợp lý!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="2" style="display:none;">
                    <div class="rounded-circle bg-warning d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">LH</div>
                    <div class="fw-bold mb-1" style="font-size:0.82em;">Lê Hữu Phước</div>
                    <div class="text-muted" style="font-size:0.74em; line-height:1.4;">"Dịch vụ chuyên nghiệp, sản phẩm chính hãng, hỗ trợ kỹ thuật nhanh chóng!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="3" style="display:none;">
                    <div class="rounded-circle bg-info d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">TT</div>
                    <div class="fw-bold mb-1" style="font-size:0.82em;">Trần Thị Minh</div>
                    <div class="text-muted" style="font-size:0.74em; line-height:1.4;">"Camera giám sát chất lượng tốt, hình ảnh rõ nét, dễ cài đặt và sử dụng!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="4" style="display:none;">
                    <div class="rounded-circle bg-danger d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">PH</div>
                    <div class="fw-bold mb-1" style="font-size:0.82em;">Phạm Hoàng Nam</div>
                    <div class="text-muted" style="font-size:0.74em; line-height:1.4;">"Hệ thống khóa từ hoạt động ổn định, an toàn, tiết kiệm chi phí vận hành!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="5" style="display:none;">
                    <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">VH</div>
                    <div class="fw-bold mb-1" style="font-size:0.82em;">Võ Hoàng Anh</div>
                    <div class="text-muted" style="font-size:0.74em; line-height:1.4;">"Cổng phân làn chất lượng tốt, lắp đặt nhanh, vận hành ổn định!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="6" style="display:none;">
                    <div class="rounded-circle bg-dark d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">HT</div>
                    <div class="fw-bold mb-1" style="font-size:0.82em;">Hoàng Thị Lan</div>
                    <div class="text-muted" style="font-size:0.74em; line-height:1.4;">"Thiết bị báo động hiện đại, cảm biến nhạy, bảo mật cao!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="7" style="display:none;">
                    <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">LQ</div>
                    <div class="fw-bold mb-1" style="font-size:0.82em;">Lý Quốc Bình</div>
                    <div class="text-muted" style="font-size:0.74em; line-height:1.4;">"Hệ thống POS tính tiền nhanh, giao diện thân thiện, hỗ trợ nhiều phương thức thanh toán!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="8" style="display:none;">
                    <div class="rounded-circle bg-success d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">DT</div>
                    <div class="fw-bold mb-1" style="font-size:0.82em;">Đặng Thị Hương</div>
                    <div class="text-muted" style="font-size:0.74em; line-height:1.4;">"Phụ kiện đa dạng, chất lượng tốt, giá cả cạnh tranh!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="9" style="display:none;">
                    <div class="rounded-circle bg-warning d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">BT</div>
                    <div class="fw-bold mb-1" style="font-size:0.82em;">Bùi Thanh Tùng</div>
                    <div class="text-muted" style="font-size:0.74em; line-height:1.4;">"Phân tầng thang máy hoạt động chính xác, an toàn, tiết kiệm thời gian!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="10" style="display:none;">
                    <div class="rounded-circle bg-info d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">CM</div>
                    <div class="fw-bold mb-1" style="font-size:0.9em;">Châu Minh Khôi</div>
                    <div class="text-muted" style="font-size:0.8em; line-height:1.4;">"Dịch vụ bảo trì định kỳ tốt, nhân viên kỹ thuật chuyên nghiệp!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="11" style="display:none;">
                    <div class="rounded-circle bg-danger d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">NT</div>
                    <div class="fw-bold mb-1" style="font-size:0.9em;">Ngô Thị Mai</div>
                    <div class="text-muted" style="font-size:0.8em; line-height:1.4;">"Sản phẩm chính hãng, bảo hành uy tín, giá cả hợp lý!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="12" style="display:none;">
                    <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">TH</div>
                    <div class="fw-bold mb-1" style="font-size:0.9em;">Trịnh Hoàng Sơn</div>
                    <div class="text-muted" style="font-size:0.8em; line-height:1.4;">"Hệ thống tích hợp hoàn hảo, dễ quản lý, tiết kiệm chi phí!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="13" style="display:none;">
                    <div class="rounded-circle bg-dark d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">LH</div>
                    <div class="fw-bold mb-1" style="font-size:0.9em;">Lâm Hoàng Vân</div>
                    <div class="text-muted" style="font-size:0.8em; line-height:1.4;">"Giao hàng nhanh chóng, đóng gói cẩn thận, hướng dẫn sử dụng chi tiết!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="14" style="display:none;">
                    <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">PT</div>
                    <div class="fw-bold mb-1" style="font-size:0.9em;">Phan Thị Ngọc</div>
                    <div class="text-muted" style="font-size:0.8em; line-height:1.4;">"Camera IP chất lượng cao, hình ảnh sắc nét, dễ cài đặt!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="15" style="display:none;">
                    <div class="rounded-circle bg-success d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">VQ</div>
                    <div class="fw-bold mb-1" style="font-size:0.9em;">Vũ Quang Huy</div>
                    <div class="text-muted" style="font-size:0.8em; line-height:1.4;">"Máy chấm công vân tay chính xác, bảo mật cao, dễ sử dụng!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="16" style="display:none;">
                    <div class="rounded-circle bg-warning d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">HT</div>
                    <div class="fw-bold mb-1" style="font-size:0.9em;">Huỳnh Thị Linh</div>
                    <div class="text-muted" style="font-size:0.8em; line-height:1.4;">"Khóa điện tử hiện đại, an toàn, tiện lợi cho gia đình!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="17" style="display:none;">
                    <div class="rounded-circle bg-info d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">ND</div>
                    <div class="fw-bold mb-1" style="font-size:0.9em;">Nguyễn Đức Anh</div>
                    <div class="text-muted" style="font-size:0.8em; line-height:1.4;">"Hệ thống kiểm soát cửa ra vào hoạt động ổn định, bảo mật tốt!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="18" style="display:none;">
                    <div class="rounded-circle bg-danger d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">LT</div>
                    <div class="fw-bold mb-1" style="font-size:0.9em;">Lê Thị Thanh</div>
                    <div class="text-muted" style="font-size:0.8em; line-height:1.4;">"Dịch vụ khách hàng tận tâm, hỗ trợ 24/7, rất hài lòng!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="19" style="display:none;">
                    <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">TM</div>
                    <div class="fw-bold mb-1" style="font-size:0.9em;">Trần Minh Tuấn</div>
                    <div class="text-muted" style="font-size:0.8em; line-height:1.4;">"Sản phẩm đa dạng, đáp ứng mọi nhu cầu, giá cả phải chăng!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="20" style="display:none;">
                    <div class="rounded-circle bg-dark d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">PH</div>
                    <div class="fw-bold mb-1" style="font-size:0.9em;">Phạm Hoàng Long</div>
                    <div class="text-muted" style="font-size:0.8em; line-height:1.4;">"Hệ thống báo cháy tự động, cảm biến nhạy, an toàn tuyệt đối!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="21" style="display:none;">
                    <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">VH</div>
                    <div class="fw-bold mb-1" style="font-size:0.9em;">Vũ Hoàng Nam</div>
                    <div class="text-muted" style="font-size:0.8em; line-height:1.4;">"Máy POS tính tiền nhanh, giao diện thân thiện, dễ sử dụng!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="22" style="display:none;">
                    <div class="rounded-circle bg-success d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">NT</div>
                    <div class="fw-bold mb-1" style="font-size:0.9em;">Nguyễn Thị Hoa</div>
                    <div class="text-muted" style="font-size:0.8em; line-height:1.4;">"Camera dome chất lượng tốt, góc quay rộng, hình ảnh rõ nét!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="23" style="display:none;">
                    <div class="rounded-circle bg-warning d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">LT</div>
                    <div class="fw-bold mb-1" style="font-size:0.9em;">Lý Thị Bích</div>
                    <div class="text-muted" style="font-size:0.8em; line-height:1.4;">"Khóa từ thông minh, bảo mật cao, dễ cài đặt và sử dụng!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="24" style="display:none;">
                    <div class="rounded-circle bg-info d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">TH</div>
                    <div class="fw-bold mb-1" style="font-size:0.9em;">Trịnh Hoàng Anh</div>
                    <div class="text-muted" style="font-size:0.8em; line-height:1.4;">"Hệ thống quản lý tòa nhà tích hợp hoàn hảo, tiết kiệm chi phí!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="25" style="display:none;">
                    <div class="rounded-circle bg-danger d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">PM</div>
                    <div class="fw-bold mb-1" style="font-size:0.9em;">Phạm Minh Khang</div>
                    <div class="text-muted" style="font-size:0.8em; line-height:1.4;">"Dịch vụ bảo trì định kỳ tốt, nhân viên kỹ thuật chuyên nghiệp!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="26" style="display:none;">
                    <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">VQ</div>
                    <div class="fw-bold mb-1" style="font-size:0.9em;">Vũ Quang Minh</div>
                    <div class="text-muted" style="font-size:0.8em; line-height:1.4;">"Cổng xoay chất lượng tốt, lắp đặt nhanh, vận hành ổn định!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="27" style="display:none;">
                    <div class="rounded-circle bg-dark d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">NT</div>
                    <div class="fw-bold mb-1" style="font-size:0.9em;">Ngô Thị Lan</div>
                    <div class="text-muted" style="font-size:0.8em; line-height:1.4;">"Thiết bị báo động hiện đại, cảm biến nhạy, bảo mật cao!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="28" style="display:none;">
                    <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">LH</div>
                    <div class="fw-bold mb-1" style="font-size:0.9em;">Lê Hoàng Sơn</div>
                    <div class="text-muted" style="font-size:0.8em; line-height:1.4;">"Hệ thống kiểm soát ra vào hoạt động chính xác, an toàn!"</div>
                </div>
                <div class="testimonial-item text-center mb-3" data-index="29" style="display:none;">
                    <div class="rounded-circle bg-success d-inline-flex align-items-center justify-content-center mb-2" style="width:50px; height:50px; color:white; font-weight:bold;">TT</div>
                    <div class="fw-bold mb-1" style="font-size:0.9em;">Trần Thị Hương</div>
                    <div class="text-muted" style="font-size:0.8em; line-height:1.4;">"Sản phẩm chính hãng, bảo hành uy tín, giá cả hợp lý!"</div>
                </div>
            </div>

        </div>
        
       
        </div>
    </div>
</div>

<!-- Mobile Banner -->
<div class="d-lg-none mb-3">
    @include('components.banner-side')
</div>

<!-- Mobile Chat & Info Boxes -->
<div class="d-lg-none mb-3">
    <div class="row g-3">
        <!-- Mobile Chat Widget -->
        <div class="col-12">
            <div class="border-0 rounded-4 p-3 bg-white shadow-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-white d-flex align-items-center justify-content-center" style="width:45px; height:45px;">
                            <i class="bi bi-headset text-primary" style="font-size: 1.5em;"></i>
                        </div>
                        <div>
                            <div class="fw-bold mb-1" style="font-size:0.95em;">Chuyên viên tư vấn</div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:8px; height:8px; background-color: #00ff88;">
                                    <div class="rounded-circle bg-white" style="width:4px; height:4px;"></div>
                                </div>
                                <span style="font-size:0.8em; opacity: 0.9;">Online 24/7</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="badge bg-white text-primary mb-1" style="font-size:0.7em;">CHAT</div>
                    </div>
                </div>
                <button class="btn w-100 fw-bold" style="border-radius:12px; font-size:0.9em; background-color:white; color:#667eea; border:none; box-shadow: 0 4px 15px rgba(255,255,255,0.3);" data-bs-toggle="modal" data-bs-target="#chatModal">
                    <i class="bi bi-chat-dots me-2"></i>Bắt đầu chat ngay
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Desktop Banner -->


{{-- HOT SALE CUỐI TUẦN --}}
@if(isset($hotSaleProducts) && $hotSaleProducts->count())
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<style>
    @keyframes hotSaleSpin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    @keyframes hotSaleFlicker {
        0%   { text-shadow: 0 0 6px rgba(255, 180, 0, 0.55), 0 0 14px rgba(255, 60, 0, 0.35), 0 0 22px rgba(255, 0, 38, 0.25); filter: drop-shadow(0 0 6px rgba(255, 120, 0, 0.35)); }
        50%  { text-shadow: 0 0 10px rgba(255, 200, 0, 0.70), 0 0 20px rgba(255, 60, 0, 0.45), 0 0 30px rgba(255, 0, 38, 0.30); filter: drop-shadow(0 0 10px rgba(255, 120, 0, 0.50)); }
        100% { text-shadow: 0 0 7px rgba(255, 180, 0, 0.60), 0 0 16px rgba(255, 60, 0, 0.40), 0 0 24px rgba(255, 0, 38, 0.26); filter: drop-shadow(0 0 7px rgba(255, 120, 0, 0.40)); }
    }

    .hot-sale-section {
        border-radius: 26px;
        padding: 18px;
        background: #ffffff;
        border: 2px solid rgba(227, 0, 25, 0.35);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.10);
        position: relative;
        overflow: hidden;
    }
    .hot-sale-section::after {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        padding: 2px;
        border-radius: 26px;
        background: conic-gradient(from 0deg, rgba(227,0,25,0.15), rgba(227,0,25,0.65), rgba(227,0,25,0.15));
        -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        opacity: 0.75;
        animation: none;
    }
    .hot-sale-section::before {
        content: "";
        position: absolute;
        inset: 10px;
        border-radius: 20px;
        background: rgba(227, 0, 25, 0.03);
        border: 1px solid rgba(227, 0, 25, 0.15);
        pointer-events: none;
    }
    .hot-sale-banner {
        border-radius: 18px;
        overflow: hidden;
        position: relative;
        padding: 0;
        margin-bottom: 14px;
    }
    .hot-sale-banner img {
        width: 100%;
        height: auto;
        max-height: 220px;
        object-fit: contain;
        object-position: center;
        display: block;
    }
    .hot-sale-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        margin: 0;
        z-index: 2;
    }
    .hot-sale-countdown-float {
        position: absolute;
        top: 10px;
        right: 10px;
        margin: 0;
        z-index: 2;
        background: rgba(255, 0, 38, 0.45);
        backdrop-filter: blur(6px);
        border-radius: 14px;
        padding: 8px 10px;
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.26),
            inset 0 -10px 16px rgba(0,0,0,0.10),
            0 8px 18px rgba(0,0,0,0.12);
    }
    .hot-sale-inner {
        position: relative;
        z-index: 3;
        border-radius: 18px;
        background: rgba(255, 255, 255, 0.96);
        padding: 16px;
        margin-top: 0;
        box-shadow:
            0 10px 24px rgba(0,0,0,0.10),
            inset 0 1px 0 rgba(255,255,255,0.75);
    }
    .hot-sale-topbar {
        position: relative;
        z-index: 1;
        border-radius: 18px;
        padding: 10px 12px 9px;
        background: rgba(255, 0, 38, 0.62);
        backdrop-filter: blur(6px);
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.26),
            inset 0 -10px 16px rgba(0,0,0,0.10),
            0 8px 18px rgba(0,0,0,0.12);
        overflow: hidden;
    }
    .hot-sale-topbar .hot-sale-header {
        position: relative;
        z-index: 2;
    }
    .hot-sale-topbar .hot-sale-header::after {
        content: "";
        position: absolute;
        left: 0;
        right: 0;
        top: -6px;
        height: 20px;
        border-radius: 18px;
        background: linear-gradient(180deg, rgba(255,255,255,0.14), rgba(255,255,255,0));
        pointer-events: none;
    }
    .hot-sale-topbar::before,
    .hot-sale-topbar::after {
        content: "";
        position: absolute;
        top: 10px;
        width: 46px;
        height: 46px;
        border-radius: 14px;
        transform: rotate(12deg);
        background:
            radial-gradient(circle at 30% 30%, rgba(255,255,255,0.9), rgba(255,255,255,0) 55%),
            radial-gradient(circle at 70% 60%, rgba(255,255,255,0.7), rgba(255,255,255,0) 60%),
            linear-gradient(135deg, rgba(255,255,255,0.35), rgba(255,255,255,0));
        opacity: 0.55;
        pointer-events: none;
    }
    .hot-sale-topbar::before { left: 10px; }
    .hot-sale-topbar::after { right: 10px; transform: rotate(-12deg); }
    .hot-sale-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 0;
    }
    .hot-sale-ribbon {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: linear-gradient(180deg, rgba(255,255,255,0.22), rgba(255,255,255,0.10));
        border: 1px solid rgba(255,255,255,0.34);
        color: #fff;
        font-weight: 900;
        letter-spacing: 0.8px;
        padding: 10px 16px;
        border-radius: 14px;
        box-shadow:
            0 10px 18px rgba(0, 0, 0, 0.18),
            inset 0 1px 0 rgba(255,255,255,0.30),
            inset 0 -10px 18px rgba(0,0,0,0.14);
        text-transform: uppercase;
        position: relative;
        transform: translateZ(0);
    }
    .hot-sale-ribbon span {
        color: #fff;
        font-weight: 950;
        letter-spacing: 1px;
        animation: hotSaleFlicker 1.35s infinite alternate;
    }
    .hot-sale-ribbon i {
        animation: hotSaleFlicker 1.15s infinite alternate;
        filter: drop-shadow(0 0 10px rgba(255, 120, 0, 0.55));
    }
    .hot-sale-ribbon::before {
        content: "";
        position: absolute;
        left: 12px;
        right: 12px;
        top: 6px;
        height: 10px;
        border-radius: 999px;
        background: linear-gradient(180deg, rgba(255,255,255,0.30), rgba(255,255,255,0));
        pointer-events: none;
    }
    .hot-sale-ribbon::after {
        content: "";
        position: absolute;
        left: 10px;
        right: 10px;
        bottom: -12px;
        height: 14px;
        border-radius: 0 0 14px 14px;
        background:
            radial-gradient(18px 10px at 22px 0px, rgba(0,0,0,0.35), rgba(0,0,0,0) 70%),
            radial-gradient(18px 10px at calc(100% - 22px) 0px, rgba(0,0,0,0.35), rgba(0,0,0,0) 70%),
            linear-gradient(180deg, rgba(0,0,0,0.22), rgba(0,0,0,0.05));
        box-shadow:
            0 10px 18px rgba(0,0,0,0.25);
        transform: perspective(120px) rotateX(32deg);
        opacity: 0.55;
        pointer-events: none;
    }
    .hot-sale-ribbon i { font-size: 1.1em; }
    .hot-sale-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin: 12px 0 10px;
    }
    .hot-sale-pill {
        background: rgba(255,255,255,0.92);
        color: #b10012;
        border: 1px solid rgba(227, 0, 25, 0.22);
        border-radius: 999px;
        padding: 8px 12px;
        font-weight: 800;
        font-size: 0.95em;
        line-height: 1;
        white-space: nowrap;
        user-select: none;
    }
    .hot-sale-countdown {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #fff;
        font-weight: 900;
        white-space: nowrap;
    }
    .hot-sale-countdown .label {
        font-weight: 900;
        letter-spacing: 0.6px;
        text-transform: uppercase;
        font-size: 0.95em;
        opacity: 0.95;
    }
    .hot-sale-countdown .box {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        background: rgba(255,255,255,0.95);
        color: #e30019;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 18px rgba(0,0,0,0.12);
        font-variant-numeric: tabular-nums;
    }
    .hot-sale-section .swiper {
        padding: 8px 6px 12px;
    }
   
    .hot-sale-section .product-card-modern {
        background: rgba(255, 255, 255, 0.78);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        border: 1px solid rgba(255, 255, 255, 0.55) !important;
    }
    .hot-sale-section .swiper-button-next,
    .hot-sale-section .swiper-button-prev {
        width: 44px;
        height: 44px;
        border-radius: 999px;
        background: rgba(255,255,255,0.96);
        box-shadow: 0 10px 24px rgba(0,0,0,0.15);
        border: 1px solid rgba(227,0,25,0.18);
    }
    .hot-sale-section .swiper-button-next::after,
    .hot-sale-section .swiper-button-prev::after {
        font-size: 16px;
        font-weight: 900;
        color: #e30019;
    }
    .hot-sale-section .swiper-pagination-bullet-active { background: #e30019; }
    @media (max-width: 767.98px) {
        .hot-sale-header { flex-direction: column; align-items: flex-start; gap: 10px; }
        .hot-sale-countdown { align-self: flex-end; }
        .hot-sale-banner img { max-height: 320px; }
        .hot-sale-inner { margin-top: 0; }
        .hot-sale-ribbon {
            padding: 8px 10px;
            font-size: 0.9em;
            max-width: calc(100% - 20px);
        }
        .hot-sale-ribbon span {
            letter-spacing: 0.4px;
        }
        .hot-sale-countdown-float {
            top: auto;
            right: auto;
            left: 50%;
            bottom: 10px;
            transform: translateX(-50%);
            padding: 8px 10px;
            border-radius: 14px;
            max-width: calc(100% - 20px);
        }
        .hot-sale-countdown .box {
            width: 30px;
            height: 30px;
            border-radius: 9px;
            font-size: 0.95em;
        }
    }
</style>
<div class="hot-sale-section mb-4 reveal-on-scroll">
    <div class="hot-sale-inner">
        <div class="hot-sale-banner">
            <img src="{{ asset('images/anhhotsale.png') }}" alt="Hot Sale" loading="lazy" decoding="async">
        </div>
    <div class="swiper hot-sale-swiper">
        <div class="swiper-wrapper">
            @foreach($hotSaleProducts as $product)
                @php
                    $discount = $product->discount_percent ?? 0;
                    $oldPrice = (float) ($product->price ?? 0);
                    $finalPrice = $discount ? round($oldPrice * (100 - $discount) / 100, -3) : $oldPrice;
                    $agentPrice = (float) ($product->agency_price ?? 0);
                    $displayPrice = $isAgentUser && $agentPrice > 0 ? $agentPrice : $finalPrice;
                    $showListedStrike = $isAgentUser && $agentPrice > 0 && $oldPrice > 0;
                @endphp
                <div class="swiper-slide">
                    <div class="card h-100 border-0 shadow product-card-modern w-100 position-relative" style="box-shadow:0 6px 24px rgba(43,47,142,0.08); cursor:pointer;" onclick="window.location.href='{{ route('product.show', $product->slug) }}'">
                        @if($discount)
                            <span class="badge bg-danger position-absolute top-0 start-0 m-2" style="font-size:0.95em; z-index:2;">Giảm {{ $discount }}%</span>
                        @endif
                        <div class="product-img-wrap d-flex align-items-center justify-content-center" style="height:250px; background:#fff; border-radius:1.5rem 1.5rem 0 0; overflow:hidden;">
                            <a href="{{ route('product.show', $product->slug) }}" onclick="event.stopPropagation();" class="d-block w-100 h-100">
                                <img src="{{ asset('images/products/' . $product->image) }}" class="product-img-modern" alt="{{ $product->name }}" loading="lazy" decoding="async">
                            </a>
                        </div>
                        <div class="card-body d-flex flex-column p-3" style="flex:1 1 auto;">
                            <div class="mb-2 d-flex align-items-center gap-1" style="font-size:1.08em; color:#FFC107;">
                                <i class="bi bi-star-fill"></i>
                                <span class="fw-bold" style="color:#222;">{{ number_format(mt_rand(48, 50) / 10, 1) }}</span>
                            </div>
                            <h6 class="card-title fw-bold mb-2" style="font-size:0.85rem; font-weight:600; min-height:36px; color:#222; line-height:1.3; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;" title="{{ $product->name }}">{{ Str::limit($product->name, 45) }}</h6>
                            <div class="mb-2">
                                @if($displayPrice == 0)
                                    <span class="product-price-main" style="font-size:1.08em; color:#d32f2f; font-weight:700;">
                                        <a href="https://zalo.me/0982751039" target="_blank" style="text-decoration:none; color:inherit;">Liên hệ</a>
                                    </span>
                                @else
                                    <span class="product-price-main" style="font-size:1.08em; color:#d32f2f; font-weight:700;">{{ number_format($displayPrice, 0, ',', '.') }}đ</span>
                                    @if($showListedStrike)
                                        <span class="product-price-old" style="font-size:0.98em; color:#888; text-decoration:line-through; margin-left:6px;">{{ number_format($oldPrice, 0, ',', '.') }}đ</span>
                                    @elseif($discount)
                                        <span class="product-price-old" style="font-size:0.98em; color:#888; text-decoration:line-through; margin-left:6px;">{{ number_format($oldPrice, 0, ',', '.') }}đ</span>
                                    @endif
                                @endif
                            </div>
                            <div class="mt-2">
                                <form action="{{ route('cart.add', $product->id) }}" method="POST" class="d-inline-block w-100 add-to-cart-form" onclick="event.stopPropagation();">
                                    @csrf
                                    <button type="submit" class="btn btn-compact w-100 fw-bold d-flex align-items-center justify-content-center gap-2" style="border-radius:1.2rem; background: linear-gradient(135deg, #E30019 0%, #ff4d4d 100%); color:white; border:none; box-shadow:0 4px 12px rgba(227,0,25,0.3);">
                                        <i class="bi bi-cart-plus"></i> Mua ngay
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <!-- Add Arrows -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
        <!-- Add Pagination -->
        <div class="swiper-pagination"></div>
    </div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Testimonial Carousel
        let currentTestimonial = 0;
        const testimonials = document.querySelectorAll('.testimonial-item');
        
        function showTestimonial(index) {
            testimonials.forEach(item => item.style.display = 'none');
            testimonials[index].style.display = 'block';
        }
        
        function nextTestimonial() {
            currentTestimonial = (currentTestimonial + 1) % testimonials.length;
            showTestimonial(currentTestimonial);
        }
        
        // Auto change every 3 seconds
        showTestimonial(0);
        setInterval(nextTestimonial, 3000);

        const hotSaleCountdownEl = document.querySelector('.hot-sale-countdown');
        if (hotSaleCountdownEl) {
            const boxes = hotSaleCountdownEl.querySelectorAll('.box');
            if (boxes.length >= 3) {
                const storageKey = 'hotSaleCountdownEnd';
                const cycleMs = 24 * 60 * 60 * 1000;

                let endTime = parseInt(localStorage.getItem(storageKey) || '', 10);
                if (!Number.isFinite(endTime) || endTime <= Date.now()) {
                    endTime = Date.now() + cycleMs;
                    localStorage.setItem(storageKey, String(endTime));
                }

                const pad2 = (n) => String(n).padStart(2, '0');

                const tick = () => {
                    let diff = endTime - Date.now();
                    if (diff <= 0) {
                        endTime = Date.now() + cycleMs;
                        localStorage.setItem(storageKey, String(endTime));
                        diff = endTime - Date.now();
                    }

                    const totalSeconds = Math.floor(diff / 1000);
                    const hours = Math.floor(totalSeconds / 3600);
                    const minutes = Math.floor((totalSeconds % 3600) / 60);
                    const seconds = totalSeconds % 60;

                    boxes[0].textContent = pad2(hours);
                    boxes[1].textContent = pad2(minutes);
                    boxes[2].textContent = pad2(seconds);
                };

                tick();
                setInterval(tick, 1000);
            }
        }

        // Chat Modal Options (mở Zalo tin cậy hơn, tránh bị chặn popup)
        document.querySelectorAll('.chat-option-card').forEach(card => {
            card.addEventListener('click', function (e) {
                e.preventDefault();
                const service = this.getAttribute('data-service');
                let phoneNumber = '';

                switch (service) {
                    case 'consultation':
                        phoneNumber = '0982751039';
                        break;
                    case 'technical':
                        phoneNumber = '0919006976';
                        break;
                    case 'warranty':
                        phoneNumber = '0968220919';
                        break;
                }

                const zaloWebUrl = `https://zalo.me/${phoneNumber}`;

                // Cách 1: tạo anchor tạm và click (ít bị chặn hơn window.open trực tiếp)
                const a = document.createElement('a');
                a.href = zaloWebUrl;
                a.target = '_blank';
                a.rel = 'noopener noreferrer';
                a.style.display = 'none';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);

                // Cách 2 (fallback): nếu popup bị chặn, mở trên tab hiện tại
                setTimeout(() => {
                    try {
                        window.location.href = zaloWebUrl;
                    } catch (_) {}
                }, 150);

                // Đóng modal sau khi kích hoạt mở link
                const modal = bootstrap.Modal.getInstance(document.getElementById('chatModal'));
                if (modal) modal.hide();
            });
        });
        
        
        // Hot Sale Swiper
        const hotSaleSwiperEl = document.querySelector('.hot-sale-swiper');
        if (hotSaleSwiperEl) {
            const isMobile = window.matchMedia('(max-width: 575.98px)').matches;
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            const nextEl = hotSaleSwiperEl.querySelector('.swiper-button-next');
            const prevEl = hotSaleSwiperEl.querySelector('.swiper-button-prev');
            const paginationEl = hotSaleSwiperEl.querySelector('.swiper-pagination');

            new Swiper(hotSaleSwiperEl, {
                slidesPerView: 5,
                spaceBetween: 24,
                loop: !isMobile,
                preloadImages: false,
                watchSlidesProgress: true,
                observer: true,
                observeParents: true,
                navigation: (nextEl && prevEl) ? { nextEl, prevEl } : undefined,
                pagination: paginationEl ? { el: paginationEl, clickable: true } : undefined,
                speed: 650,
                effect: 'slide',
                grabCursor: true,
                autoplay: (reduceMotion || isMobile) ? false : {
                    delay: 2500,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                breakpoints: {
                    1200: { slidesPerView: 5, spaceBetween: 24 },
                    992: { slidesPerView: 4, spaceBetween: 20 },
                    768: { slidesPerView: 3, spaceBetween: 16 },
                    576: { slidesPerView: 2, spaceBetween: 12 },
                    0: { slidesPerView: 1.2, spaceBetween: 10 }
                }
            });
        }
    });
</script>
@endif
{{-- END HOT SALE CUỐI TUẦN --}}

{{-- SẢN PHẨM THEO DANH MỤC (CellphoneS style: Banner + Tabs + 4 sản phẩm) --}}
@if(isset($categoryWithProducts) && $categoryWithProducts->count())
    @foreach($categoryWithProducts as $cat)
        @if($cat->allProducts->count() > 0)
            <x-category-section :category="$cat" :products="$cat->allProducts" :wishlistProductIds="$wishlistProductIds ?? []" />
        @endif
    @endforeach
@endif
{{-- END SẢN PHẨM THEO DANH MỤC --}}

@auth
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.wishlist-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var productId = this.getAttribute('data-product-id');
            var icon = this.querySelector('i');
            fetch("{{ route('wishlist.toggle') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ product_id: productId })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'added') {
                    icon.classList.add('bi-heart-fill', 'text-danger');
                    icon.classList.remove('bi-heart');
                } else {
                    icon.classList.remove('bi-heart-fill', 'text-danger');
                    icon.classList.add('bi-heart');
                }
            });
        });
    });
});
</script>
@endauth

<style>
    .product-card .card-title {
        font-size: 1em !important;
        word-wrap: break-word;
        hyphens: auto;
    }
    .product-card .fw-bold {
        font-size: 1.05em !important;
    }
    .product-card-modern .card-title {
        font-size: 1.02em !important;
        word-wrap: break-word;
        hyphens: auto;
        text-overflow: ellipsis;
        overflow: hidden;
    }
    .product-card-modern .product-price-main {
        font-size: 1.02em !important;
    }
    .product-card-modern .product-price-old {
        font-size: 0.92em !important;
    }
    .product-card-modern .btn-compact {
        font-size: 0.92em !important;
    }
    .product-card-modern .card-body > .mb-2.d-flex {
        font-size: 1em !important;
    }
    .product-card-modern .card-body > div[style*="font-size:0.98em"] {
        font-size: 0.92em !important;
    }
.product-card { min-height: 370px; display: flex; flex-direction: column; }
.product-card .card-body { flex: 1 1 auto; display: flex; flex-direction: column; }
.product-card:hover {
    box-shadow: 0 6px 24px 0 rgba(0,0,0,0.12), 0 1.5px 6px 0 rgba(0,0,0,0.08);
    z-index: 2;
}
.text-truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.product-card-modern {
    border-radius: 1.5rem;
    box-shadow: 0 4px 24px 0 rgba(0,0,0,0.10);
    transition: box-shadow 0.25s, transform 0.18s;
    background: #fff;
    overflow: hidden;
    min-height: 420px;
    display: flex;
    flex-direction: column;
}
.product-card-modern:hover {
    box-shadow: 0 8px 32px 0 rgba(255,117,15,0.13), 0 2px 8px 0 rgba(0,0,0,0.08);
    transform: translateY(-4px) scale(1.025);
    z-index: 3;
}
.product-img-wrap {
    background: #fffbe9;
    border-radius: 1.5rem 1.5rem 0 0;
    overflow: hidden;
    height: 270px;
    padding: 12px;
}
.product-img-modern {
    width: 100%;
    height: 100%;
    max-width: 100%;
    object-fit: contain;
    transition: transform 0.25s;
    display: block;
}
.product-card-modern:hover .product-img-modern {
    transform: scale(1.08);
}
.btn-modern-main {
    background: var(--brand-secondary);
    color: #fff;
    border-radius: 1.2rem;
        font-size: 0.95em;
        padding: 0.55em 0.9em;
        box-shadow: 0 2px 8px 0 rgba(43,47,142,0.18);
    border: none;
    transition: background 0.18s, color 0.18s;
}
.btn-compact {
    font-size: 0.88em !important;
    padding: 0.45em 0.8em !important;
    line-height: 1.1 !important;
}
.btn-modern-main:hover {
    background: var(--brand-primary);
    color: #fff;
}
@media (min-width: 1200px) {
  .col-xl-5th {
    flex: 0 0 20%;
    max-width: 20%;
  }
}
/* Fix card sizing to prevent resizing issues */
.row.g-3 .col-6,
.row.g-3 .col-sm-6,
.row.g-3 .col-md-4,
.row.g-3 .col-lg-3,
.row.g-3 .col-xl-5th {
    display: flex;
    flex-shrink: 0;
}
.row.g-3 .col-6 .card,
.row.g-3 .col-sm-6 .card,
.row.g-3 .col-md-4 .card,
.row.g-3 .col-lg-3 .card,
.row.g-3 .col-xl-5th .card {
    width: 100%;
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
}
.pagination {
    border-radius: 1.5rem;
    box-shadow: 0 2px 12px 0 rgba(227,0,25,0.07);
    padding: 0.5rem 1.2rem;
    background: #fff;
    gap: 0.25rem;
}
.pagination .page-item .page-link {
    border-radius: 0.8rem !important;
    border: 1.5px solid var(--brand-secondary);
    color: var(--brand-secondary);
    font-weight: 500;
    margin: 0 2px;
    transition: all 0.18s;
    box-shadow: none;
}
.pagination .page-item.active .page-link,
.pagination .page-item .page-link:focus,
.pagination .page-item .page-link:hover {
    background: var(--brand-primary);
    color: #fff;
    border-color: var(--brand-primary);
    box-shadow: 0 2px 8px rgba(227,0,25,0.13);
}
.pagination .page-item.disabled .page-link {
    color: #ccc;
    background: #f8f9fa;
    border-color: #eee;
}
.hotline-box {
    box-shadow: 0 2px 10px 0 rgba(227,0,25,0.07) !important;
    border-radius: 1.2rem !important;
    min-height: 80px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-size: 0.98em;
    transition: box-shadow 0.18s, border 0.18s;
}
.hotline-box:hover {
    box-shadow: 0 4px 18px 0 rgba(227,0,25,0.13) !important;
    border-width: 2px !important;
    background: var(--brand-muted) !important;
}
.hotline-box:hover .bi,
.hotline-box:hover .fw-bold {
    color: var(--brand-primary) !important;
}
.hotline-box:not(:last-child) {
    margin-bottom: 30px !important;
}
.product-price-main {
    color: #e30019;
    font-size: 1.3em;
    font-weight: 700;
    line-height: 1.1;
    letter-spacing: 0.2px;
    font-family: 'Arial', 'Helvetica Neue', Helvetica, sans-serif;
    display: inline-block;
}
.product-price-old {
    color: #888;
    font-size: 1em;
    text-decoration: line-through;
    margin-left: 10px;
    font-weight: 400;
    vertical-align: middle;
}

/* Chat Modal Styles */
.chat-option-card {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.chat-option-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.chat-option-card:active {
    transform: translateY(0);
}

/* Banner nhỏ dưới hero - dạng thẻ trắng, nền gradient (phiên bản cũ) */
.home-promo-row .home-promo-card {
    border-radius: 16px;
    overflow: hidden;
    background: #ffffff;
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
    border: 1px solid #e5e7eb;
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    display: flex;
    flex-direction: column;
}
.home-promo-row .home-promo-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 26px rgba(0,0,0,0.12);
    border-color: rgba(227,0,25,0.35);
}
.home-promo-row .home-promo-image {
    height: 110px;
    background: linear-gradient(135deg, #fef3f2, #eff6ff);
    display: flex;
    align-items: center;
    justify-content: center;
}
.home-promo-row .home-promo-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.home-promo-row .home-promo-title {
    padding: 10px 12px 12px;
    font-size: 0.9rem;
    font-weight: 600;
    color: #111827;
    text-align: left;
}
@media (max-width: 767.98px) {
    .home-promo-row .home-promo-image {
        height: 90px;
    }
}

/* Mobile optimizations */
@media (max-width: 767.98px) {
  html, body { overflow-x: hidden; }
  .hot-sale-section { border-radius: 1rem; padding: 1rem; }
  .product-img-wrap { height: 210px; padding: 8px; }
  .product-img-modern { height: 100%; }
  .product-card-modern { border-radius: 1rem; min-height: 0; }
  .btn-modern-main { font-size: 0.95em; padding: 0.55em 0; }
  .swiper-button-next, .swiper-button-prev { display: none; }
  .swiper-pagination { bottom: 0; }
  .rounded-4 { border-radius: 14px !important; }
  .shadow-lg { box-shadow: 0 6px 18px rgba(0,0,0,0.08)!important; }
}
</style>

<script>
// Đảm bảo luôn bắt sự kiện click (kể cả khi phần Hot Sale không render)
document.addEventListener('click', function(e) {
    const card = e.target.closest('.chat-option-card');
    if (!card) return;
    e.preventDefault();

    const service = card.getAttribute('data-service');
    let phoneNumber = '';
    switch (service) {
        case 'consultation':
            phoneNumber = '0982751039';
            break;
        case 'technical':
            phoneNumber = '0919006976';
            break;
        case 'warranty':
            phoneNumber = '0968220919';
            break;
    }
    if (!phoneNumber) return;

    const zaloWebUrl = `https://zalo.me/${phoneNumber}`;
    const a = document.createElement('a');
    a.href = zaloWebUrl;
    a.target = '_blank';
    a.rel = 'noopener noreferrer';
    a.style.display = 'none';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);

    // Fallback
    setTimeout(() => {
        try { window.location.href = zaloWebUrl; } catch (_) {}
    }, 200);

    const modalEl = document.getElementById('chatModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.hide();
    }
});
</script>

<!-- Chat Modal -->
<div class="modal fade" id="chatModal" tabindex="-1" aria-labelledby="chatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="chatModalLabel">
                    <i class="bi bi-chat-dots text-primary me-2"></i>Chọn dịch vụ hỗ trợ
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="row g-3">
                    <!-- Tư vấn & Báo giá -->
                    <div class="col-12">
                        <div class="chat-option-card" data-service="consultation" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; padding: 24px; color: white; cursor: pointer; transition: all 0.3s ease; border: none; box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; box-shadow: 0 4px 15px rgba(255,255,255,0.3);">
                                        <i class="bi bi-headset text-primary" style="font-size: 1.8em;"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1" style="font-size: 1.1em;">Tư vấn sản phẩm & Báo giá</h6>
                                    <p class="mb-0" style="font-size: 0.95em; opacity: 0.95;">Nhận tư vấn chi tiết và báo giá tốt nhất</p>
                                </div>
                                <div>
                                    <i class="bi bi-arrow-right-circle text-white" style="font-size: 1.3em;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hỗ trợ kỹ thuật -->
                    <div class="col-12">
                        <div class="chat-option-card" data-service="technical" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 16px; padding: 24px; color: white; cursor: pointer; transition: all 0.3s ease; border: none; box-shadow: 0 8px 25px rgba(240, 147, 251, 0.3);">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; box-shadow: 0 4px 15px rgba(255,255,255,0.3);">
                                        <i class="bi bi-gear-wide-connected text-danger" style="font-size: 1.8em;"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1" style="font-size: 1.1em;">Hỗ trợ kỹ thuật</h6>
                                    <p class="mb-0" style="font-size: 0.95em; opacity: 0.95;">Giải đáp thắc mắc và hỗ trợ cài đặt</p>
                                </div>
                                <div>
                                    <i class="bi bi-arrow-right-circle text-white" style="font-size: 1.3em;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Thông tin bảo hành -->
                    <div class="col-12">
                        <div class="chat-option-card" data-service="warranty" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 16px; padding: 24px; color: white; cursor: pointer; transition: all 0.3s ease; border: none; box-shadow: 0 8px 25px rgba(79, 172, 254, 0.3);">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; box-shadow: 0 4px 15px rgba(255,255,255,0.3);">
                                        <i class="bi bi-patch-check-fill text-info" style="font-size: 1.8em;"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold mb-1" style="font-size: 1.1em;">Thông tin bảo hành</h6>
                                    <p class="mb-0" style="font-size: 0.95em; opacity: 0.95;">Tra cứu và hỗ trợ bảo hành sản phẩm</p>
                                </div>
                                <div>
                                    <i class="bi bi-arrow-right-circle text-white" style="font-size: 1.3em;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Sidebar -->
@include('components.mobile-sidebar', ['categories' => $categories])

<!-- Include Login Modal -->

<!-- Mobile Login Modal -->
<div id="mobileLoginModal" class="mobile-modal-overlay" style="display: none;">
    <div class="mobile-modal-content">
        <div class="mobile-modal-header">
            <h5 class="mobile-modal-title">
                <i class="bi bi-person-circle me-2"></i>Đăng nhập
            </h5>
            <button type="button" class="mobile-modal-close" onclick="closeMobileLoginModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="mobile-modal-body">
            <div id="mobileLoginAlert" class="alert" style="display: none;"></div>
            <form id="mobileLoginForm" method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label for="mobileLoginEmail" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope-at"></i></span>
                        <input type="email" class="form-control" id="mobileLoginEmail" name="email" required autofocus>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="mobileLoginPassword" class="form-label">Mật khẩu</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="mobileLoginPassword" name="password" required>
                    </div>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="mobileRemember" name="remember">
                    <label class="form-check-label" for="mobileRemember">Ghi nhớ đăng nhập</label>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a href="#" class="text-decoration-none">Quên mật khẩu?</a>
                    <a href="#" class="text-decoration-none">Đăng ký</a>
                </div>
                <button type="submit" class="btn btn-primary w-100" id="mobileLoginSubmitBtn">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.mobile-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.mobile-modal-content {
    background: white;
    border-radius: 18px;
    width: 100%;
    max-width: 400px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 8px 32px rgba(255, 117, 15, 0.2);
}

.mobile-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 20px 0 20px;
    border-bottom: none;
}

.mobile-modal-title {
    color: #FF750F;
    font-weight: bold;
    margin: 0;
}

.mobile-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #666;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.mobile-modal-close:hover {
    color: #333;
}

.mobile-modal-body {
    padding: 20px;
}

@media (max-width: 575.98px) {
    .mobile-modal-overlay {
        padding: 10px;
    }
    
    .mobile-modal-content {
        border-radius: 0;
        height: 100vh;
        max-height: 100vh;
    }
    
    .mobile-modal-header {
        padding: 15px 15px 0 15px;
    }
    
    .mobile-modal-body {
        padding: 15px;
    }
}

/* Alert styling for mobile modal */
#mobileLoginAlert {
    margin-bottom: 20px;
    border-radius: 12px;
    border: none;
    font-size: 0.95em;
    padding: 12px 16px;
}

#mobileLoginAlert.alert-success {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
    border-left: 4px solid #28a745;
}

#mobileLoginAlert.alert-danger {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    color: #721c24;
    border-left: 4px solid #dc3545;
}

#mobileLoginAlert.alert-warning {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    color: #856404;
    border-left: 4px solid #ffc107;
}

#mobileLoginAlert.alert-info {
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    color: #0c5460;
    border-left: 4px solid #17a2b8;
}

.reveal-on-scroll {
    opacity: 0;
    transform: translateY(18px);
    transition: opacity 600ms ease, transform 600ms ease;
    will-change: opacity, transform;
}

.reveal-on-scroll.reveal-visible {
    opacity: 1;
    transform: translateY(0);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Home page loaded');

    const revealEls = document.querySelectorAll('.reveal-on-scroll');
    if (revealEls.length) {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('reveal-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            root: null,
            threshold: 0.12,
            rootMargin: '0px 0px -10% 0px'
        });

        revealEls.forEach(el => revealObserver.observe(el));
    }
    
    // Mobile login button functionality
    const mobileLoginBtn = document.querySelector('.mobile-login-btn');
    if (mobileLoginBtn) {
        console.log('Mobile login button found');
        mobileLoginBtn.addEventListener('click', function(e) {
            console.log('Mobile login button clicked');
            e.preventDefault();
            openMobileLoginModal();
        });
    } else {
        console.log('Mobile login button not found');
    }
    
    // Close modal when clicking outside
    const modalOverlay = document.getElementById('mobileLoginModal');
    if (modalOverlay) {
        modalOverlay.addEventListener('click', function(e) {
            if (e.target === modalOverlay) {
                closeMobileLoginModal();
            }
        });
    }
    
    // Handle mobile login form submission
    const mobileLoginForm = document.getElementById('mobileLoginForm');
    if (mobileLoginForm) {
        mobileLoginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleMobileLogin();
        });
    }
});

function openMobileLoginModal() {
    console.log('Opening mobile login modal');
    const modal = document.getElementById('mobileLoginModal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        // Clear previous alerts
        hideMobileLoginAlert();
        // Focus on email input
        const emailInput = document.getElementById('mobileLoginEmail');
        if (emailInput) {
            setTimeout(() => emailInput.focus(), 100);
        }
    }
}

function closeMobileLoginModal() {
    console.log('Closing mobile login modal');
    const modal = document.getElementById('mobileLoginModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        // Clear form and alerts
        clearMobileLoginForm();
    }
}

function handleMobileLogin() {
    const form = document.getElementById('mobileLoginForm');
    const submitBtn = document.getElementById('mobileLoginSubmitBtn');
    const email = document.getElementById('mobileLoginEmail').value;
    const password = document.getElementById('mobileLoginPassword').value;
    
    // Validation
    if (!email || !password) {
        showMobileLoginAlert('Vui lòng nhập đầy đủ email và mật khẩu', 'danger');
        return;
    }
    
    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Đang xử lý...';
    
    // Lấy CSRF token
    const csrfToken = document.querySelector('input[name="_token"]').value;
    console.log('CSRF Token:', csrfToken);
    
    // Sử dụng FormData để gửi form
    const formData = new FormData();
    formData.append('email', email);
    formData.append('password', password);
    formData.append('remember', document.getElementById('mobileRemember').checked);

    console.log('Submitting form to:', form.action);
    console.log('Form data:', {
        email: email,
        password: password,
        remember: document.getElementById('mobileRemember').checked
    });
    
    // Submit form via AJAX
    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Kiểm tra content type
        const contentType = response.headers.get('content-type');
        console.log('Content-Type:', contentType);
        
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            // Nếu không phải JSON, có thể là redirect
            console.log('Non-JSON response, likely a redirect');
            return { success: true, redirect: true };
        }
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.redirect || data.success) {
            // Login successful
            showMobileLoginAlert('Đăng nhập thành công!', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            // Login failed
            const errorMessage = data.message || 'Email hoặc mật khẩu không đúng';
            showMobileLoginAlert(errorMessage, 'danger');
            // Reset password field
            document.getElementById('mobileLoginPassword').value = '';
            document.getElementById('mobileLoginPassword').focus();
        }
    })
    .catch(error => {
        console.error('Login error:', error);
        
        // Xử lý các loại lỗi khác nhau
        let errorMessage = 'Có lỗi xảy ra, vui lòng thử lại';
        
        if (error.message.includes('HTTP error')) {
            if (error.message.includes('422')) {
                errorMessage = 'Email hoặc mật khẩu không đúng';
            } else if (error.message.includes('419')) {
                errorMessage = 'Phiên làm việc hết hạn, vui lòng tải lại trang';
            } else if (error.message.includes('500')) {
                errorMessage = 'Lỗi server, vui lòng thử lại sau';
            }
        }
        
        showMobileLoginAlert(errorMessage, 'danger');
    })
    .finally(() => {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập';
    });
}

function showMobileLoginAlert(message, type) {
    const alertDiv = document.getElementById('mobileLoginAlert');
    if (alertDiv) {
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = message;
        alertDiv.style.display = 'block';
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            hideMobileLoginAlert();
        }, 5000);
    }
}

function hideMobileLoginAlert() {
    const alertDiv = document.getElementById('mobileLoginAlert');
    if (alertDiv) {
        alertDiv.style.display = 'none';
    }
}

function clearMobileLoginForm() {
    const form = document.getElementById('mobileLoginForm');
    if (form) {
        form.reset();
        hideMobileLoginAlert();
    }
}
</script>

@endsection 