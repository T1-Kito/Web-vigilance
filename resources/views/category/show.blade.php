@extends('layouts.user')

@section('title', (isset($category) ? $category->name . ' - ' : '') . 'Vigilance')

 @php
     $categoryCanonical = isset($category) ? route('category.show', $category->slug) : url()->current();
     $hasFacetParams = request()->hasAny(['brand', 'sort', 'filter', 'page']);
     $isAgentUser = auth()->check() && (string) auth()->user()->role === 'agent';
 @endphp

@section('meta_description', (isset($category) ? ('Mua ' . $category->name . ' chính hãng, giá tốt, nhiều lựa chọn, tư vấn 24/7. Xem ngay danh sách sản phẩm ' . $category->name . ' tại Vigilance.') : 'Danh mục sản phẩm Vigilance.'))
@section('canonical', $categoryCanonical)
@if($hasFacetParams)
    @section('meta_robots', 'noindex,follow')
@endif

@push('structured_data')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Trang chủ',
            'item' => url('/')
        ],
        [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => isset($category) ? $category->name : 'Danh mục',
            'item' => url()->current()
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>
@endpush

@section('content')
<style>
.cat-mobile-header {
    position: sticky;
    top: 0;
    z-index: 1040;
    background: #fff;
    border-bottom: 1px solid rgba(31,45,61,0.10);
}

.cat-mobile-header .cat-mh-btn {
    width: 40px;
    height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
}

.category-toolbar {
    background: #fff;
    border: 1px solid rgba(31,45,61,0.08);
    border-radius: 16px;
    padding: 12px 14px;
    box-shadow: 0 10px 30px rgba(31,45,61,0.06);
    position: static;
    top: auto;
    z-index: auto;
}

.category-header-card {
    background: #fff;
    border: 1px solid rgba(31,45,61,0.08);
    border-radius: 16px;
    padding: 12px 14px;
    box-shadow: 0 10px 30px rgba(31,45,61,0.06);
    position: static;
    top: auto;
    z-index: auto;
}

.category-header-card .category-toolbar {
    background: transparent;
    border: none;
    border-radius: 0;
    padding: 0;
    box-shadow: none;
    position: static;
    top: auto;
}

.brand-filter {
    background: #fff;
    border: 1px solid rgba(31,45,61,0.08);
    border-radius: 16px;
    padding: 10px 12px;
    box-shadow: 0 10px 30px rgba(31,45,61,0.05);
}

.category-header-card .brand-filter {
    background: transparent;
    border: none;
    border-radius: 0;
    padding: 10px 0 0;
    box-shadow: none;
}

.brand-filter-scroll {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    padding: 2px 2px;
    scroll-snap-type: x mandatory;
    -webkit-overflow-scrolling: touch;
}

.brand-filter-scroll::-webkit-scrollbar {
    height: 6px;
}

.brand-filter-scroll::-webkit-scrollbar-thumb {
    background: rgba(31,45,61,0.14);
    border-radius: 99px;
}

.brand-chip {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    border-radius: 99px;
    background: #fff;
    color: #1f2d3d;
    text-decoration: none;
    white-space: nowrap;
    font-weight: 700;
    font-size: 0.92rem;
    scroll-snap-align: start;
    transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
}

.brand-chip:hover {
    transform: translateY(-1px);
    border-color: rgba(227,0,25,0.35);
    box-shadow: 0 12px 28px rgba(31,45,61,0.10);
}

.brand-chip.is-active {
    border-color: rgba(227,0,25,0.55);
    box-shadow: 0 12px 28px rgba(227,0,25,0.10);
}

.brand-chip-avatar {
    width: 30px;
    height: 30px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 900;
    font-size: 0.78rem;
    letter-spacing: 0.2px;
    box-shadow: 0 6px 16px rgba(31,45,61,0.14);
    flex: 0 0 auto;
}

.brand-chip.has-logo {
    gap: 0;
    padding: 6px 0px;
    background: #fff;
    border: none;
    box-shadow: none;
}

.brand-chip.has-logo .brand-chip-avatar {
    width: 130px;
    height: 45px;
    border-radius: 999px;
    background: #fff;
    border: 1px solid rgba(31,45,61,0.12);
    box-shadow: none;
    padding: 2px 7px;
}

.brand-chip.has-logo .brand-chip-avatar img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
}

.brand-chip.brand-chip-all {
    padding: 6px 0;
    border: none;
}

