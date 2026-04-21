@php
    $topStripBanner = \App\Models\Banner::query()
        ->active()
        ->position('top_strip')
        ->orderBy('sort_order')
        ->first();
@endphp

@if($topStripBanner)
<!-- Thanh banner mỏng trên cùng (PhongVu-style) -->
<div class="pv-top-strip d-none d-md-block">
    <a href="{{ $topStripBanner->link_url ?: '#' }}" class="pv-top-strip__link" @if($topStripBanner->link_url) target="_blank" rel="noopener" @endif aria-label="{{ $topStripBanner->title ?: 'Khuyến mãi' }}">
        @if($topStripBanner->is_video)
            <video class="pv-top-strip__bg-video" autoplay muted loop playsinline preload="metadata">
                @php
                    $mediaPath = strtolower(parse_url($topStripBanner->media_url, PHP_URL_PATH) ?: $topStripBanner->media_url);
                    $mediaType = str_ends_with($mediaPath, '.webm') ? 'video/webm' : 'video/mp4';
                @endphp
                <source src="{{ $topStripBanner->media_url }}" type="{{ $mediaType }}">
            </video>
            <span class="pv-top-strip__bg-overlay"></span>
        @else
            <span class="pv-top-strip__bg" style="background-image:url('{{ $topStripBanner->image_url }}')"></span>
        @endif
        <span class="pv-top-strip__content container-fluid">
            <span class="pv-top-strip__items">
                <span class="pv-top-strip__item"><i class="bi bi-truck"></i> Giao nhanh</span>
                <span class="pv-top-strip__item"><i class="bi bi-shield-check"></i> Chính hãng</span>
                <span class="pv-top-strip__item"><i class="bi bi-headset"></i> Hỗ trợ 24/7</span>
                <span class="pv-top-strip__item"><i class="bi bi-telephone"></i> Hotline: 0903 222 183</span>
            </span>
            <span class="pv-top-strip__cta">Xem ưu đãi <i class="bi bi-arrow-right"></i></span>
        </span>
    </a>
</div>

