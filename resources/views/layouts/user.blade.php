<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'VIGILANCE')</title>
    <meta name="description" content="@yield('meta_description', 'Vigilance Việt Nam JSC - Cung cấp giải pháp công nghệ và thiết bị chuyên nghiệp')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon/favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/vigilance-logo.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/vigilance-logo.png') }}">
    <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}">
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Vigilance Việt Nam JSC')">
    <meta property="og:description" content="@yield('meta_description', 'Vigilance Việt Nam JSC - Cung cấp giải pháp công nghệ và thiết bị chuyên nghiệp')">
    <meta property="og:site_name" content="Vigilance Việt Nam JSC">
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="@yield('title', 'Vigilance Việt Nam JSC')">
    <meta name="twitter:description" content="@yield('meta_description', 'Vigilance Việt Nam JSC - Cung cấp giải pháp công nghệ và thiết bị chuyên nghiệp')">
    @stack('structured_data')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/custom-fonts.css') }}" onerror="console.error('Failed to load custom-fonts.css')">
    <style>
        :root {
            /* Brand palette derived from logo */
            --brand-primary: #e30019; /* Vigilance red */
            --brand-secondary: #2b2f8e; /* Deep navy/purple subtitle */
            --brand-accent: #ff6f61; /* Warm accent for hovers */
            --brand-muted: #fbeaec; /* Soft red-tint background */
        }
        /* Map Bootstrap tokens to brand */
        :root {
            --bs-primary: var(--brand-primary);
            --bs-primary-rgb: 227, 0, 25;
            --bs-link-color: var(--brand-secondary);
            --bs-link-hover-color: var(--brand-primary);
        }
        /* Generic helpers */
        .text-brand { color: var(--brand-primary) !important; }
        .text-brand-secondary { color: var(--brand-secondary) !important; }
        .bg-brand { background-color: var(--brand-primary) !important; }
        .bg-brand-secondary { background-color: var(--brand-secondary) !important; }
        .border-brand { border-color: var(--brand-primary) !important; }
        .btn-brand { background: var(--brand-primary); color: #fff; border: none; }
        .btn-brand:hover { background: var(--brand-accent); color:#fff; }
    </style>
    <style>
        body { background: #FFFFFF; font-size: 0.97em; }
    </style>
    <style>
        /* Floating User Button & Menu */
        #floating-user-menu-root {
            position: fixed;
            left: 32px;
            bottom: 32px;
            z-index: 9999;
            display: flex;
            flex-direction: column-reverse;
            align-items: flex-start;
        }
        
        /* Mobile optimization for floating menu */
        @media (max-width: 768px) {
            #floating-user-menu-root {
                display: none;
                left: 16px;
                bottom: calc(16px + var(--app-bottom-nav-height, 72px));
            }
            #floating-user-btn {
                width: 48px;
                height: 48px;
                font-size: 24px;
            }
        }
        #floating-user-btn {
            width: 56px;
            height: 56px;
            background: #00B894;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            cursor: pointer;
            color: #fff;
            font-size: 28px;
            border: 4px solid #b2ebf2;
            transition: box-shadow 0.2s;
        }
        #floating-user-btn:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.25);
        }
        #floating-user-menu {
            display: none;
            flex-direction: column;
            margin-bottom: 16px;
            animation: slideUp 0.3s;
        }
        #floating-user-menu.show {
            display: flex;
            animation: slideUp 0.3s;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px);}
            to { opacity: 1; transform: translateY(0);}
        }
        #floating-user-menu button {
            background: #7ac943;
            color: #fff;
            border: none;
            border-radius: 20px;
            margin-bottom: 10px;
            padding: 10px 24px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        #floating-user-menu button:hover {
            background: #5cb85c;
        }
    </style>
    <style>
  body {
    background: #F4F6FA;
  }
  *,
  *::before,
  *::after {
    box-shadow: none !important;
  }
  .shadow,
  .shadow-sm,
  .shadow-lg {
    box-shadow: none !important;
  }
  
  .product-card { min-height: 370px; display: flex; flex-direction: column; }
  .product-card .card-body { flex: 1 1 auto; display: flex; flex-direction: column; }
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
  
  /* Mobile optimization for product cards */
  @media (max-width: 768px) {
    .product-card-modern {
      min-height: 380px;
    }
    .product-img-wrap {
      height: 160px;
    }
    .product-img-modern {
      max-height: 140px;
    }
  }
  .product-img-wrap {
    background: #fffbe9;
    border-radius: 1.5rem 1.5rem 0 0;
    overflow: hidden;
    height: 210px;
  }
  .product-img-modern {
    max-height: 180px;
    max-width: 100%;
    width: auto;
    margin: 0 auto;
    transition: transform 0.25s;
    display: block;
  }
  .btn-modern-main {
    background: var(--brand-secondary);
    color: #fff;
    border-radius: 1.2rem;
    font-size: 1.04em;
    box-shadow: 0 2px 8px 0 rgba(43,47,142,0.18);
    border: none;
    padding: 0.65em 0;
    transition: background 0.18s, color 0.18s;
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
</style>
    <style>
        @media (max-width: 768px) {
            body {
                --app-bottom-nav-height: 74px;
            }
            main.container {
                padding-bottom: calc(1.5rem + var(--app-bottom-nav-height, 74px));
            }
        }
        .app-bottom-nav {
            height: var(--app-bottom-nav-height, 74px);
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 9998;
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-top: 1px solid rgba(15, 23, 42, 0.10);
            padding: 8px 10px 10px;
        }
        .app-bottom-nav .nav-inner {
            max-width: 520px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 6px;
        }
        .app-bottom-nav .nav-item-btn {
            border: 0;
            background: transparent;
            padding: 6px 4px;
            border-radius: 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            text-decoration: none;
            color: rgba(15, 23, 42, 0.70);
            font-weight: 700;
            font-size: 0.68rem;
            line-height: 1;
            min-height: 56px;
        }
        .app-bottom-nav .nav-item-btn i {
            font-size: 1.3rem;
            line-height: 1;
        }
        .app-bottom-nav .nav-item-btn.active {
            color: var(--brand-secondary);
            background: rgba(43, 47, 142, 0.08);
        }
        .app-bottom-nav .nav-item-btn.active i {
            color: var(--brand-secondary);
        }
        .app-bottom-nav .nav-item-btn .badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: var(--brand-primary);
            display: inline-block;
        }
    </style>
