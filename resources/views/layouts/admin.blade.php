<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - VIKHANG Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('images/vigilance-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/vigilance-logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        /* Custom Select2 styling */
        .select2-container--bootstrap-5 .select2-selection {
            height: 48px !important;
            border-radius: 12px !important;
            border: 1px solid #dee2e6 !important;
            padding: 0.5rem 0.75rem !important;
        }
        
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            line-height: 1.5 !important;
            padding-left: 0 !important;
            font-size: 1.08em !important;
        }
        
        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
            height: 46px !important;
        }
        
        .select2-container--bootstrap-5 .select2-dropdown {
            border-radius: 12px !important;
            border: 1px solid #dee2e6 !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
        }
        
        .select2-container--bootstrap-5 .select2-search--dropdown .select2-search__field {
            border-radius: 8px !important;
            border: 1px solid #dee2e6 !important;
            padding: 8px 12px !important;
            font-size: 14px !important;
            width: 100% !important;
        }
        
        .select2-container--bootstrap-5 .select2-search--dropdown {
            padding: 8px !important;
        }
        
        .select2-container--bootstrap-5 .select2-results__option {
            padding: 8px 12px !important;
            border-bottom: 1px solid #f8f9fa !important;
        }
        
        .select2-container--bootstrap-5 .select2-results__option--highlighted[aria-selected] {
            background-color: #007bff !important;
            color: white !important;
        }
        
        .select2-container--bootstrap-5 .select2-results__option[aria-selected=true] {
            background-color: #e9ecef !important;
        }
    </style>
    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-bg: #1e3a8a;
            --sidebar-hover: #3b82f6;
            --sidebar-active: #fbbf24;
            --sidebar-active-text: #1f2937;
            --content-bg: #f8fafc;
        }
        
        body {
            background-color: var(--content-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Sidebar */
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--sidebar-bg) 0%, #1e40af 100%);
            color: white;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
        }
        
        /* Logo Section */
        .logo-section {
            padding: 20px 15px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.15);
            margin-bottom: 5px;
        }
        
        .logo-section img {
            max-width: 160px;
            max-height: 60px;
            width: auto;
            height: auto;
            object-fit: contain;
            display: block;
            margin: 0 auto;
            filter: brightness(0) invert(1);
            opacity: 0.95;
            transition: opacity 0.3s ease;
        }
        
        .logo-section:hover img {
            opacity: 1;
        }
        
        /* Alternative: Logo với background trắng nhẹ nhàng */
        .logo-section.white-bg {
            background: rgba(255,255,255,0.95);
            border-radius: 12px;
            margin: 15px 12px;
            padding: 15px 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .logo-section.white-bg img {
            filter: none;
            opacity: 1;
        }
        
        /* Profile Section */
        .profile-section {
            padding: 20px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
        }
        
        .profile-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin: 0 auto 12px;
            overflow: hidden;
            background: linear-gradient(45deg, #3b82f6, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5em;
            font-weight: bold;
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }
        
        .profile-name {
            font-size: 1.3em;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .profile-greeting {
            font-size: 0.9em;
            opacity: 0.8;
        }
        
        /* Navigation Menu */
        .nav-menu {
            padding: 20px 0;
        }
        
        .nav-item {
            margin: 5px 15px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 1.05em;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .nav-link.active {
            background: var(--sidebar-active);
            color: var(--sidebar-active-text);
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
        }
        
        .nav-link.active:hover {
            background: var(--sidebar-active);
            color: var(--sidebar-active-text);
        }
        
        .nav-icon {
            width: 24px;
            height: 24px;
            margin-right: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Main Content */
        .admin-main {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            padding: 20px;
        }
        
        /* Header */
        .admin-header {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header-logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .header-logo img {
            max-height: 50px;
            max-width: 180px;
            width: auto;
            height: auto;
            object-fit: contain;
        }
        
        .page-title {
            font-size: 2em;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            overflow: hidden;
            background: linear-gradient(45deg, #3b82f6, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2em;
        }
        
        .logout-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }
        
        /* Content Cards */
        .content-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .admin-sidebar.show {
                transform: translateX(0);
            }
            
            .admin-main {
                margin-left: 0;
            }
            
            .sidebar-toggle {
                display: block;
            }
        }
        
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: var(--sidebar-bg);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <!-- Sidebar Toggle for Mobile -->
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="bi bi-list"></i>
    </button>
    
    <!-- Sidebar -->
    <div class="admin-sidebar" id="sidebar">
        <!-- Logo Section -->
        <div class="logo-section white-bg">
            <img src="{{ asset('logovigilance.jpg') }}" alt="Vigilance Logo" class="company-logo">
        </div>
        
        <!-- Profile Section -->
        <div class="profile-section">
            @php
                $adminUnreadNotificationsCount = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;
            @endphp
            <div class="position-relative d-inline-block">
                <div class="profile-avatar">
                    @if(Auth::user() && Auth::user()->avatar)
                        <img src="{{ route('admin.profile.avatar.image', ['v' => optional(Auth::user()->updated_at)->timestamp]) }}" alt="Avatar" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                    @else
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 2)) }}
                    @endif
                </div>
                <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-light position-absolute" style="top: -8px; right: -8px; border-radius: 999px; padding: 6px 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.12);">
                    <i class="bi bi-bell"></i>
                    <span id="vw-admin-bell-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65em; display: {{ $adminUnreadNotificationsCount > 0 ? 'inline-block' : 'none' }};">
                        {{ $adminUnreadNotificationsCount > 0 ? $adminUnreadNotificationsCount : '' }}
                    </span>
                </a>
            </div>
            <div class="profile-name">{{ Auth::user()->name ?? 'Admin' }}</div>
            <div class="profile-greeting">Chào mừng bạn trở lại</div>
        </div>
        
        <!-- Navigation Menu -->
        <div class="nav-menu">
            <div class="nav-item">
                <a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                    <div class="nav-icon"><i class="bi bi-speedometer2"></i></div>
                    Bảng điều khiển
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                    <div class="nav-icon"><i class="bi bi-tags"></i></div>
                    Quản lý sản phẩm
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <div class="nav-icon"><i class="bi bi-grid"></i></div>
                    Quản lý danh mục
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('admin.banners.index') }}" class="nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                    <div class="nav-icon"><i class="bi bi-images"></i></div>
                    Quản lý banner
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('admin.orders.index') }}" class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                    <div class="nav-icon"><i class="bi bi-cart-check"></i></div>
                    Quản lý đơn hàng
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('admin.warranties.index') }}" class="nav-link {{ request()->routeIs('admin.warranties.*') ? 'active' : '' }}">
                    <div class="nav-icon"><i class="bi bi-shield-check"></i></div>
                    Quản lý bảo hành
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('admin.warranties.claims') }}" class="nav-link {{ request()->routeIs('admin.warranties.claims') ? 'active' : '' }}">
                    <div class="nav-icon"><i class="bi bi-list-check"></i></div>
                    Yêu cầu bảo hành
                </a>
            </div>
            
            <div class="nav-item">
                <a href="{{ route('admin.repair-forms.index') }}" class="nav-link {{ request()->routeIs('admin.repair-forms.*') ? 'active' : '' }}">
                    <div class="nav-icon"><i class="bi bi-file-earmark-text"></i></div>
                    Phiếu nhận & trả bảo hành
                </a>
            </div>
            
            @php
                $superAdminEmail = strtolower(trim((string) env('SUPER_ADMIN_EMAIL', '')));
                $currentEmail = strtolower(trim((string) (Auth::user()->email ?? '')));
                $canManageUsers = ($superAdminEmail === '') || ($currentEmail !== '' && $currentEmail === $superAdminEmail);
            @endphp
            @if($canManageUsers)
                <div class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <div class="nav-icon"><i class="bi bi-people"></i></div>
                        Quản lý người dùng
                    </a>
                </div>
            @endif

            <div class="nav-item">
                <a href="{{ route('admin.activity-logs.index') }}" class="nav-link {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}">
                    <div class="nav-icon"><i class="bi bi-clock-history"></i></div>
                    Nhật ký hoạt động
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.customers.index') }}" class="nav-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                    <div class="nav-icon"><i class="bi bi-person-badge"></i></div>
                    Quản lý khách hàng
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.borrow-requests.index') }}" class="nav-link {{ request()->routeIs('admin.borrow-requests.*') ? 'active' : '' }}">
                    <div class="nav-icon"><i class="bi bi-clipboard-check"></i></div>
                    Quản lý mượn hàng
                </a>
            </div>

            <div class="nav-item">
                <a href="{{ route('admin.chat-support.index') }}" class="nav-link {{ request()->routeIs('admin.chat-support.*') ? 'active' : '' }}">
                    <div class="nav-icon"><i class="bi bi-chat-left-text"></i></div>
                    <span style="display: inline-flex; align-items: center; gap: 8px; width: 100%;">
                        <span>Hộp thư Chat</span>
                        <span id="vw-admin-chat-unread-nav" class="badge rounded-pill bg-danger" style="font-size: 0.75em; display: none;"></span>
                    </span>
                </a>
            </div>
            
            <div class="nav-item">
                <a href="#" class="nav-link">
                    <div class="nav-icon"><i class="bi bi-graph-up"></i></div>
                    Báo cáo doanh thu
                </a>
            </div>
            
            <div class="nav-item">
                <a href="#" class="nav-link">
                    <div class="nav-icon"><i class="bi bi-gear"></i></div>
                    Cài đặt hệ thống
                </a>
            </div>
            
            <div class="nav-item">
                <div class="nav-link" style="cursor: pointer;" onclick="toggleColorPicker()">
                    <div class="nav-icon"><i class="bi bi-palette"></i></div>
                    Thay đổi màu
                </div>
            </div>
            
            <div class="nav-item" style="margin-top: 30px;">
                <a href="{{ route('home') }}" class="nav-link">
                    <div class="nav-icon"><i class="bi bi-house"></i></div>
                    Về trang chủ
                </a>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="admin-main">
        <!-- Header -->
        <div class="admin-header">
            <div class="header-logo">
                <img src="{{ asset('logovigilance.jpg') }}" alt="Vigilance Logo">
                <h1 class="page-title">@yield('title')</h1>
            </div>
            <div class="user-info">
                <div class="user-avatar">
                    @if(Auth::user() && Auth::user()->avatar)
                        <img src="{{ route('admin.profile.avatar.image', ['v' => optional(Auth::user()->updated_at)->timestamp]) }}" alt="Avatar" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                    @else
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 2)) }}
                    @endif
                </div>
                <span style="font-weight: 600; color: #374151;">{{ Auth::user()->name ?? 'Admin' }}</span>
                <a href="{{ route('admin.profile.avatar.edit') }}" class="btn btn-sm btn-outline-primary" style="margin-left: 10px;">
                    <i class="bi bi-person-bounding-box"></i> Đổi avatar
                </a>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="logout-btn">
                        <i class="bi bi-box-arrow-right"></i> Đăng xuất
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Page Content -->
        @yield('content')
    </div>
    
    <!-- Color Picker Modal -->
    <div class="modal fade" id="colorPickerModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 15px 15px 0 0; border: none;">
                    <h5 class="modal-title">
                        <i class="bi bi-palette me-2"></i>Thay đổi màu sidebar
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold mb-3">Chọn màu chủ đạo:</label>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <div class="color-option" data-color="#1e3a8a" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #1e3a8a, #1e40af); cursor: pointer; border: 3px solid #e5e7eb; transition: all 0.3s ease;" onclick="changeSidebarColor('#1e3a8a', '#1e40af')"></div>
                            <div class="color-option" data-color="#059669" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #059669, #047857); cursor: pointer; border: 3px solid #e5e7eb; transition: all 0.3s ease;" onclick="changeSidebarColor('#059669', '#047857')"></div>
                            <div class="color-option" data-color="#7c3aed" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #7c3aed, #6d28d9); cursor: pointer; border: 3px solid #e5e7eb; transition: all 0.3s ease;" onclick="changeSidebarColor('#7c3aed', '#6d28d9')"></div>
                            <div class="color-option" data-color="#dc2626" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #dc2626, #b91c1c); cursor: pointer; border: 3px solid #e5e7eb; transition: all 0.3s ease;" onclick="changeSidebarColor('#dc2626', '#b91c1c')"></div>
                            <div class="color-option" data-color="#ea580c" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #ea580c, #c2410c); cursor: pointer; border: 3px solid #e5e7eb; transition: all 0.3s ease;" onclick="changeSidebarColor('#ea580c', '#c2410c')"></div>
                            <div class="color-option" data-color="#0891b2" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #0891b2, #0e7490); cursor: pointer; border: 3px solid #e5e7eb; transition: all 0.3s ease;" onclick="changeSidebarColor('#0891b2', '#0e7490')"></div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold mb-3">Hoặc chọn màu tùy chỉnh:</label>
                        <div class="d-flex align-items-center gap-3">
                            <input type="color" id="customColor" class="form-control form-control-color" style="width: 60px; height: 60px; border-radius: 50%; border: 3px solid #e5e7eb; cursor: pointer;">
                            <button type="button" class="btn btn-primary" onclick="applyCustomColor()">
                                <i class="bi bi-check-lg me-2"></i>Áp dụng
                            </button>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary" onclick="resetToDefault()">
                            <i class="bi bi-arrow-clockwise me-2"></i>Mặc định
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-2"></i>Đóng
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.sidebar-toggle');
            
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
        
        // Color picker functions
        function toggleColorPicker() {
            const modal = new bootstrap.Modal(document.getElementById('colorPickerModal'));
            modal.show();
        }
        
        function changeSidebarColor(primaryColor, secondaryColor, event) {
            const sidebar = document.getElementById('sidebar');
            const profileSection = document.querySelector('.profile-section');
            
            // Update sidebar background
            if (sidebar) {
            sidebar.style.background = `linear-gradient(180deg, ${primaryColor} 0%, ${secondaryColor} 100%)`;
            }
            
            // Update profile section
            if (profileSection) {
                profileSection.style.background = `linear-gradient(135deg, ${primaryColor}, ${secondaryColor})`;
            }
            
            // Update active nav item
            const activeNav = document.querySelector('.nav-link.active');
            if (activeNav) {
                activeNav.style.background = `linear-gradient(135deg, ${primaryColor}, ${secondaryColor})`;
            }
            
            // Save to localStorage
            localStorage.setItem('sidebarColor', primaryColor);
            localStorage.setItem('sidebarSecondaryColor', secondaryColor);
            
            // Add visual feedback
            document.querySelectorAll('.color-option').forEach(option => {
                option.style.border = '3px solid #e5e7eb';
            });
            if (event && event.target) {
            event.target.style.border = '3px solid #fbbf24';
            }
        }
        
        function applyCustomColor() {
            const customColor = document.getElementById('customColor').value;
            const lighterColor = lightenColor(customColor, 20);
            changeSidebarColor(customColor, lighterColor, null);
        }
        
        function lightenColor(color, percent) {
            const num = parseInt(color.replace("#",""), 16);
            const amt = Math.round(2.55 * percent);
            const R = (num >> 16) + amt;
            const G = (num >> 8 & 0x00FF) + amt;
            const B = (num & 0x0000FF) + amt;
            return "#" + (0x1000000 + (R<255?R<1?0:R:255)*0x10000 + (G<255?G<1?0:G:255)*0x100 + (B<255?B<1?0:B:255)).toString(16).slice(1);
        }
        
        function resetToDefault() {
            changeSidebarColor('#1e3a8a', '#1e40af', null);
            document.getElementById('customColor').value = '#1e3a8a';
        }
        
        // Load saved color on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedColor = localStorage.getItem('sidebarColor');
            const savedSecondaryColor = localStorage.getItem('sidebarSecondaryColor');

            if (savedColor && savedSecondaryColor) {
                changeSidebarColor(savedColor, savedSecondaryColor, null);
            }

            const isChatSupportPage = {{ request()->routeIs('admin.chat-support.*') ? 'true' : 'false' }};
            const unreadNotificationsCount = Number({{ (int) $adminUnreadNotificationsCount }});
            const seenKey = 'vw_admin_chat_seen_user_id_v1';

            const bellBadge = document.getElementById('vw-admin-bell-badge');
            const navBadge = document.getElementById('vw-admin-chat-unread-nav');

            function setBadge(chatCount) {
                const c = Number(chatCount || 0);
                const chatShow = c > 0;

                if (navBadge) {
                    navBadge.textContent = chatShow ? String(c) : '';
                    navBadge.style.display = chatShow ? 'inline-block' : 'none';
                }

                if (bellBadge) {
                    const total = unreadNotificationsCount + c;
                    const show = total > 0;
                    bellBadge.textContent = show ? String(total) : '';
                    bellBadge.style.display = show ? 'inline-block' : 'none';
                }
            }

            let timer = null;
            let backoffMs = 10000;
            const slowMs = 10000;
            const fastMs = 2500;
            const maxMs = 60000;
            let aborter = null;

            function schedule(ms) {
                if (timer) clearTimeout(timer);
                timer = setTimeout(poll, ms);
            }

            async function poll() {
                if (!navBadge && !bellBadge) {
                    return;
                }

                if (document.visibilityState === 'hidden') {
                    schedule(slowMs);
                    return;
                }

                const sinceId = Number(localStorage.getItem(seenKey) || 0);
                const url = `{{ route('admin.chat-support.unread') }}?since_id=${encodeURIComponent(sinceId)}`;

                try {
                    if (aborter) aborter.abort();
                    aborter = new AbortController();

                    const res = await fetch(url, {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin',
                        signal: aborter.signal,
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const data = await res.json();
                    if (!data || data.ok !== true) throw new Error('Bad response');

                    const count = Number(data.count || 0);
                    const maxId = Number(data.max_id || 0);

                    if (isChatSupportPage && maxId > 0) {
                        localStorage.setItem(seenKey, String(maxId));
                        setBadge(0);
                    } else {
                        setBadge(count);
                    }

                    backoffMs = count > 0 ? fastMs : slowMs;
                    schedule(backoffMs);
                } catch (e) {
                    backoffMs = Math.min(maxMs, Math.max(slowMs, backoffMs * 2));
                    schedule(backoffMs);
                }
            }

            poll();
        });
    </script>
</body>
</html>