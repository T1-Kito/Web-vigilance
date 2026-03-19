{{-- Category Section: 2 Banner dọc trái + Tabs danh mục con + 8 Sản phẩm (2 hàng x 4 cột) bên phải (CellphoneS style) --}}
@props(['category', 'products' => null, 'wishlistProductIds' => []])

@php
    $products = $products ?? $category->products ?? collect();
    $children = $category->children ?? collect();
    $sectionId = 'cat-section-' . $category->id;
    // Lấy danh sách các hãng (brand) unique từ sản phẩm
    $brands = $products->pluck('brand')->filter()->unique()->values()->take(8);
    
    // Kiểm tra có banner không
    $hasBanner = !empty($category->banner_image_1);
    
    // Tùy chỉnh số hàng theo danh mục (hardcode)
    $categoryName = strtolower($category->name);
    if (str_contains($categoryName, 'chấm công') || str_contains($categoryName, 'cham cong')) {
        $displayRows = 3;
    } else {
        $displayRows = 2;
    }
        // Nếu có banner: 4 SP/hàng, không banner: 5 SP/hàng
    $productsPerRow = 4;
    $maxProducts = $displayRows * $productsPerRow;
@endphp

@if($products->count() > 0)
<div class="category-section mb-5 reveal-on-scroll" id="{{ $sectionId }}">
    {{-- Header: Tên danh mục + Tabs hãng sản phẩm --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
        <h3 class="fw-bold mb-0" style="color:var(--brand-secondary); font-size:1rem; letter-spacing:0.5px;">
            <a href="{{ route('category.show', $category->slug) }}" style="text-decoration:none; color:inherit;">
                {{ strtoupper($category->name) }}
            </a>
        </h3>
        
        {{-- Tabs các hãng (brand) --}}
        @if($brands->count() > 0)
        @php
            $brandUi = [
                'zkteco' => ['label' => 'ZKTeco', 'logo' => 'images/brands/zkteco.png'],
                'dahua' => ['label' => 'Dahua', 'logo' => 'images/brands/dahua.png'],
                'jieshun' => ['label' => 'Jieshun', 'logo' => 'images/brands/jieshun.png'],
                'hikvision' => ['label' => 'Hikvision', 'logo' => 'images/brands/hikvision.png'],
                'kbvision' => ['label' => 'KBVision', 'logo' => 'images/brands/kbvision.png'],
                'imou' => ['label' => 'Imou', 'logo' => 'images/brands/imou.png'],
                'ezviz' => ['label' => 'Ezviz', 'logo' => 'images/brands/ezviz.png'],
                'tplink' => ['label' => 'TP-Link', 'logo' => 'images/brands/tplink.png'],
                'risco' => ['label' => 'RISCO', 'logo' => 'images/brands/risco.png'],
                'sengate' => ['label' => 'SENGATE', 'logo' => 'images/brands/sengate.png'],
                'hytera' => ['label' => 'Hytera', 'logo' => 'images/brands/hytera.png'],
                'commax' => ['label' => 'Commax', 'logo' => 'images/brands/commax.png'],
            ];

            $normalizeBrandKey = function ($label) {
                $value = strtolower(trim($label ?? ''));
                return preg_replace('/[^a-z0-9]/', '', $value);
            };

            $presentBrandLogo = function ($brandLabel) use ($brandUi, $normalizeBrandKey) {
                $key = $normalizeBrandKey($brandLabel);
                if (isset($brandUi[$key]['logo'])) {
                    $path = $brandUi[$key]['logo'];
                    if ($path && file_exists(public_path($path))) return asset($path);
                }
                $fallbackMap = [
                    'vigilance' => 'logovigilance.jpg',
                ];
                if (isset($fallbackMap[$key])) {
                    $path = $fallbackMap[$key];
                    if ($path && file_exists(public_path($path))) return asset($path);
                }
                return null;
            };
        @endphp
        <div class="brand-tabs-modern">
            @foreach($brands as $brand)
                @php
                    $brandLogo = $presentBrandLogo($brand);
                @endphp
                <a href="{{ route('category.show', $category->slug) }}?brand={{ urlencode($brand) }}" 
                   class="brand-pill {{ request('brand') == $brand ? 'active' : '' }}">
                    @if($brandLogo)
                        <img src="{{ $brandLogo }}" alt="{{ $brand }}">
                    @else
                        <span>{{ $brand }}</span>
                    @endif
                </a>
            @endforeach
            <a href="{{ route('category.show', $category->slug) }}" class="brand-view-all">
                Xem tất cả <i class="bi bi-chevron-right"></i>
            </a>
        </div>
        @endif
    </div>

    {{-- Layout: Banner (nếu có) + Sản phẩm --}}
    <div class="row g-3">
        {{-- Banner bên trái (chỉ hiện nếu có ảnh upload) --}}
        @if($hasBanner)
        <div class="col-12 col-lg-2 d-none d-lg-block">
            <a href="{{ route('category.show', $category->slug) }}" class="d-block">
                <div class="category-banner-card rounded-3 overflow-hidden position-relative" style="height:700px;">
                    <img src="{{ asset('images/categories/' . $category->banner_image_1) }}" alt="{{ $category->name }}" 
                         style="width:100%; height:100%; object-fit:contain;">
                </div>
            </a>
        </div>
        @else
        <div class="col-12 col-lg-2 d-none d-lg-block">
            <div class="category-banner-card rounded-3 overflow-hidden position-relative" style="height:700px;"></div>
        </div>
        @endif

        {{-- Sản phẩm (chiếm hết nếu không có banner) --}}
        <div class="col-12 col-lg-10">
            <div class="row g-3">
                @foreach($products->take($maxProducts) as $product)
                    @php
                        $star = number_format(mt_rand(48, 50) / 10, 1);
                        $discount = $product->discount_percent ?? 0;
                        $oldPrice = $product->price;
                        $finalPrice = $discount ? round($oldPrice * (100 - $discount) / 100, -3) : $oldPrice;
                        $wishlistArray = is_array($wishlistProductIds) ? $wishlistProductIds : ($wishlistProductIds ? $wishlistProductIds->toArray() : []);
                        $isFavorited = in_array($product->id, $wishlistArray);
                    @endphp
                    <div class="col-6 col-md-3">
                        <div class="card h-100 border-0 shadow-sm product-card-modern w-100 position-relative" 
                             style="cursor:pointer; min-height:340px;" 
                             onclick="window.location.href='{{ route('product.show', $product->slug) }}'">
                            
                            {{-- Nút yêu thích --}}
                            <div class="position-absolute top-0 end-0 m-2" style="z-index:3;">
                                @auth
                                    <button type="button" class="btn btn-light p-1 wishlist-btn" data-product-id="{{ $product->id }}" style="border-radius:50%; box-shadow:0 2px 8px rgba(43,47,142,0.13);" onclick="event.stopPropagation();">
                                        <i class="bi bi-heart{{ $isFavorited ? '-fill text-danger' : '' }}" style="font-size:1.1em;"></i>
                                    </button>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-light p-1" style="border-radius:50%; box-shadow:0 2px 8px rgba(43,47,142,0.13);" onclick="event.stopPropagation();">
                                        <i class="bi bi-heart" style="font-size:1.1em;"></i>
                                    </a>
                                @endauth
                            </div>

                            {{-- Badge giảm giá --}}
                            @if($discount)
                                <span class="badge bg-danger position-absolute top-0 start-0 m-2" style="font-size:0.7rem; z-index:2;">-{{ $discount }}%</span>
                            @endif

                            {{-- Ảnh sản phẩm --}}
                            <div class="product-img-wrap d-flex align-items-center justify-content-center" style="height:140px; background:#fff; border-radius:1rem 1rem 0 0; overflow:hidden; padding:8px;">
                                <img src="{{ asset('images/products/' . $product->image) }}" class="product-img-modern" alt="{{ $product->name }}" style="max-height:120px; object-fit:contain;">
                            </div>

                            
                            {{-- Thông tin sản phẩm --}}
                            <div class="card-body d-flex flex-column p-2" style="flex:1 1 auto;">
                                {{-- Rating --}}
                                <div class="mb-1 d-flex align-items-center gap-1" style="font-size:0.75rem; color:#FFC107;">
                                    <i class="bi bi-star-fill"></i>
                                    <span class="fw-bold" style="color:#222;">{{ $star }}</span>
                                </div>

                                {{-- Tên sản phẩm --}}
                                <h6 class="card-title fw-bold mb-2" style="font-size:0.85rem; font-weight:600; min-height:36px; color:#222; line-height:1.3; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;" title="{{ $product->name }}">
                                    {{ Str::limit($product->name, 45) }}
                                </h6>

                                {{-- Giá --}}
                                <div class="mb-1">
                                    @if($product->price == 0)
                                        <span style="font-size:0.9rem; color:#d32f2f; font-weight:700;">Liên hệ</span>
                                    @else
                                        <span style="font-size:0.9rem; color:#d32f2f; font-weight:700;">{{ number_format($finalPrice, 0, ',', '.') }}đ</span>
                                        @if($discount)
                                            <span style="font-size:0.75rem; color:#888; text-decoration:line-through; margin-left:4px;">{{ number_format($oldPrice, 0, ',', '.') }}đ</span>
                                        @endif
                                    @endif
                                </div>

                                {{-- Thông tin bổ sung (trả góp, ưu đãi...) --}}
                                <div class="product-extra-info mb-2" style="font-size:0.7rem; line-height:1.4;">
                                    @if($product->price > 0 && $product->price >= 3000000)
                                        <div style="color:#288ad6; background:#f2f8ff; padding:2px 0; border-radius:4px; line-height:1.4; display:flex; align-items:center; gap:4px;" class="mb-1">
                                            <i class="bi bi-patch-check-fill"></i> Bảo hành chính hãng 12 tháng
                                        </div>
                                    @endif
                                    <div style="color:#E30019; background:#fff5f5; padding:2px 0; border-radius:4px; line-height:1.4; display:flex; align-items:center; gap:4px;" class="mb-1">
                                        <i class="bi bi-bag-plus-fill"></i> Giảm thêm 5% khi mua kèm Phụ kiện
                                    </div>
                                    <div style="color:#28a745; background:#e8f5e9; padding:2px 0; border-radius:4px; line-height:1.4; display:flex; align-items:center; gap:4px;" class="mb-1">
                                        <i class="bi bi-cart-plus-fill"></i> Giảm thêm 10% khi mua 3 sản phẩm
                                    </div>
                                </div>

                                {{-- Nút mua --}}
                                <div class="mt-auto">
                                    <form action="{{ route('cart.add', $product->id) }}" method="POST" class="add-to-cart-form" onclick="event.stopPropagation();">
                                        @csrf
                                        <button type="submit" class="btn btn-sm w-100 fw-bold" style="border-radius:0.8rem; background:var(--brand-primary); color:white; font-size:0.8rem;">
                                            <i class="bi bi-lightning-charge-fill"></i> Mua ngay
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

          
        </div>
    </div>
</div>

<style>
    /* Container */
    .brand-tabs-modern{
        display:flex;
        gap:14px;
        align-items:center;
        flex-wrap:nowrap;
        overflow-x:auto;
        padding:6px 0;
        margin-bottom: 18px;
        scrollbar-width:none;
        -webkit-overflow-scrolling: touch;
    }

    .brand-tabs-modern::-webkit-scrollbar{
        display:none;
    }

    /* Pill button */
    .brand-pill{
        display:flex;
        align-items:center;
        gap:10px;
        padding:10px 22px;
        border-radius:40px;
        background:rgba(255,255,255,0.75);
        backdrop-filter:blur(10px);
        border:1px solid rgba(233,236,239,0.9);
        text-decoration:none;
        transition:all .3s ease;
        font-size:0.95rem;
        font-weight:600;
        white-space:nowrap;
        color: #0f172a;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.06);
    }

    .brand-pill img{
        height:24px;
        width: auto;
        display: block;
        filter: grayscale(20%) contrast(1.08);
        opacity: 0.92;
        transition:0.3s;
    }

    .brand-pill:focus-visible{
        outline: none;
        box-shadow:
            0 0 0 3px rgba(255, 255, 255, 0.8),
            0 0 0 6px rgba(214, 18, 18, 0.25),
            0 10px 25px rgba(15, 23, 42, 0.12);
    }

    /* Hover effect */
    .brand-pill:hover{
        transform:translateY(-4px);
        box-shadow:0 14px 30px rgba(15, 23, 42, 0.14);
        border-color:var(--brand-primary);
    }

    .brand-pill:hover img{
        filter:grayscale(0%) contrast(1.1);
        opacity: 1;
    }

    /* Active state */
    .brand-pill.active{
        background:linear-gradient(135deg,var(--brand-primary),#ff6b6b);
        color:#fff;
        border:none;
        transform: translateY(-2px) scale(1.03);
        box-shadow:
            0 12px 30px rgba(15, 23, 42, 0.18),
            0 0 0 3px rgba(255,255,255,0.7);
    }

    .brand-pill.active img{
        filter:none;
        opacity: 1;
    }

    .brand-pill.active span{
        letter-spacing: 0.1px;
    }

    /* View all link */
    .brand-view-all{
        font-size:0.8rem;
        font-weight:600;
        color:var(--brand-primary);
        text-decoration:none;
        padding-left:6px;
        transition:0.3s;
        white-space: nowrap;
        flex: 0 0 auto;
    }

    .brand-view-all:hover{
        opacity:0.7;
    }

    .category-banner-card {
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .category-banner-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0,0,0,0.15);
    }
</style>
@endif
