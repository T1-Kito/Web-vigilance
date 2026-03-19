<aside class="bg-white {{ !empty($offcanvas) ? 'category-offcanvas p-0 mb-0 shadow-none rounded-0 border-0' : 'shadow-sm rounded-3 p-2 mb-4' }} {{ !empty($overlay) ? 'home-category-aside' : '' }}" style="min-width:{{ !empty($offcanvas) ? '0' : '180px' }}; width:100%; max-width:{{ !empty($offcanvas) ? '100%' : '240px' }}; border:{{ !empty($offcanvas) ? '0' : '1.5px solid #F1F1F1' }};">
    <div class="category-sidebar-scroll">
        <ul class="nav flex-column category-sidebar">
        @php
            $currentCategoryId = isset($currentCategory) ? (int) $currentCategory->id : null;
            $iconMap = [
                'máy chấm công' => 'bi-fingerprint',
                'chấm công' => 'bi-fingerprint',
                'cổng phân làn' => 'bi-signpost-2',
                'phân làn' => 'bi-signpost-2',
                'báo động' => 'bi-bell',
                'kiểm soát cửa' => 'bi-door-closed',
                'thang máy' => 'bi-building',
                'phân tầng' => 'bi-layers',
                'hệ thống pos' => 'bi-cash-stack',
                'pos' => 'bi-cash-stack',
                'camera' => 'bi-camera-video',
                'camera quan sát' => 'bi-camera-video',
                'quan sát' => 'bi-camera-video',
                'thiết bị mạng' => 'bi-router',
                'mạng' => 'bi-router',
                'bộ đàm' => 'bi-broadcast',
                'đàm' => 'bi-broadcast',
                'thiết bị thông minh' => 'bi-cpu',
                'thông minh' => 'bi-cpu',
                'phụ kiện' => 'bi-headphones',
                'đồng hồ' => 'bi-watch',
                'âm thanh' => 'bi-mic',
                'tivi' => 'bi-tv',
                'laptop' => 'bi-laptop',
                'pc' => 'bi-pc-display',
                'khuyến mãi' => 'bi-gift',
            ];
            if (!function_exists('renderMenuTree')) {
                function renderMenuTree($categories, $iconMap, $level = 0, $currentCategoryId = null) {
                    foreach($categories as $cat) {
                        $icon = 'bi-grid-3x3-gap-fill';
                        foreach($iconMap as $key => $val) {
                            if(Str::of(Str::lower($cat->name))->contains($key)) {
                                $icon = $val;
                                break;
                            }
                        }
                        $isActive = $currentCategoryId && ((int) $cat->id === (int) $currentCategoryId);
                        $hasChildren = ($cat->children && $cat->children->count());
                        $hasActiveChild = false;
                        if ($hasChildren && $currentCategoryId) {
                            $hasActiveChild = $cat->children->contains('id', (int) $currentCategoryId);
                        }
                        $submenuId = 'cat-submenu-' . $cat->id;
                        $submenuShown = ($hasChildren && ($hasActiveChild));
                        echo '<li class="nav-item mb-1 category-parent level-'.$level.'" style="position:relative;">';
                        echo '<a href="'.route('category.show', $cat->slug).'" class="nav-link d-flex align-items-center py-1 px-2 rounded-2 small fw-semibold category-link'.($isActive ? ' active' : '').'">';
                        if($level == 0) echo '<i class="bi '.$icon.' me-2" style="color:var(--brand-secondary); font-size:1.05em; min-width: 1.35em;"></i> ';
                        echo $cat->name;
                        if($hasChildren) {
                            echo '<span class="ms-auto d-inline-flex align-items-center justify-content-center category-submenu-toggle" role="button" aria-controls="'.$submenuId.'" aria-expanded="'.($submenuShown ? 'true' : 'false').'" data-submenu-id="'.$submenuId.'" style="width:28px; height:28px; border-radius:10px;">';
                            echo '<i class="bi '.($submenuShown ? 'bi-chevron-up' : 'bi-chevron-down').'" style="color:var(--brand-secondary); font-size:0.9em;"></i>';
                            echo '</span>';
                        }
                        echo '</a>';
                        if($hasChildren) {
                            echo '<ul id="'.$submenuId.'" class="nav flex-column category-submenu ps-4 mt-1" style="background:transparent; box-shadow:none; display:'.($submenuShown ? 'block' : 'none').';">';
                            renderMenuTree($cat->children, $iconMap, $level+1, $currentCategoryId);
                            echo '</ul>';
                        }
                        echo '</li>';
                    }
                }
            }
            renderMenuTree($categories, $iconMap, 0, $currentCategoryId);
        @endphp
        </ul>
    </div>
    @if(empty($offcanvas))
        <hr>
        <a href="{{ route('orders.history') }}" class="d-flex align-items-center gap-2 px-2 py-2 mt-2 rounded-2 fw-semibold" style="color:var(--brand-secondary); font-size:1.04em; text-decoration:none;">
            <i class="bi bi-clock-history" style="font-size:1.2em;"></i> Lịch sử đơn hàng
        </a>
    @endif
    <style>
        .home-category-aside {
            display: flex;
            flex-direction: column;
        }

        .category-offcanvas .category-sidebar {
            padding: 6px 0;
        }

        .category-offcanvas .category-sidebar .category-link {
            font-size: 0.98rem !important;
            line-height: 1.2;
            padding-top: 14px !important;
            padding-bottom: 14px !important;
            padding-left: 14px !important;
            padding-right: 14px !important;
            border-radius: 0 !important;
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .category-offcanvas .category-sidebar .category-parent.level-0:last-child .category-link {
            border-bottom: 0;
        }

        .category-offcanvas .category-submenu {
            padding-left: 18px !important;
            margin-top: 6px !important;
            margin-bottom: 6px !important;
        }

        .category-offcanvas .category-sidebar .category-submenu .category-link {
            text-transform: none;
            letter-spacing: 0;
            font-size: 0.92rem !important;
            padding-top: 10px !important;
            padding-bottom: 10px !important;
            border-bottom: 0;
        }

        .category-offcanvas .category-sidebar .category-parent.level-0 > .category-link i {
            color: #e11d2e !important;
            font-size: 1.15em !important;
        }

        .category-offcanvas .category-submenu-toggle {
            width: 34px !important;
            height: 34px !important;
            border-radius: 10px !important;
        }

        .category-offcanvas .category-submenu-toggle i {
            color: #64748b !important;
        }
        .category-sidebar-title {
            font-size: 0.91em !important;
            line-height: 1.2;
        }
        .category-sidebar-scroll {
            flex: 1 1 auto;
            min-height: 0;
        }
        .category-sidebar .category-link {
            font-size: 0.74em !important;
            line-height: 1.18;
            color: var(--brand-secondary);
            transition: background 0.15s, color 0.15s;
        }
        .category-sidebar .category-submenu .category-link {
            font-size: 0.72em !important;
            line-height: 1.18;
        }
        .category-sidebar .category-link:hover {
            background: #F1F1F1;
            color: var(--brand-primary);
        }
        .category-sidebar .nav-link.active, .category-sidebar .nav-link:active {
            background: #F1F1F1;
            color: var(--brand-primary);
        }
        .category-submenu-toggle:hover {
            background: rgba(43,47,142,0.08);
        }

        @media (min-width: 768px) {
            /* Non-home pages: allow hover to expand submenu inline (avoid overlay/fixed positioning) */
            aside:not(.home-category-aside) .category-parent:hover > .category-submenu {
                display: block !important;
            }

            /* Desktop: submenu hiển thị dạng overlay để không đẩy chiều cao cột trái (tránh banner bị kéo xuống) */
            .home-category-aside .category-parent.level-0 > .category-submenu {
                position: fixed !important;
                left: 0;
                top: 0;
                z-index: 2000;
                background: #ffffff !important;
                border: 1px solid rgba(0,0,0,0.08);
                border-radius: 12px;
                box-shadow: 0 10px 24px rgba(0,0,0,0.10);
                padding: 8px 8px;
                margin-top: 0 !important;
                min-width: 220px;
                width: 260px;
                max-width: 320px;
                max-height: var(--home-hero-height, 520px);
                overflow-y: auto;
                display: none;
            }
            .home-category-aside .category-parent.level-0:hover > .category-submenu {
                display: block !important;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.category-submenu-toggle[data-submenu-id]').forEach(function (toggle) {
                toggle.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    var parentItem = toggle.closest('li.category-parent');
                    if (!parentItem) return;

                    var menu = parentItem.querySelector(':scope > ul.category-submenu');
                    if (!menu) return;

                    var isOpen = menu.style.display !== 'none' && menu.style.display !== '';
                    var nextOpen = !isOpen;
                    menu.style.display = nextOpen ? 'block' : 'none';

                    toggle.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
                    var icon = toggle.querySelector('i');
                    if (icon) {
                        icon.classList.remove('bi-chevron-up', 'bi-chevron-down');
                        icon.classList.add(nextOpen ? 'bi-chevron-up' : 'bi-chevron-down');
                    }
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            var mql = window.matchMedia('(min-width: 768px)');
            if (!mql.matches) return;

            var root = document.querySelector('.home-category-aside');
            if (!root) return;

            var sidebarScroll = root.querySelector('.category-sidebar-scroll');
            var openItem = null;
            var openMenu = null;

            function placeMenu(item, menu) {
                if (!item || !menu) return;
                var rect = item.getBoundingClientRect();
                var gap = 8;
                var left = rect.right + gap;
                var top = rect.top;

                menu.style.left = left + 'px';
                menu.style.top = top + 'px';

                var vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
                var vh = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);

                var menuRect = menu.getBoundingClientRect();
                if (menuRect.right > vw - 10) {
                    var altLeft = rect.left - menuRect.width - gap;
                    menu.style.left = Math.max(10, altLeft) + 'px';
                }
                if (menuRect.bottom > vh - 10) {
                    var maxH = Math.max(120, vh - top - 10);
                    menu.style.maxHeight = maxH + 'px';
                }
            }

            function onEnter(e) {
                var item = e.currentTarget;
                var menu = item.querySelector(':scope > .category-submenu');
                if (!menu) return;
                openItem = item;
                openMenu = menu;
                placeMenu(item, menu);
            }

            function onLeave(e) {
                if (openItem === e.currentTarget) {
                    openItem = null;
                    openMenu = null;
                }
            }

            var items = root.querySelectorAll('.category-parent.level-0');
            items.forEach(function (item) {
                item.addEventListener('mouseenter', onEnter);
                item.addEventListener('mouseleave', onLeave);
            });

            function onRelayout() {
                if (!openItem || !openMenu) return;
                placeMenu(openItem, openMenu);
            }

            window.addEventListener('resize', onRelayout);
            window.addEventListener('scroll', onRelayout, true);
            if (sidebarScroll) sidebarScroll.addEventListener('scroll', onRelayout, { passive: true });
        });
    </script>
</aside>