.brand-chip.brand-chip-all .brand-chip-avatar {
    width: 130px;
    height: 45px;
    border-radius: 999px;
    background: #fff;
    border: 1px solid rgba(31,45,61,0.12);
    box-shadow: none;
    padding: 2px 7px;
    color: #1f2d3d;
}

.brand-chip.has-logo .brand-chip-count {
    display: none;
}

.brand-chip.has-logo:hover {
    box-shadow: 0 10px 22px rgba(31,45,61,0.08);
}

.brand-chip-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 22px;
    min-width: 22px;
    padding: 0 8px;
    border-radius: 999px;
    background: rgba(31,45,61,0.06);
    color: #1f2d3d;
    font-weight: 800;
    font-size: 0.82rem;
}

.category-breadcrumb {
    font-size: 0.78rem;
    color: #6c757d;
}

.category-breadcrumb a {
    text-decoration: none;
    color: inherit;
}

.category-breadcrumb a:hover {
    color: var(--brand-primary);
}

.category-sort-select {
    border-radius: 12px;
    border-color: rgba(31,45,61,0.14);
    font-size: 0.76rem;
    padding: 4px 8px;
}

.product-card {
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    border-radius: 18px;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
    min-height: 380px;
    border: 1px solid rgba(31,45,61,0.10);
    box-shadow: 0 10px 30px rgba(31,45,61,0.08);
    background: #ffffff;
}

.product-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 18px 48px rgba(31,45,61,0.14);
    border-color: rgba(0,123,255,0.22);
}

.product-media {
    position: relative;
    background: linear-gradient(180deg, #ffffff 0%, #f6f8fb 100%);
}

.product-media::before {
    content: "";
    display: block;
    aspect-ratio: 1 / 1;
}

.product-media-inner {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 14px;
}

.product-card .card-img-top {
    transition: transform 0.18s ease;
    max-height: 100%;
    max-width: 100%;
    height: auto;
    width: auto;
    object-fit: contain;
}

.product-card:hover .card-img-top {
    transform: scale(1.06);
}

.product-quick-actions {
    position: absolute;
    left: 10px;
    right: 10px;
    bottom: 10px;
    display: flex;
    justify-content: center;
    opacity: 0;
    transform: translateY(6px);
    transition: opacity 0.15s ease, transform 0.15s ease;
    pointer-events: none;
}

.product-card:hover .product-quick-actions {
    opacity: 1;
    transform: translateY(0);
    pointer-events: auto;
}

.product-quick-actions .btn {
    border-radius: 999px !important;
    padding: 6px 12px;
    font-weight: 800;
    box-shadow: 0 12px 28px rgba(31,45,61,0.12);
}

.product-desc {
    font-size: 0.92rem;
    color: #5a6570;
    line-height: 1.35;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.product-card .card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
    padding: 16px;
    justify-content: space-between;
    min-width: 0;
}

.product-price {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 900;
    color: #d32f2f;
    font-size: 1.02em;
}

.product-price-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 10px;
    border-radius: 999px;
    background: rgba(227,0,25,0.08);
    color: #d32f2f;
    font-weight: 900;
    font-size: 0.92em;
}

.product-card .card-title {
    font-size: 1.05em !important;
    line-height: 1.25;
}

.product-title-link {
    text-decoration: none;
    color: inherit;
    cursor: pointer;
    transition: color 0.3s;
    overflow: visible;
    text-overflow: unset;
    display: block;
    white-space: normal;
    word-break: break-word;
    width: 100%;
    line-height: 1.3;
}

.product-title-link:hover {
    color: var(--brand-primary) !important;
    text-decoration: underline;
}

.product-card {
    cursor: pointer;
}

.product-contact-text {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.3;
}

.product-card .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,184,148,0.3);
}

/* Button styling only for product cards */
.product-card .btn {
    border-radius: 12px !important;
}

.product-card form button.btn {
    height: 44px !important;
    box-shadow: 0 10px 20px rgba(227,0,25,0.18);
}

.product-card form button.btn:hover {
    box-shadow: 0 14px 28px rgba(227,0,25,0.24);
}

/* Đảm bảo tất cả card có chiều cao đều nhau */
.col-6.col-md-4.col-lg-3 {
    display: flex;
    margin-bottom: 20px;
}

.col-6.col-md-4.col-lg-3 .card {
    width: 100%;
}

/* Đảm bảo ảnh placeholder khi không có ảnh */
.card-img-top:not([src]), .card-img-top[src=""] {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
    font-size: 0.9em;
    text-align: center;
}

.card-img-top:not([src])::after, .card-img-top[src=""]::after {
    content: "Không có ảnh";
}