</head>
@php
    $isEmbed = (string) request()->query('embed') === '1';
@endphp
<body style="min-height: 100vh; display: flex; flex-direction: column; background: #fff;">
    @php
        if (!isset($categories)) {
            $categories = \App\Models\Category::with(['children' => function ($q) {
                $q->with('children');
            }])->whereNull('parent_id')->ordered()->get();
        }
    @endphp
    @if(!$isEmbed)
        <div class="d-none d-md-block">
            @include('components.header', ['featuredProducts' => $featuredProducts ?? null])
        </div>
        <div class="d-md-none">
            <div class="header-topbar py-1 small" style="background: var(--brand-primary); color: #fff; border-bottom: 1px solid #F1F1F1;">
                <div class="container-fluid d-flex align-items-center" style="font-size: 1em;">
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
        </div>
    @endif
     @php
         $sideLeftBanner = \App\Models\Banner::query()->active()->position('side_left')->orderBy('sort_order')->first();
         $sideRightBanner = \App\Models\Banner::query()->active()->position('side_right')->orderBy('sort_order')->first();
     @endphp
     @if(!$isEmbed && ($sideLeftBanner || $sideRightBanner))
     <div id="floating-side-banners" aria-hidden="true">
         @if($sideLeftBanner)
         <a class="side-banner side-banner-left" href="{{ $sideLeftBanner->link_url ?: '#' }}" target="_blank" rel="noopener" tabindex="-1">
             <img src="{{ asset($sideLeftBanner->image_path) }}" alt="" loading="lazy" decoding="async">
         </a>
         @endif
         @if($sideRightBanner)
         <a class="side-banner side-banner-right" href="{{ $sideRightBanner->link_url ?: '#' }}" target="_blank" rel="noopener" tabindex="-1">
             <img src="{{ asset($sideRightBanner->image_path) }}" alt="" loading="lazy" decoding="async">
         </a>
         @endif
     </div>
     @endif
      @if($isEmbed)
        <main style="flex: 1 0 auto; background: #fff;">
            @yield('content')
        </main>
    @else
        <main class="container py-4" style="flex: 1 0 auto; background: #fff; border-radius: 1.5rem; box-shadow: 0 2px 24px rgba(227,0,25,0.08);">
            @yield('content')
        </main>
    @endif
    @php
        $bottomNavActive = function (array $patterns) {
            foreach ($patterns as $p) {
                if (request()->routeIs($p)) return true;
            }
            return false;
        };
        $isHome = $bottomNavActive(['home']);
        $isCart = $bottomNavActive(['cart.*']);
        $isOrders = $bottomNavActive(['orders.*']);
        $isWishlist = $bottomNavActive(['wishlist.*']);
        $isAccount = $isOrders || $isWishlist || $bottomNavActive(['profile.*', 'login', 'register']);
        $isNotifications = $bottomNavActive(['notifications.*']);
        $unreadNotificationsCount = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;
    @endphp
    @if(!$isEmbed)
        <nav class="app-bottom-nav d-md-none" aria-label="App navigation">
            <div class="nav-inner">
                <a class="nav-item-btn {{ $isHome ? 'active' : '' }}" href="{{ route('home') }}">
                    <i class="bi bi-house"></i>
                    <span>Trang chủ</span>
                </a>
                <button type="button" class="nav-item-btn" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
                    <i class="bi bi-grid-3x3-gap"></i>
                    <span>Danh mục</span>
                </button>
                <a class="nav-item-btn {{ $isCart ? 'active' : '' }}" href="{{ route('cart.view') }}">
                    <i class="bi bi-cart3"></i>
                    <span>Giỏ hàng</span>
                </a>
                <a class="nav-item-btn {{ $isNotifications ? 'active' : '' }}" href="{{ route('notifications.index') }}">
                    <i class="bi bi-bell"></i>
                    @if($unreadNotificationsCount > 0)
                        <span class="badge-dot" aria-hidden="true"></span>
                    @endif
                    <span>Thông báo</span>
                </a>
                <button type="button" class="nav-item-btn {{ $isAccount ? 'active' : '' }}" data-bs-toggle="offcanvas" data-bs-target="#mobileAccount" aria-controls="mobileAccount">
                    <i class="bi bi-person"></i>
                    <span>Tài khoản</span>
                </button>
            </div>
        </nav>
    @endif

    <div class="offcanvas offcanvas-bottom d-md-none" tabindex="-1" id="mobileAccount" aria-labelledby="mobileAccountLabel" style="height: 100vh; max-height: 100vh; border-top-left-radius: 0; border-top-right-radius: 0;">
        <div class="offcanvas-header" style="border-bottom: 1px solid rgba(15, 23, 42, 0.08);">
            <h5 class="offcanvas-title fw-bold" id="mobileAccountLabel" style="color: var(--brand-secondary);">Tài khoản</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body pt-3" style="overflow-y: auto;">
            @auth
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(43,47,142,0.10); color: var(--brand-secondary); font-size: 1.25rem;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold" style="font-size: 1rem;">{{ auth()->user()->name }}</div>
                        <div class="text-muted" style="font-size: 0.85rem;">{{ auth()->user()->email }}</div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm" style="border-radius: 999px;">Đăng xuất</button>
                    </form>
                </div>

                <div class="list-group list-group-flush mb-3">
                    <a href="{{ route('orders.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-bag" style="font-size: 1.2rem; color: var(--brand-secondary);"></i>
                        <span class="fw-semibold">Đơn hàng của bạn</span>
                        <i class="bi bi-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a href="{{ route('wishlist.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-heart" style="font-size: 1.2rem; color: var(--brand-secondary);"></i>
                        <span class="fw-semibold">Yêu thích</span>
                        <i class="bi bi-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a href="{{ route('warranty.check') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-shield-check" style="font-size: 1.2rem; color: var(--brand-secondary);"></i>
                        <span class="fw-semibold">Tra cứu bảo hành</span>
                        <i class="bi bi-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a href="{{ route('orders.lookup') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-receipt" style="font-size: 1.2rem; color: var(--brand-secondary);"></i>
                        <span class="fw-semibold">Tra cứu đơn hàng</span>
                        <i class="bi bi-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a href="{{ route('orders.history') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-clock-history" style="font-size: 1.2rem; color: var(--brand-secondary);"></i>
                        <span class="fw-semibold">Lịch sử đơn hàng</span>
                        <i class="bi bi-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a href="{{ route('policies.terms') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-file-text" style="font-size: 1.2rem; color: var(--brand-secondary);"></i>
                        <span class="fw-semibold">Chính sách & điều khoản</span>
                        <i class="bi bi-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a href="{{ route('policies.shipping') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-truck" style="font-size: 1.2rem; color: var(--brand-secondary);"></i>
                        <span class="fw-semibold">Chính sách giao hàng</span>
                        <i class="bi bi-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a href="{{ route('policies.payment') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-credit-card" style="font-size: 1.2rem; color: var(--brand-secondary);"></i>
                        <span class="fw-semibold">Phương thức thanh toán</span>
                        <i class="bi bi-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a href="{{ route('policies.returns') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-arrow-repeat" style="font-size: 1.2rem; color: var(--brand-secondary);"></i>
                        <span class="fw-semibold">Chính sách đổi trả</span>
                        <i class="bi bi-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a href="tel:0982751075" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-headset" style="font-size: 1.2rem; color: var(--brand-secondary);"></i>
                        <span class="fw-semibold">Chăm sóc khách hàng</span>
                        <span class="ms-auto fw-bold" style="color: var(--brand-secondary);">0982 751 075</span>
                    </a>
                    <a href="tel:0982751039" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-telephone" style="font-size: 1.2rem; color: var(--brand-secondary);"></i>
                        <span class="fw-semibold">Gọi mua hàng</span>
                        <span class="ms-auto fw-bold" style="color: var(--brand-secondary);">0982 751 039</span>
                    </a>
                </div>
            @else
                <div class="p-3 rounded-4 mb-3" style="background: rgba(43,47,142,0.06); border: 1px solid rgba(43,47,142,0.10);">
                    <div class="fw-bold mb-2" style="color: var(--brand-secondary);">Đăng nhập để trải nghiệm đầy đủ</div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('login') }}" class="btn btn-primary flex-fill" style="border-radius: 999px;">Đăng nhập</a>
                        <a href="{{ route('register') }}" class="btn btn-outline-primary flex-fill" style="border-radius: 999px;">Đăng ký</a>
                    </div>
                </div>

                <div class="list-group list-group-flush mb-3">
                    <a href="{{ route('orders.lookup') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-receipt" style="font-size: 1.2rem; color: var(--brand-secondary);"></i>
                        <span class="fw-semibold">Tra cứu đơn hàng</span>
                        <i class="bi bi-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a href="{{ route('orders.history') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-clock-history" style="font-size: 1.2rem; color: var(--brand-secondary);"></i>
                        <span class="fw-semibold">Lịch sử đơn hàng</span>
                        <i class="bi bi-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a href="{{ route('warranty.check') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-shield-check" style="font-size: 1.2rem; color: var(--brand-secondary);"></i>
                        <span class="fw-semibold">Tra cứu bảo hành</span>
                        <i class="bi bi-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a href="{{ route('policies.terms') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-file-text" style="font-size: 1.2rem; color: var(--brand-secondary);"></i>
                        <span class="fw-semibold">Chính sách & điều khoản</span>
                        <i class="bi bi-chevron-right ms-auto text-muted"></i>
                    </a>
                    <a href="tel:0982751075" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-headset" style="font-size: 1.2rem; color: var(--brand-secondary);"></i>
                        <span class="fw-semibold">Chăm sóc khách hàng</span>
                        <span class="ms-auto fw-bold" style="color: var(--brand-secondary);">0982 751 075</span>
                    </a>
                    <a href="tel:0982751039" class="list-group-item list-group-item-action d-flex align-items-center gap-3 py-3">
                        <i class="bi bi-telephone" style="font-size: 1.2rem; color: var(--brand-secondary);"></i>
                        <span class="fw-semibold">Gọi mua hàng</span>
                        <span class="ms-auto fw-bold" style="color: var(--brand-secondary);">0982 751 039</span>
                    </a>
                </div>
            @endauth
        </div>
    </div>

    @if(!$isEmbed)
        <footer class="footer mt-5" style="flex-shrink: 0;">
            @include('components.footer', ['variant' => $footerVariant ?? 'compact'])
        </footer>
    @endif
    @if(!$isEmbed)
        @include('components.mobile-sidebar', ['categories' => $categories])
    @endif
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('click', function (e) {
            var link = e.target && e.target.closest ? e.target.closest('.pagination a') : null;
            if (!link) return;
            if (e.defaultPrevented) return;
            if (e.button !== 0) return;
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
            var href = link.getAttribute('href');
            if (!href || href === '#') return;
            e.preventDefault();
            window.location.href = href;
        }, true);
    </script>
    @stack('scripts')
    <script>
        // Mở Zalo app trực tiếp khi bấm vào các link zalo.me/xxxx (fallback về web nếu không có app)
        document.addEventListener('click', function (e) {
            var anchor = e.target && e.target.closest('a');
            if (!anchor) return;
            var href = anchor.getAttribute('href') || '';
            if (!/zalo\.me\//i.test(href)) return;

            var match = href.match(/zalo\.me\/(\d{8,15})/i);
            if (!match) return;
            e.preventDefault();

            var phone = match[1];
            var webUrl = 'https://zalo.me/' + phone;
            var isAndroid = /Android/i.test(navigator.userAgent);
            var isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent);
            var isDesktop = !isAndroid && !isIOS;

            // Deep links
            var deepLinkCandidates = [
                'zalo://chat?phone=' + phone,
                'zalo://conversation?phone=' + phone,
                'zalo://msg?phone=' + phone
            ];
            var intentLink = 'intent://chat?phone=' + phone + '#Intent;scheme=zalo;package=com.zing.zalo;end';

            // Fallback sang web nếu app không bắt được
            var start = Date.now();
            var fallbackTimer = setTimeout(function () {
                // Nếu sau ~800ms vẫn ở lại trang (chưa chuyển đi), mở web (cùng tab để tránh bị chặn popup)
                if (Date.now() - start < 1500) {
                    window.location.href = webUrl;
                }
            }, 900);

            try {
                if (isAndroid) {
                    window.location.href = intentLink;
                } else if (isIOS) {
                    window.location.href = deepLinkCandidates[0];
                } else if (isDesktop) {
                    // Desktop: tăng cường mở app Zalo bằng nhiều cách (Chrome/Edge/Firefox)
                    // 1) Cố gắng mở trong cùng tab
                    try { window.open(deepLinkCandidates[0], '_self'); } catch (e) {}
                    // 2) Ẩn iframe để kích hoạt custom protocol (một số trình duyệt cần cách này)
                    try {
                        var ifr = document.createElement('iframe');
                        ifr.style.display = 'none';
                        ifr.src = deepLinkCandidates[0];
                        document.body.appendChild(ifr);
                        setTimeout(function(){ if (ifr && ifr.parentNode) ifr.parentNode.removeChild(ifr); }, 1200);
                    } catch (e) {}
                    // 3) Thử thêm method/endpoint khác sau một nhịp ngắn
                    setTimeout(function(){
                        try { window.location.href = deepLinkCandidates[1]; } catch (e) {}
                    }, 250);
                }
            } catch (err) {
                clearTimeout(fallbackTimer);
                window.location.href = webUrl;
            }
        }, true);
    </script>
    @if(session('showLoginModal') || session('showRegisterModal'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            try {
                var isMobile = window.matchMedia && window.matchMedia('(max-width: 575.98px)').matches;
                var hasMobileLoginModal = !!document.getElementById('mobileLoginModal');

                @if(session('showLoginModal'))
                    if (isMobile && hasMobileLoginModal && typeof window.openMobileLoginModal === 'function') {
                        window.openMobileLoginModal();
                    } else if (document.getElementById('loginModal')) {
                        var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                        loginModal.show();
                    }
                @endif

                @if(session('showRegisterModal'))
                    if (document.getElementById('registerModal')) {
                        var registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
                        registerModal.show();
                    }
                @endif
            } catch (error) {
                console.log('Modal initialization error:', error);
            }
        });
    </script>
    @endif
    <script>
        (function () {
            var root = document.getElementById('floating-side-banners');
            if (!root) return;

            var footer = document.querySelector('footer.footer');
            if (!footer) return;

            var mql = window.matchMedia('(min-width: 992px)');
            var ticking = false;

            function update() {
                ticking = false;
                if (!mql.matches) {
                    root.classList.remove('is-ready');
                    return;
                }

                var footerTop = footer.getBoundingClientRect().top + window.scrollY;
                var vh = window.innerHeight || document.documentElement.clientHeight;
                var centerY = window.scrollY + (vh / 2);
                var margin = 16;

                var banners = root.querySelectorAll('.side-banner');
                var hasMeasuredBanner = false;
                for (var i = 0; i < banners.length; i++) {
                    var el = banners[i];
                    var h = el.offsetHeight || 0;
                    if (!h) continue;
                    hasMeasuredBanner = true;
                    var minCenterY = window.scrollY + margin + (h / 2);
                    var maxCenterY = footerTop - margin - (h / 2);
                    var clampedCenterY = Math.min(Math.max(centerY, minCenterY), maxCenterY);
                    var topInViewport = (clampedCenterY - window.scrollY) - (h / 2);
                    el.style.top = topInViewport + 'px';
                }

                if (hasMeasuredBanner) {
                    root.classList.add('is-ready');
                }
            }

            function requestUpdate() {
                if (ticking) return;
                ticking = true;
                window.requestAnimationFrame(update);
            }

            window.addEventListener('scroll', requestUpdate, { passive: true });
            window.addEventListener('resize', requestUpdate);
            window.addEventListener('load', requestUpdate);
            if (mql.addEventListener) {
                mql.addEventListener('change', requestUpdate);
            } else if (mql.addListener) {
                mql.addListener(requestUpdate);
            }

            var imgs = root.querySelectorAll('img');
            for (var j = 0; j < imgs.length; j++) {
                if (imgs[j] && !imgs[j].complete) {
                    imgs[j].addEventListener('load', requestUpdate, { once: true });
                    imgs[j].addEventListener('error', requestUpdate, { once: true });
                }
            }
            requestUpdate();
        })();
    </script>
    <!-- Floating User Button & Menu -->
    <div id="floating-user-menu-root">
        <div id="floating-user-btn" onclick="toggleUserMenu()">
            <i class="bi bi-person-fill"></i>
        </div>
        <div id="floating-user-menu">
            <button onclick="openZalo('0982751075')">CSKH</button>
            <button onclick="openZalo('0982751039')">Tư vấn báo giá</button>
            <button onclick="openZalo('0968220919')">Bán hàng 2</button>
            <button onclick="openZalo('vigilancevn')">Kỹ thuật vigilancevn</button>
            <button onclick="openZalo('0879774476')">Gặp lỗi Khi đặt hàng</button>
        </div>
    </div>
    <script>
        function toggleUserMenu() {
            const menu = document.getElementById('floating-user-menu');
            menu.classList.toggle('show');
        }
        // Ẩn menu khi click ra ngoài
        document.addEventListener('click', function(event) {
            const btn = document.getElementById('floating-user-btn');
            const menu = document.getElementById('floating-user-menu');
            if (!btn.contains(event.target) && !menu.contains(event.target)) {
                menu.classList.remove('show');
            }
        });
        
        // Hàm mở Zalo với số điện thoại (ưu tiên có mã quốc gia) hoặc username
        function openZalo(phoneOrUsername) {
            // Chuẩn hóa số: bỏ ký tự lạ, thêm +84 nếu bắt đầu 0 và đủ 10-11 số
            function normalizeZaloId(val) {
                var digitsOnly = (val || '').replace(/\D/g, '');
                var isLikelyPhone = /^\d{9,12}$/.test(digitsOnly);
                if (isLikelyPhone) {
                    if (/^0\d{8,10}$/.test(digitsOnly)) {
                        return '84' + digitsOnly.slice(1);
                    }
                    return digitsOnly;
                }
                return val; // username/oa id giữ nguyên
            }

            var target = normalizeZaloId(phoneOrUsername);
            var webUrl = 'https://zalo.me/' + target;
            var isAndroid = /Android/i.test(navigator.userAgent);
            var isIOS = /iPhone|iPad|iPod/i.test(navigator.userAgent);
            var isDesktop = !isAndroid && !isIOS;

            // Deep links
            var deepLinkCandidates = [
                'zalo://chat?phone=' + target,
                'zalo://conversation?phone=' + target,
                'zalo://msg?phone=' + target
            ];
            
            // Cho username (vigilancevn), sử dụng web URL
            if (phoneOrUsername === 'vigilancevn') {
                window.open('https://zalo.me/' + phoneOrUsername, '_blank');
                return;
            }
            
            var intentLink = 'intent://chat?phone=' + target + '#Intent;scheme=zalo;package=com.zing.zalo;end';

            // Fallback sang web nếu app không bắt được
            var start = Date.now();
            var fallbackTimer = setTimeout(function () {
                // Nếu sau ~800ms vẫn ở lại trang (chưa chuyển đi), mở web
                if (Date.now() - start < 1500) {
                    if (window.open) {
                        window.open(webUrl, '_blank');
                    } else {
                        window.location.href = webUrl;
                    }
                }
            }, 900);

            try {
                if (isAndroid) {
                    window.location.href = intentLink;
                } else if (isIOS) {
                    window.location.href = deepLinkCandidates[0];
                } else if (isDesktop) {
                    // Desktop: tăng cường mở app Zalo bằng nhiều cách (Chrome/Edge/Firefox)
                    // 1) Cố gắng mở trong cùng tab
                    try { window.open(deepLinkCandidates[0], '_self'); } catch (e) {}
                    // 2) Ẩn iframe để kích hoạt custom protocol (một số trình duyệt cần cách này)
                    try {
                        var ifr = document.createElement('iframe');
                        ifr.style.display = 'none';
                        ifr.src = deepLinkCandidates[0];
                        document.body.appendChild(ifr);
                        setTimeout(function(){ if (ifr && ifr.parentNode) ifr.parentNode.removeChild(ifr); }, 1200);
                    } catch (e) {}
                    // 3) Thử thêm method/endpoint khác sau một nhịp ngắn
                    setTimeout(function(){
                        try { window.location.href = deepLinkCandidates[1]; } catch (e) {}
                    }, 250);
                }
            } catch (err) {
                clearTimeout(fallbackTimer);
                window.location.href = webUrl;
            }
        }
    </script>
{{-- Widget Chat AI Hỗ trợ tư vấn --}}
<style>
#aiChatWidgetBtn {
    position: fixed;
    bottom: 32px;
    right: 32px;
    z-index: 9999;
    background: #fff;
    border: none;
    box-shadow: 0 2px 12px rgba(0,0,0,0.18);
    border-radius: 50%;
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: box-shadow 0.2s;
}
#aiChatWidgetBtn:hover {
    box-shadow: 0 4px 24px rgba(0,0,0,0.22);
}
#aiChatWidget {
    position: fixed;
    bottom: 110px;
    right: 32px;
    z-index: 10000;
    width: 370px;
    max-width: 98vw;
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    overflow: hidden;
    display: none;
    flex-direction: column;
    border: 2.5px solid #ffb300;
}
#aiChatWidget.open {
    display: flex;
}
#aiChatWidgetHeader {
    background: #ffb300;
    color: #fff;
    padding: 16px 18px 12px 18px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 1.18em;
    font-weight: 700;
    border-bottom: 1.5px solid #ffe082;
}
#aiChatWidgetHeader img {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #fff;
}
#aiChatWidgetClose {
    margin-left: auto;
    background: none;
    border: none;
    color: #fff;
    font-size: 1.5em;
    cursor: pointer;
    opacity: 0.8;
}
#aiChatWidgetBody {
    flex: 1 1 auto;
    padding: 16px 14px 0 14px;
    background: #f8fafc;
    overflow-y: auto;
    max-height: 340px;
}
.ai-chat-msg {
    margin-bottom: 12px;
    display: flex;
    align-items: flex-end;
}
.ai-chat-msg.user { justify-content: flex-end; }
.ai-chat-msg .msg {
    max-width: 75%;
    padding: 10px 16px;
    border-radius: 16px;
    font-size: 1.08em;
    line-height: 1.5;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}
.ai-chat-msg.user .msg {
    background: #e3f7f3;
    color: #007bff;
    border-bottom-right-radius: 4px;
}
.ai-chat-msg.ai .msg {
    background: #fff;
    color: #222;
    border-bottom-left-radius: 4px;
    border: 1.5px solid #ffb300;
}
#aiChatWidgetFooter {
    padding: 10px 14px 14px 14px;
    background: #fff;
    border-top: 1.5px solid #ffe082;
    display: flex;
    gap: 8px;
}
#aiChatInput {
    flex: 1 1 auto;
    border-radius: 12px;
    border: 1.5px solid #e3e8f0;
    padding: 8px 12px;
    font-size: 1.08em;
    outline: none;
}
#aiChatSendBtn {
    background: #ffb300;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 8px 18px;
    font-weight: 600;
    font-size: 1.08em;
    cursor: pointer;
    transition: background 0.2s;
}
#aiChatSendBtn:hover {
    background: #ffa000;
}

/* Mobile Responsive - Chỉ CSS đơn giản */
@media (max-width: 767.98px) {
    .product-card-modern { min-height: 320px; }
    .product-img-wrap { height: 160px !important; }
    .product-img-modern { max-height: 140px !important; object-fit: contain !important; }
    .btn { font-size: 0.9em; padding: 0.5rem 1rem; }
    .card-body { padding: 0.75rem !important; }
}

@media (max-width: 575.98px) {
    .product-card-modern { min-height: 280px; }
    .product-img-wrap { height: 140px !important; }
    .product-img-modern { max-height: 120px !important; object-fit: contain !important; }
    .btn { font-size: 0.85em; padding: 0.4rem 0.8rem; }
    .card-body { padding: 0.5rem !important; }
    .product-price-main { font-size: 0.9em !important; }
    .product-price-old { font-size: 0.8em !important; }
}

/* Mobile Hover Effects */
@media (hover: hover) {
    .list-group-item:hover {
        background-color: #f8f9fa !important;
        transform: translateX(5px);
    }
    
    .hotline-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
    }
}

/* Mobile Touch Feedback */
@media (hover: none) {
    .list-group-item:active {
        background-color: #e9ecef !important;
    }
    
    .hotline-box:active {
        transform: scale(0.98);
    }
}

#floating-side-banners {
  position: fixed;
  left: 0;
  top: 0;
  width: 100%;
  height: 0;
  z-index: 10005;
  pointer-events: none;
  visibility: hidden;
}

#floating-side-banners.is-ready {
  visibility: visible;
}

#floating-side-banners .side-banner {
  position: fixed;
  top: 50%;
  transform: none;
  width: var(--side-banner-width, 140px);
  height: auto;
  max-height: var(--side-banner-max-h, calc(100vh - 200px));
  pointer-events: auto;
  text-decoration: none;
  border-radius: 14px;
  overflow: hidden;
}

#floating-side-banners .side-banner img {
  width: 100%;
  height: auto;
  display: block;
  border-radius: inherit;
  max-height: var(--side-banner-max-h, calc(100vh - 200px));
  object-fit: contain;
}

#floating-side-banners {
  --container-max: 1320px;
  --side-banner-width: 140px;
  --side-banner-max-h: 600px;
  --side-banner-gap: 12px;
  --side-banner-edge-min: 8px;
}

@media (max-width: 1399.98px) {
  #floating-side-banners { --container-max: 1140px; --side-banner-width: 120px; --side-banner-max-h: 540px; }
}

@media (max-width: 1199.98px) {
  #floating-side-banners { --container-max: 960px; --side-banner-width: 110px; --side-banner-max-h: 500px; }
}

@media (max-width: 991.98px) {
  #floating-side-banners { display: none !important; }
}

#floating-side-banners .side-banner-left {
  left: max(
    var(--side-banner-edge-min),
    calc((100vw - var(--container-max)) / 2 - var(--side-banner-width) - var(--side-banner-gap))
  );
}

#floating-side-banners .side-banner-right {
  right: max(
    var(--side-banner-edge-min),
    calc((100vw - var(--container-max)) / 2 - var(--side-banner-width) - var(--side-banner-gap))
  );
}

@media (max-height: 720px) {
  #floating-side-banners { --side-banner-width: 110px; --side-banner-max-h: 460px; }
}

</style>

</body>
</html>