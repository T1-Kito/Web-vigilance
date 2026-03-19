<!-- Banner Slider (SwiperJS) -->
@once
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    @endpush
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js" defer></script>
    @endpush
@endonce
<style>
    /* Khung banner lớn: bo góc nhẹ, phẳng giống PhongVu */
    .banner-swiper {
        width: 100%;
        max-width: 100%;
        margin-left: 0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 8px 22px rgba(0, 0, 0, 0.10);
        margin-bottom: 8px; /* giảm khoảng trống dưới banner lớn */
        background: #ffffff;
    }
    /* Chiều cao banner desktop gần với mẫu PhongVu */
    .banner-swiper .swiper-slide {
        height: 320px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
    }
    .banner-swiper .swiper-slide > a {
        display: block;
        width: 100%;
        height: 100%;
    }
    .banner-swiper .swiper-slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center center;
        display: block;
        border-radius: 14px;
        transition: transform 0.5s cubic-bezier(.22,1,.36,1), box-shadow 0.25s;
        background: transparent;
    }
    .banner-swiper .swiper-slide-active img {
        transform: scale(1.01);
        box-shadow: 0 10px 26px rgba(0, 0, 0, 0.15);
    }
    
    .swiper-pagination-bullet-active {
        background: #e30019;
    }
    .swiper-button-next, .swiper-button-prev {
        color: #e30019;
    }
    @media (max-width: 991px) {
        .banner-swiper {
            width: 100%;
            margin-left: 0;
            max-width: 100%;
        }
        .banner-swiper .swiper-slide { height: 260px; }
        .banner-swiper .swiper-slide-active img { animation: none; }
    }
    @media (max-width: 768px) {
        .banner-swiper {
            width: 100%;
            margin-left: 0;
            max-width: 100%;
        }
        .banner-swiper .swiper-slide { height: 220px; }
        .banner-swiper .swiper-slide-active img { animation: none; }
    }
    @media (max-width: 600px) {
        .banner-swiper {
            width: 100%;
            margin-left: 0;
            max-width: 100%;
        }
        .banner-swiper .swiper-slide { height: 200px; }
        .banner-swiper .swiper-slide-active img { animation: none; }
    }

    @media (max-width: 575.98px) {
        .banner-swiper {
            border-radius: 16px;
        }
        .banner-swiper .swiper-slide {
            height: auto;
            aspect-ratio: 16 / 9;
        }
        .banner-swiper .swiper-slide img {
            object-fit: contain;
            background: #fff;
            transform: none !important;
            box-shadow: none !important;
        }
    }
</style>
<!-- Thêm class banner-slider để JS Swiper bắt đúng root -->
<div class="banner-swiper banner-slider">
    <div class="swiper">
        <div class="swiper-wrapper">
            @php
                $dbBanners = \App\Models\Banner::active()->position('general')->orderBy('sort_order')->get();
                $firstBannerUrl = null;
                if ($dbBanners->count()) {
                    $firstBannerUrl = $dbBanners->first()->image_url;
                } else {
                    $firstBannerUrl = asset('images/banner1.jpg');
                }
            @endphp
            @push('preload')
                <link rel="preload" as="image" href="{{ $firstBannerUrl }}" fetchpriority="high">
            @endpush
            @if($dbBanners->count())
                @foreach($dbBanners as $i => $b)
                    <div class="swiper-slide">
                        @if($b->link_url)
                            <a href="{{ $b->link_url }}" target="_blank" rel="noopener">
                                <img src="{{ $b->image_url }}" alt="{{ $b->title ?? 'Banner' }}" decoding="async" @if($i===0) fetchpriority="high" loading="eager" @else loading="lazy" @endif>
                            </a>
                        @else
                            <img src="{{ $b->image_url }}" alt="{{ $b->title ?? 'Banner' }}" decoding="async" @if($i===0) fetchpriority="high" loading="eager" @else loading="lazy" @endif>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="swiper-slide"><img src="{{ asset('images/banner1.jpg') }}" alt="Banner 1" decoding="async" fetchpriority="high" loading="eager"></div>
                <div class="swiper-slide"><img src="{{ asset('images/banner2.jpg') }}" alt="Banner 2" decoding="async" loading="lazy"></div>
                <div class="swiper-slide"><img src="{{ asset('images/banner3.jpg') }}" alt="Banner 3" decoding="async" loading="lazy"></div>
                <div class="swiper-slide"><img src="{{ asset('images/banner4.jpg') }}" alt="Banner 4" decoding="async" loading="lazy"></div>
            @endif
        </div>
        <!-- Add Pagination -->
        <div class="swiper-pagination"></div>
        <!-- Add Navigation -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
</div>
<script>
    (function () {
        function initBannerSwiper() {
            if (window.__bannerSideSwiper) return true;
            if (typeof window.Swiper === 'undefined') return false;

            var root = document.querySelector('.banner-slider');
            if (!root) return true;

            var swiperEl = root.querySelector('.swiper');
            if (!swiperEl) return true;

            var paginationEl = root.querySelector('.swiper-pagination');
            var nextEl = root.querySelector('.swiper-button-next');
            var prevEl = root.querySelector('.swiper-button-prev');

            window.__bannerSideSwiper = new Swiper(swiperEl, {
                loop: true,
                autoplay: {
                    delay: 3500,
                    disableOnInteraction: false,
                },
                effect: 'slide',
                speed: 650,
                pagination: {
                    el: paginationEl,
                    clickable: true,
                },
                navigation: {
                    nextEl: nextEl,
                    prevEl: prevEl,
                },
            });
            return true;
        }

        if (initBannerSwiper()) return;

        var tries = 0;
        var timer = setInterval(function () {
            tries++;
            if (initBannerSwiper() || tries > 50) {
                clearInterval(timer);
            }
        }, 100);

        window.addEventListener('load', function () {
            initBannerSwiper();
        });
    })();
</script>