/* Mobile responsive */
@media (max-width: 991.98px) {
    .category-toolbar {
        position: static;
        top: auto;
    }

    .category-header-card {
        position: static;
        top: auto;
    }

    .cat-mobile-header + .row .category-header-card {
        top: 64px;
    }

    /* Ẩn sidebar trên mobile */
    .col-md-3 {
        display: none;
    }
    
    /* Content chiếm toàn bộ width trên mobile */
    .col-md-9 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    /* Tối ưu card cho mobile */
    .product-card {
        min-height: 320px;
        border-radius: 16px;
    }
    
    .product-card .card-img-top {
        height: 170px;
    }
    
    .product-card .card-body {
        padding: 12px;
    }
    
    .product-card .card-title {
        font-size: 1.15em !important;
        min-height: 40px !important;
        margin-bottom: 8px !important;
    }
    
    /* Tối ưu grid cho mobile */
    .col-6.col-md-4.col-lg-3 {
        margin-bottom: 16px;
    }
    
    /* Tối ưu header */
    .d-flex.justify-content-between.align-items-center {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 8px;
    }
    
    .d-flex.justify-content-between.align-items-center h3 {
        font-size: 1.5em !important;
        margin-bottom: 0 !important;
    }
    
    .d-flex.justify-content-between.align-items-center .badge {
        font-size: 0.9em !important;
    }
    
    /* Tối ưu pagination */
    .pagination {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .pagination .page-link {
        padding: 8px 12px;
        font-size: 0.9em;
    }

    @media (max-width: 576px) {
        .category-pagination-wrap nav {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            padding: 6px 10px;
        }

        .category-pagination-wrap nav::-webkit-scrollbar {
            display: none;
        }

        .category-pagination-wrap nav {
            scrollbar-width: none;
        }

        .pagination {
            justify-content: flex-start;
            flex-wrap: nowrap;
            width: max-content;
            min-width: max-content;
            margin: 0;
        }

        .pagination .page-item {
            flex: 0 0 auto;
            margin: 0 3px;
        }

        .pagination .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 34px;
            min-width: 34px;
            padding: 0 10px;
            font-size: 0.88em;
            border-radius: 12px;
        }
    }
    
    /* Tối ưu mobile dropdown */
    .dropdown-menu {
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        border: 1px solid #e3e8f0;
    }
    
    .dropdown-item.active {
        background-color: #007BFF;
        color: white;
    }
    
    .dropdown-item:hover {
        background-color: #f8f9fa;
    }
    
    .dropdown-item.active:hover {
        background-color: #0056b3;
    }

    #mobileCategoryDropdown {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    #mobileCategoryDropdown .mobile-category-label {
        min-width: 0;
        flex: 1 1 auto;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        text-align: left;
    }

    #mobileCategoryDropdown.dropdown-toggle::after {
        margin-left: auto;
        flex: 0 0 auto;
    }

    .category-toolbar .category-title-wrap {
        min-width: 0 !important;
        width: 100%;
    }

    .category-toolbar .category-title-row {
        flex-wrap: wrap;
        row-gap: 6px;
    }

    .category-toolbar .category-title-text {
        min-width: 0;
        max-width: 100%;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .category-toolbar .category-count-badge {
        flex: 0 0 auto;
        max-width: 100%;
        white-space: nowrap;
    }
}
</style>
<div class="d-md-none cat-mobile-header">
    <div class="container py-2">
        <div class="d-flex align-items-center">
            <button type="button" class="btn btn-outline-secondary cat-mh-btn" aria-label="Quay lại" onclick="if (window.history.length > 1) { window.history.back(); } else { window.location.href = '{{ route('home') }}'; }">
                <i class="bi bi-arrow-left"></i>
            </button>
        </div>
    </div>