<style>
    .pv-top-strip {
        position: relative;
        height: 44px;
        min-height: 44px;
        max-height: 44px;
        overflow: hidden;
        background: #111827;
        border-bottom: 1px solid rgba(17, 24, 39, 0.12);
        contain: layout paint;
        transform: translateZ(0);
        backface-visibility: hidden;
    }
    .pv-top-strip__link {
        display: block;
        height: 44px;
        text-decoration: none;
        color: #fff;
        position: relative;
        transform: translateZ(0);
    }
    .pv-top-strip__bg-video {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        transform: translateZ(0);
        filter: saturate(1.05) contrast(1.05);
        will-change: transform;
    }
    .pv-top-strip__bg-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(0,0,0,0.35), rgba(0,0,0,0.15), rgba(0,0,0,0.35));
    }
    .pv-top-strip__bg {
        position: absolute;
        inset: 0;
        background-size: cover;
        background-position: 50% 50%;
        filter: saturate(1.05) contrast(1.05);
        transform: translateZ(0);
        animation: pvTopStripPan 18s linear infinite;
        will-change: transform, background-position;
    }
    /* lớp phủ để chữ dễ đọc (chỉ khi dùng ảnh nền) */
    .pv-top-strip__bg::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, rgba(0,0,0,0.35), rgba(0,0,0,0.15), rgba(0,0,0,0.35));
    }
    .pv-top-strip__content {
        position: relative;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        font-size: 0.9rem;
        font-weight: 600;
        text-shadow: 0 2px 10px rgba(0,0,0,0.35);
    }
    .pv-top-strip__items {
        display: inline-flex;
        align-items: center;
        gap: 14px;
        white-space: nowrap;
        overflow: hidden;
        mask-image: linear-gradient(90deg, rgba(0,0,0,0), #000 8%, #000 92%, rgba(0,0,0,0));
    }
    .pv-top-strip__item {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        opacity: 0.96;
    }
    .pv-top-strip__cta {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        border-radius: 999px;
        background: rgba(255,255,255,0.14);
        border: 1px solid rgba(255,255,255,0.22);
        backdrop-filter: blur(6px);
    }
    .pv-top-strip__link:hover .pv-top-strip__cta {
        background: rgba(255,255,255,0.20);
    }
    @keyframes pvTopStripPan {
        0% { background-position: 50% 50%; transform: scale(1.05); }
        50% { background-position: 60% 50%; transform: scale(1.08); }
        100% { background-position: 50% 50%; transform: scale(1.05); }
    }
</style>
@endif

<!-- Topbar: KHÔNG sticky, chỉ chạy ở trên cùng, cuộn xuống sẽ ẩn -->
<div class="header-topbar py-1 small" style="background: var(--brand-primary); color: #fff; border-bottom: 1px solid #F1F1F1;">
    <div class="container-fluid d-flex align-items-center flex-wrap" style="font-size: 1em;">
        <div class="flex-grow-1 overflow-hidden" style="min-width: 0;">
            <div class="pv-marquee" aria-label="Thông tin ưu đãi">
                <div class="pv-marquee__track">
                    <span class="pv-marquee__item"><i class="bi bi-gift"></i> Ưu đãi doanh nghiệp</span>
                    <span class="pv-marquee__sep">&nbsp;&nbsp;</span>
                    <span class="pv-marquee__item"><i class="bi bi-truck"></i> Giao hàng nhanh toàn quốc</span>
                    <span class="pv-marquee__sep">&nbsp;&nbsp;</span>
                    <span class="pv-marquee__item"><i class="bi bi-shield-check"></i> Chính hãng - Bảo hành 12 tháng</span>
                    <span class="pv-marquee__sep">&nbsp;&nbsp;</span>
                    <span class="pv-marquee__item"><i class="bi bi-geo-alt"></i> Cửa hàng gần bạn</span>
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap topbar-links" style="margin-left: 32px;">
            <a href="https://www.google.com/maps/search/?api=1&query=96%20%C4%90%C6%B0%E1%BB%9Dng%20s%E1%BB%91%2014%2C%20KDC%20Him%20Lam%2C%20Ph%C6%B0%E1%BB%9Dng%20T%C3%A2n%20H%C6%B0ng%2C%20TP.HCM" target="_blank" rel="noopener" class="d-flex align-items-center gap-1 topbar-link" style="color:#fff; text-decoration:none; font-weight:500;">
                <i class="bi bi-geo-alt"></i> Cửa hàng gần bạn
            </a>
            <a href="{{ route('orders.lookup') }}" class="d-flex align-items-center gap-1 topbar-link" style="color:#fff; text-decoration:none; font-weight:500;">
                <i class="bi bi-receipt"></i> Tra cứu đơn hàng
            </a>
            <a href="{{ route('warranty.check') }}" class="d-flex align-items-center gap-1 topbar-link" style="color:#fff; text-decoration:none; font-weight:500;">
                <i class="bi bi-shield-check"></i> Tra cứu bảo hành
            </a>
            <a href="https://zalo.me/0903222183" onclick="event.preventDefault(); if (typeof openZalo === 'function') { openZalo('0903222183'); } else { window.open('https://zalo.me/0903222183', '_blank'); }" class="d-flex align-items-center gap-1 topbar-link" style="color:#fff; text-decoration:none; font-weight:500;">
                <i class="bi bi-telephone"></i> 0903 222 183
            </a>
            <a href="#" class="d-flex align-items-center gap-1 topbar-link" style="color:#fff; text-decoration:none; font-weight:500;">
                <i class="bi bi-phone"></i> Tải ứng dụng
            </a>
        </div>
    </div>
</div>

<style>
    .pv-marquee {
        width: 100%;
        overflow: hidden;
        white-space: nowrap;
    }
    .pv-marquee__track {
        display: inline-flex;
        align-items: center;
        gap: 0;
        white-space: nowrap;
        will-change: transform;
        animation: pvMarqueeScroll 16s linear infinite;
        transform: translate3d(0,0,0);
    }
    .pv-marquee__item { display: inline-flex; align-items: center; gap: 6px; }
    .pv-marquee__sep { opacity: 0.7; }
    @keyframes pvMarqueeScroll {
        0% { transform: translateX(100%); }
        100% { transform: translateX(-100%); }
    }
    @media (prefers-reduced-motion: reduce) {
        .pv-marquee__track { animation: none; }
    }
</style>

<!-- Đảm bảo đã import Bootstrap Icons -->
<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
<link rel="dns-prefetch" href="//cdn.jsdelivr.net">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<!-- Navbar sticky: chỉ logo (to) + menu + search + giỏ hàng + đăng nhập -->
<nav class="navbar navbar-expand-lg shadow-sm sticky-top" style="background: #fff; top:0; z-index:1039; min-height:110px; height:110px; display:flex; align-items:center; border-bottom:1.5px solid #F1F1F1;">
    <div class="container d-flex align-items-center justify-content-between flex-nowrap position-relative" style="height:110px;">
        <!-- Logo to, không có chữ VIKHANG -->
        <a class="navbar-brand d-flex align-items-center justify-content-center me-3 mb-2 mb-lg-0" href="/" style="gap: 12px; min-width:120px; height:100%; align-items:center;">
            <img src="/logovigilance.jpg" alt="Logo" style="height:70px; max-height:70px; display:block; margin:0 auto;">
        </a>
        <!-- Danh mục: Đặt lại về vị trí cũ cạnh logo -->
        <div class="dropdown me-2 mb-2 mb-lg-0 header-category">
            <button class="btn btn-light d-flex align-items-center fw-bold px-3 header-action-btn header-action-btn-lg" data-bs-toggle="dropdown" style="color:var(--brand-secondary); border:1.5px solid var(--brand-secondary); background:#fff;">
                <i class="bi bi-grid-3x3-gap-fill me-2" style="color:var(--brand-secondary);"></i> Danh mục <i class="bi bi-chevron-down ms-1"></i>
            </button>
            <ul class="dropdown-menu p-2" style="min-width:260px; max-height:60vh; overflow:auto;">
                @php
                  if(!function_exists('renderHeaderMenu')){
                    function renderHeaderMenu($nodes,$level=0){
                      foreach($nodes as $node){
                        echo '<li class="position-relative header-cat-item">';
                        echo '<a class="dropdown-item d-flex align-items-center" href="'.route('category.show',$node->slug).'">'.e($node->name);
                        if($node->children && $node->children->count()){
                          echo '<i class="bi bi-chevron-down ms-auto small"></i>';
                        }
                        echo '</a>';
                        if($node->children && $node->children->count()){
                          echo '<ul class="list-unstyled ps-3 submenu">';
                          renderHeaderMenu($node->children,$level+1);
                          echo '</ul>';
                        }
                        echo '</li>';
                      }
                    }
                  }
                  renderHeaderMenu($categories);
                @endphp
            </ul>
        </div>
        <!-- Chọn tỉnh/thành -->
        <div class="dropdown me-2 mb-2 mb-lg-0">
            <button class="btn btn-light d-flex align-items-center px-3 header-action-btn header-action-btn-lg" data-bs-toggle="dropdown" style="color:var(--brand-secondary); border:1.5px solid var(--brand-secondary); background:#fff;">
                <i class="bi bi-geo-alt-fill me-2" style="color:var(--brand-secondary);"></i> Hồ Chí Minh <i class="bi bi-chevron-down ms-1"></i>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">Hà Nội</a></li>
                <li><a class="dropdown-item" href="#">Đà Nẵng</a></li>
                <li><a class="dropdown-item" href="#">Hồ Chí Minh</a></li>
                <li><a class="dropdown-item" href="#">Cần Thơ</a></li>
            </ul>
        </div>
        <!-- Thanh tìm kiếm -->
        <form class="d-flex flex-grow-1 mx-3 mb-2 mb-lg-0 position-relative" role="search" style="max-width:500px;" method="GET" action="{{ route('search') }}">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0" style="border:1.5px solid var(--brand-secondary);"><i class="bi bi-search" style="color:var(--brand-secondary);"></i></span>
                <input class="form-control border-start-0" type="search" name="q" id="mainSearchInput" placeholder="Bạn muốn mua gì hôm nay?" aria-label="Search" style="border:1.5px solid var(--brand-secondary);">
            </div>
            @if(isset($featuredProducts) && $featuredProducts->count())
            <div id="search-featured-dropdown" class="search-featured-dropdown" style="display:none;">
                <div class="px-3 pt-3 pb-2">
                    <div class="fw-bold mb-2" style="font-size:1.08em; color:var(--brand-secondary);">Sẩn Phẩm Mới Nhất <i class="bi bi-fire" style="color:var(--brand-primary);"></i></div>
                    <div class="row g-2">
                        @foreach($featuredProducts as $product)
                        <div class="col-12 d-flex align-items-center gap-2 mb-2">
                            <a href="{{ route('product.show', $product->slug) }}" class="d-flex align-items-center gap-2 text-decoration-none search-featured-item">
                                @php
                                    $imageName = $product->image ?? '';
                                    $imageDir = public_path('images/products/');
                                    $imageUrl = null;
                                    if ($imageName && file_exists($imageDir . $imageName)) {
                                        $imageUrl = asset('images/products/' . $imageName);
                                    } else {
                                        $baseName = pathinfo($imageName, PATHINFO_FILENAME);
                                        $foundName = null;
                                        foreach (['webp','png','jpg','jpeg','JPG','PNG','JPEG'] as $ext) {
                                            if ($baseName && file_exists($imageDir . $baseName . '.' . $ext)) {
                                                $foundName = $baseName . '.' . $ext;
                                                break;
                                            }
                                        }
                                        if ($foundName) {
                                            $imageUrl = asset('images/products/' . $foundName);
                                        } else {
                                            $imageUrl = asset('logovigilance.jpg');
                                        }
                                    }
                                @endphp
                                @php
                                    $primaryUrl = asset('images/products/' . basename($imageUrl));
                                    $fallbackLogo = asset('logovigilance.jpg');
                                @endphp
                                <img src="{{ $primaryUrl }}" alt="{{ $product->name }}" style="width:38px; height:38px; object-fit:cover; border-radius:10px; box-shadow:0 2px 8px #007bff22;"
                                     onerror="this.onerror=null;this.src='{{ $fallbackLogo }}';">
                                <span class="fw-semibold" style="color:#222; font-size:1.04em; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:180px;">{{ $product->name }}</span>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </form>
        <!-- Giỏ hàng -->
        <a href="/cart" class="btn btn-outline-light position-relative d-flex align-items-center me-2 mb-2 mb-lg-0 header-action-btn" style="color:var(--brand-secondary); border:1.5px solid var(--brand-secondary); background:#fff;">
            <i class="bi bi-cart3 fs-4" style="color:var(--brand-secondary);"></i>
            <span class="ms-1 d-none d-md-inline" style="color:var(--brand-secondary);">Giỏ hàng</span>
            @if(isset($cartCount) && $cartCount > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.8em;">{{ $cartCount }}</span>
            @else
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary" style="font-size:0.8em;">0</span>
            @endif
        </a>
        {{-- Đã xóa hoàn toàn nút Yêu thích trên header --}}
        <!-- Đăng nhập -->
        @guest
            <a href="{{ route('login') }}" class="btn d-flex align-items-center gap-2 ms-2 header-action-btn" style="border-radius:2em; height:48px; border:1.5px solid var(--brand-secondary); color:var(--brand-secondary); background:#fff;">
                <i class="bi bi-person-circle"></i> Đăng nhập
            </a>
        @else
            <div class="dropdown ms-2">
                <a href="#" class="btn btn-outline-primary d-flex align-items-center gap-2 dropdown-toggle header-action-btn"
                   id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                   style="border-radius:2em; height:48px; font-weight:600;">
                    <i class="bi bi-person-circle"></i>
                    {{ Str::words(Auth::user()->name, 1, '') }}
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="{{ route('orders.index') }}"><i class="bi bi-bag"></i> Đơn hàng của tôi</a></li>
                    <li><a class="dropdown-item" href="{{ route('wishlist.index') }}"><i class="bi bi-heart"></i> Yêu thích</a></li>
                    @if(Auth::user()->role === 'admin')
                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> Trang quản trị</a></li>
                    @endif
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="#"
                 onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                  <i class="bi bi-box-arrow-right"></i> Đăng xuất
              </a>
                    </li>
                </ul>
            </div>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        @endguest
    </div>
</nav>

<style>
.navbar .header-action-btn {
    font-size: 0.90em !important;
    font-weight: 600 !important;
    line-height: 1.1;
    height: 42px;
    padding-top: 0;
    padding-bottom: 0;
    border-radius: 8px !important;
    display: inline-flex;
    align-items: center;
}
.navbar .header-action-btn-lg {
    font-size: 1.00em !important;
}

.navbar .input-group-text,
.navbar #mainSearchInput {
    height: 42px;
}
.navbar .input-group-text {
    border-radius: 8px 0 0 8px !important;
}
.navbar #mainSearchInput {
    border-radius: 0 8px 8px 0 !important;
}
.navbar #mainSearchInput {
    font-size: 0.85em;
}
.navbar #mainSearchInput::placeholder {
    font-size: 0.85em;
}
.topbar-link:hover {
    color: #fff;
    background: #00B894;
    border-radius: 4px;
    padding: 2px 8px;
    transition: all 0.15s;
}
.navbar .btn, .navbar .btn:focus {
    transition: all 0.18s;
    box-shadow: none;
}
.navbar .btn:hover, .navbar .btn:active {
    background: #007BFF !important;
    color: #fff !important;
    border-color: #007BFF !important;
}
.navbar .btn-outline-success:hover, .navbar .btn-outline-success:active {
    background: #00B894 !important;
    color: #fff !important;
    border-color: #00B894 !important;
}
.navbar .btn-outline-primary:hover, .navbar .btn-outline-primary:active {
    background: #007BFF !important;
    color: #fff !important;
    border-color: #007BFF !important;
}
.navbar .btn-outline-light:hover, .navbar .btn-outline-light:active {
    background: #007BFF !important;
    color: #fff !important;
    border-color: #007BFF !important;
}
.navbar .btn .bi {
    transition: color 0.18s;
}
.navbar .btn:hover .bi, .navbar .btn:active .bi {
    color: #fff !important;
}
.sticky-category-floating {
    position: fixed;
    top: 110px;
    left: 24px;
    z-index: 1050;
    border-radius: 12px;
    box-shadow: 0 4px 24px 0 rgba(0,0,0,0.10);
    background: transparent;
    animation: stickyFadeIn 0.4s;
}
.sticky-category-btn-float {
    border-radius: 12px !important;
    background: #fffbe9 !important;
    color: #FF750F !important;
    font-weight: bold;
    box-shadow: 0 2px 8px 0 rgba(0,0,0,0.06);
    border: 1.5px solid #FFE5B4;
    transition: box-shadow 0.2s;
}
.sticky-category-btn-float:hover {
    background: #FFE5B4 !important;
    color: #FF750F !important;
    box-shadow: 0 6px 24px 0 rgba(0,0,0,0.13);
}
@keyframes stickyFadeIn {
    from { opacity: 0; transform: translateY(-16px); }
    to { opacity: 1; transform: none; }
}
@media (max-width: 991px) {
    .sticky-category-floating { display: none !important; }
    .navbar {
        height: auto !important;
        min-height: unset !important;
        padding-top: 10px;
        padding-bottom: 10px;
    }
    .navbar .container {
        height: auto !important;
        flex-direction: column;
        align-items: stretch;
        flex-wrap: wrap;
    }
    .navbar .navbar-brand {
        margin-bottom: 8px !important;
    }
    .navbar form {
        margin: 10px 0;
        max-width: 100% !important;
        width: 100% !important;
    }
    .navbar .header-action-btn {
        width: 100%;
        justify-content: center;
    }
    .navbar .dropdown {
        width: 100%;
    }
    .navbar .dropdown > .btn,
    .navbar .dropdown > a {
        width: 100%;
        justify-content: center;
    }
    
    /* Đảm bảo dropdown menu hiển thị đúng trên mobile */
    .dropdown-menu {
        position: absolute !important;
        right: 0 !important;
        left: auto !important;
        min-width: 180px !important;
    }
}
    .search-featured-dropdown {
        position: absolute;
        top: 110%;
        left: 0;
        width: 100%;
        background: #fff;
        border-radius: 1.2em;
        box-shadow: 0 8px 32px 0 #007bff22;
        z-index: 1051;
        min-width: 320px;
        max-width: 420px;
        border: 1.5px solid #F1F1F1;
        animation: fadeInDropdown 0.18s;
    }
    @keyframes fadeInDropdown {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: none; }
    }
    .search-featured-item:hover span {
        color: #007BFF;
        text-decoration: underline;
    }
    .search-featured-item img {
        transition: transform 0.18s;
    }
    .search-featured-item:hover img {
        transform: scale(1.08);
        box-shadow: 0 4px 16px #007bff33;
    }
</style>

<style>
.user-dropdown-fix {
  white-space: nowrap !important;
  overflow: visible !important;
}

/* Đảm bảo dropdown menu hiển thị đúng */
.dropdown-menu {
  z-index: 1050 !important;
  min-width: 200px !important;
}

.dropdown-item {
  padding: 8px 16px !important;
  font-size: 0.95em !important;
}

.dropdown-item:hover {
  background-color: #f8f9fa !important;
  color: #007BFF !important;
}

/* Hover to open nested submenu like sidebar */
.header-category .dropdown-menu .submenu { display: none; }
.header-category .dropdown-menu li.header-cat-item:hover > .submenu { display: block; }
.header-category .dropdown-menu { overflow: visible; }

.dropdown-divider {
  margin: 4px 0 !important;
}
</style>

<script>
// Hiện dropdown khi focus/gõ vào input, ẩn khi blur hoặc click ngoài
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('mainSearchInput');
    const dropdown = document.getElementById('search-featured-dropdown');
    if(searchInput && dropdown) {
        searchInput.addEventListener('focus', function() {
            dropdown.style.display = 'block';
        });
        searchInput.addEventListener('input', function() {
            dropdown.style.display = this.value.trim() === '' ? 'block' : 'none';
        });
        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target) && e.target !== searchInput) {
                dropdown.style.display = 'none';
            }
        });
    }
});
</script> 