</div>
<div class="row gx-3">
    <div class="col-auto d-none d-md-block" style="width:240px;">
        @include('components.sidebar', ['categories' => $categories, 'currentCategory' => $category])
    </div>
    <div class="col">
        <!-- Mobile Filters Offcanvas -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="categoryFiltersOffcanvas" aria-labelledby="categoryFiltersOffcanvasLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="categoryFiltersOffcanvasLabel" style="color:var(--brand-secondary);">Bộ lọc</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body pt-0">
                @include('components.sidebar', ['categories' => $categories, 'currentCategory' => $category])
            </div>
        </div>

        <div class="category-header-card mb-3">
        <!-- Toolbar (Breadcrumb + Sort + Mobile Filter Button) -->
        <div class="category-toolbar">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex flex-column category-title-wrap" style="min-width: 0;">
                    <nav class="category-breadcrumb" aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1" style="--bs-breadcrumb-divider: '›';">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
                        </ol>
                    </nav>

                    <div class="d-flex align-items-center gap-2 category-title-row">
                        <h3 class="category-title-text" style="color:var(--brand-secondary); margin:0; font-size:0.98rem;">
                            {{ $category->name }}
                        </h3>
                        <span class="badge bg-primary category-count-badge" style="font-size:0.78em;">{{ $products->total() }} sản phẩm</span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary d-md-none" data-bs-toggle="offcanvas" data-bs-target="#categoryFiltersOffcanvas" aria-controls="categoryFiltersOffcanvas" style="border-radius:12px;">
                        <i class="bi bi-funnel me-1"></i>Bộ lọc
                    </button>

                    @php
                        $filterQueryBase = request()->except('page', 'filter');
                        $activeFilter = $selectedFilter ?? request()->query('filter', 'all');
                        $activeFilterLabel = 'Lọc sản phẩm';
                        if ($activeFilter === 'all') {
                            $activeFilterLabel = 'Tất cả sản phẩm';
                        }
                        if ($activeFilter === 'hot') {
                            $activeFilterLabel = 'Sản phẩm hot bán chạy';
                        }
                        if ($activeFilter === 'new') {
                            $activeFilterLabel = 'Sản phẩm mới';
                        }
                    @endphp
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle category-sort-select" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="min-width: 140px;">
                            {{ $activeFilterLabel }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" style="min-width: 175px;">
                            <li>
                                <a class="dropdown-item {{ $activeFilter === 'all' ? 'active' : '' }}" href="{{ route('category.show', $category->slug) . (count($filterQueryBase) ? ('?' . http_build_query($filterQueryBase)) : '') }}">
                                    Tất cả sản phẩm
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ $activeFilter === 'hot' ? 'active' : '' }}" href="{{ route('category.show', $category->slug) . '?' . http_build_query(array_merge($filterQueryBase, ['filter' => 'hot'])) }}">
                                    Sản phẩm hot bán chạy
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item {{ $activeFilter === 'new' ? 'active' : '' }}" href="{{ route('category.show', $category->slug) . '?' . http_build_query(array_merge($filterQueryBase, ['filter' => 'new'])) }}">
                                    Sản phẩm mới
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($availableBrands) && count($availableBrands))
            @php
                $brandQueryBase = request()->except('page', 'brand');

                $brandUi = [
                    'zkteco' => ['label' => 'ZKTeco', 'abbr' => 'ZK', 'bg' => '#2b2f8e', 'logo' => 'images/brands/zkteco.png'],
                    'dahua' => ['label' => 'Dahua', 'abbr' => 'DH', 'bg' => '#e30019', 'logo' => 'images/brands/dahua.png'],
                    'jieshun' => ['label' => 'Jieshun', 'abbr' => 'JI', 'bg' => '#6c757d', 'logo' => 'images/brands/jieshun.png'],
                    'risco' => ['label' => 'RISCO', 'abbr' => 'RI', 'bg' => '#6c757d', 'logo' => 'images/brands/risco.png'],
                    'sengate' => ['label' => 'SENGATE', 'abbr' => 'SE', 'bg' => '#6c757d', 'logo' => 'images/brands/sengate.png'],
                    'tplink' => ['label' => 'TP-Link', 'abbr' => 'TP', 'bg' => '#6c757d', 'logo' => 'images/brands/tplink.png'],
                    'hytera' => ['label' => 'Hytera', 'abbr' => 'HY', 'bg' => '#6c757d', 'logo' => 'images/brands/hytera.png'],
                    'commax' => ['label' => 'Commax', 'abbr' => 'CO', 'bg' => '#6c757d', 'logo' => 'images/brands/commax.png'],
                    'hikvision' => ['label' => 'Hikvision', 'abbr' => 'HK', 'bg' => '#0d6efd', 'logo' => 'images/brands/hikvision.png'],
                    'kbvision' => ['label' => 'KBVision', 'abbr' => 'KB', 'bg' => '#20c997', 'logo' => 'images/brands/kbvision.png'],
                    'imou' => ['label' => 'Imou', 'abbr' => 'IM', 'bg' => '#fd7e14', 'logo' => 'images/brands/imou.png'],
                    'ezviz' => ['label' => 'Ezviz', 'abbr' => 'EZ', 'bg' => '#6f42c1', 'logo' => 'images/brands/ezviz.png'],
                ];

                $normalizeBrandKey = function ($label) {
                    $value = strtolower(trim($label ?? ''));
                    return preg_replace('/[^a-z0-9]/', '', $value);
                };

                $presentBrandLabel = function ($brandKey, $brandLabel) use ($brandUi, $normalizeBrandKey) {
                    $key = $normalizeBrandKey($brandKey);
                    if (isset($brandUi[$key])) return $brandUi[$key]['label'];
                    $label = trim((string) $brandLabel);
                    if ($label === '') return 'Khác';
                    return $label;
                };

                $presentBrandAbbr = function ($brandKey, $brandLabel) use ($brandUi, $normalizeBrandKey) {
                    $key = $normalizeBrandKey($brandKey);
                    if (isset($brandUi[$key])) return $brandUi[$key]['abbr'];
                    $label = trim((string) $brandLabel);
                    $label = $label !== '' ? $label : 'K';
                    return strtoupper(mb_substr($label, 0, 2));
                };

                $presentBrandBg = function ($brandKey) use ($brandUi, $normalizeBrandKey) {
                    $key = $normalizeBrandKey($brandKey);
                    return $brandUi[$key]['bg'] ?? '#6c757d';
                };

                $presentBrandLogo = function ($brandKey, $brandLabel) use ($brandUi, $normalizeBrandKey) {
                    $key = $normalizeBrandKey($brandKey);
                    if (isset($brandUi[$key]['logo'])) {
                        $path = $brandUi[$key]['logo'];
                        if ($path && file_exists(public_path($path))) return asset($path);
                    }
                    $fallbackMap = [
                        'vigilance' => 'logovigilance.jpg',
                    ];
                    $labelKey = $normalizeBrandKey($brandLabel ?? $brandKey);
                    if (isset($fallbackMap[$labelKey])) {
                        $path = $fallbackMap[$labelKey];
                        if ($path && file_exists(public_path($path))) return asset($path);
                    }
                    return null;
                };
            @endphp
            <div class="brand-filter">
                <div class="brand-filter-scroll">
                    <a
                        href="{{ route('category.show', $category->slug) . (count($brandQueryBase) ? ('?' . http_build_query($brandQueryBase)) : '') }}"
                        class="brand-chip brand-chip-all {{ $selectedBrand ? '' : 'is-active' }}"
                        aria-current="{{ $selectedBrand ? 'false' : 'page' }}"
                    >
                        <span class="brand-chip-avatar">ALL</span>
                    </a>

                    @foreach($availableBrands as $brandKey => $brand)
                        @php
                            $brandLogo = $presentBrandLogo($brandKey, $brand['label'] ?? $brandKey);
                        @endphp
                        <a
                            href="{{ route('category.show', $category->slug) . '?' . http_build_query(array_merge($brandQueryBase, ['brand' => $brandKey])) }}"
                            class="brand-chip {{ $selectedBrand === $brandKey ? 'is-active' : '' }} {{ $brandLogo ? 'has-logo' : '' }}"
                            aria-current="{{ $selectedBrand === $brandKey ? 'page' : 'false' }}"
                            title="{{ $presentBrandLabel($brandKey, $brand['label'] ?? $brandKey) }}"
                        >
                            <span class="brand-chip-avatar" style="background: {{ $brandLogo ? 'transparent' : $presentBrandBg($brandKey) }};">
                                @if($brandLogo)
                                    <img src="{{ $brandLogo }}" alt="{{ $presentBrandLabel($brandKey, $brand['label'] ?? $brandKey) }}">
                                @else
                                    {{ $presentBrandAbbr($brandKey, $brand['label'] ?? $brandKey) }}
                                @endif
                            </span>
                            @if(!$brandLogo)
                                <span>{{ $presentBrandLabel($brandKey, $brand['label'] ?? $brandKey) }}</span>
                            @endif
                            <span class="brand-chip-count">{{ $brand['count'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
        </div>

        <!-- Mobile Category Dropdown -->
        <div class="d-md-none mb-3">
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle w-100" type="button" id="mobileCategoryDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="text-align: left; padding: 12px 16px; font-size: 1em;">
                    <i class="bi bi-list"></i>
                    <span class="mobile-category-label">{{ $category->name }}</span>
                </button>
                <ul class="dropdown-menu w-100" aria-labelledby="mobileCategoryDropdown" style="max-height: 300px; overflow-y: auto;">
                    @foreach($categories as $cat)
                        <li>
                            <a class="dropdown-item {{ $cat->id == $category->id ? 'active' : '' }}" href="{{ route('category.show', $cat->slug) }}" style="padding: 10px 16px; font-size: 0.95em;">
                                <i class="bi bi-folder me-2"></i>{{ $cat->name }}
                            </a>
                        </li>
                        @if($cat->children && $cat->children->count() > 0)
                            @foreach($cat->children as $child)
                                <li>
                                    <a class="dropdown-item {{ $child->id == $category->id ? 'active' : '' }}" href="{{ route('category.show', $child->slug) }}" style="padding: 10px 16px 10px 32px; font-size: 0.9em; color: #666;">
                                        <i class="bi bi-folder-fill me-2"></i>{{ $child->name }}
                                    </a>
                                </li>
                            @endforeach
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
        
        <div class="row g-3">
            @forelse($products as $product)
                @php
                    $listedPrice = (float) ($product->price ?? 0);
                    $agentPrice = (float) ($product->agency_price ?? 0);
                    $displayPrice = $isAgentUser && $agentPrice > 0 ? $agentPrice : $listedPrice;
                    $showListedStrike = $isAgentUser && $agentPrice > 0 && $listedPrice > 0;
                @endphp
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100 product-card" onclick="window.location.href='{{ route('product.show', $product->slug) }}'">
                        <div class="product-media">
                            <div class="product-media-inner">
                                <img src="{{ asset('images/products/' . $product->image) }}" class="card-img-top" alt="{{ $product->name }}" loading="lazy" decoding="async">
                            </div>
                            <div class="product-quick-actions">
                                <a href="{{ route('product.show', $product->slug) }}" class="btn btn-light" onclick="event.stopPropagation();">
                                    <i class="bi bi-eye me-1"></i>Xem nhanh
                                </a>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title" style="font-size:1.25em; font-weight:800; min-height:72px; margin-bottom: 12px;">
                                <a href="{{ route('product.show', $product->slug) }}" class="product-title-link" onclick="event.stopPropagation();">{{ $product->name }}</a>
                            </h6>
                            
                            <div class="mb-2 product-contact-text" style="min-height: 24px;">
                                @if($displayPrice == 0)
                                    <a href="https://zalo.me/0982751039" target="_blank" onclick="event.stopPropagation();" class="product-price" style="text-decoration:none;">
                                        <span class="product-price-badge">Liên hệ</span>
                                    </a>
                                @else
                                    <span class="product-price">
                                        <span class="product-price-badge">{{ number_format($displayPrice, 0, ',', '.') }}đ</span>
                                    </span>
                                    @if($showListedStrike)
                                        <span style="font-size:0.85rem; color:#888; text-decoration:line-through; margin-left:6px;">{{ number_format($listedPrice, 0, ',', '.') }}đ</span>
                                    @endif
                                @endif
                            </div>
                            
                            <div class="mb-3 flex-grow-1 product-desc">{{ $product->description }}</div>
                            
                            <form method="POST" action="{{ route('cart.add', $product->id) }}" class="mt-auto" onclick="event.stopPropagation();">
                                @csrf
                                <button type="submit" class="btn w-100 fw-bold" style="background:var(--brand-primary); color:#fff; border:1px solid var(--brand-primary); border-radius:8px; transition:all 0.3s ease; height:45px;">
                                    <i class="bi bi-cart-plus me-2"></i>Mua ngay
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info">Chưa có sản phẩm nào trong danh mục này.</div>
                </div>
            @endforelse
        </div>
        <div class="mt-4 category-pagination-wrap">
            <div class="d-sm-none">
                {{ $products->appends(request()->query())->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
            <div class="d-none d-sm-block">
                {{ $products->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('click', function (e) {
        var toggle = e.target && e.target.closest ? e.target.closest('.category-submenu-toggle') : null;
        if (!toggle) return;

        e.preventDefault();
        e.stopPropagation();

        var submenuId = toggle.getAttribute('data-submenu-id');
        if (!submenuId) return;

        var submenu = document.getElementById(submenuId);
        if (!submenu) return;

        var isOpen = submenu.style.display === 'block';
        submenu.style.display = isOpen ? 'none' : 'block';
        toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');

        var icon = toggle.querySelector('i');
        if (icon) {
            icon.classList.remove('bi-chevron-down', 'bi-chevron-up');
            icon.classList.add(isOpen ? 'bi-chevron-down' : 'bi-chevron-up');
        }
    }, true);
</script>
@endpush
@endsection