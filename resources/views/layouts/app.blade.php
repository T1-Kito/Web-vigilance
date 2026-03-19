<!-- Layout admin cơ bản, Bootstrap 5, sidebar trái, topbar, content -->
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - @yield('title', 'Quản trị')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        body { background: #f4f6fa; }
        .admin-sidebar {
            min-height: 100vh;
            background: linear-gradient(160deg, #0056b3 60%, #00B894 100%);
            color: #fff;
            width: 240px;
            border-radius: 0 2rem 2rem 0;
            box-shadow: 2px 0 16px 0 #007bff22;
            transition: width 0.2s;
        }
        .admin-sidebar.collapsed { width: 70px; }
        .admin-sidebar .sidebar-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2.5rem;
        }
        .admin-sidebar .sidebar-logo img {
            max-width: 120px;
            max-height: 60px;
            display: block;
        }
        .admin-sidebar a {
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 6px;
            font-weight: 500;
            transition: background 0.15s, color 0.15s;
        }
        .admin-sidebar a.active, .admin-sidebar a:hover {
            background: #00B894;
            color: #fff;
        }
        .admin-sidebar .sidebar-toggle {
            background: none;
            border: none;
            color: #fff;
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            cursor: pointer;
        }
        .admin-topbar {
            background: #fff;
            border-bottom: 1px solid #F1F1F1;
            box-shadow: 0 2px 12px #007bff11;
            min-height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
        }
        .admin-topbar .admin-user {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .admin-topbar .admin-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #007BFF;
        }
        .admin-content {
            padding: 32px 24px;
        }
        .admin-breadcrumb {
            font-size: 1.05em;
            color: #007BFF;
            margin-bottom: 1.2rem;
        }
        .admin-table {
            background: #fff;
            border-radius: 1.2rem;
            box-shadow: 0 2px 16px #007bff11;
            overflow: hidden;
        }
        .admin-table th {
            background: #F1F1F1;
            position: sticky;
            top: 0;
            z-index: 2;
            font-weight: 600;
        }
        .admin-table td, .admin-table th {
            vertical-align: middle;
        }
        .admin-table tr {
            transition: background 0.13s;
        }
        .admin-table tr:hover {
            background: #f0f8ff;
        }
        .badge-status {
            border-radius: 1rem;
            padding: 0.4em 1em;
            font-size: 0.98em;
            font-weight: 500;
        }
        .badge-status.active { background: #00B894; color: #fff; }
        .badge-status.inactive { background: #ccc; color: #333; }
        .admin-action-btn {
            border-radius: 0.7rem !important;
            font-size: 1.1em;
            padding: 0.35em 0.7em;
            margin: 0 2px;
            transition: background 0.15s, color 0.15s;
        }
        .admin-action-btn.edit {
            background: #fff;
            color: #007BFF;
            border: 1.5px solid #007BFF;
        }
        .admin-action-btn.edit:hover {
            background: #007BFF;
            color: #fff;
        }
        .admin-action-btn.delete {
            background: #fff;
            color: #e74c3c;
            border: 1.5px solid #e74c3c;
        }
        .admin-action-btn.delete:hover {
            background: #e74c3c;
            color: #fff;
        }
        .admin-table .btn, .admin-table .btn:focus {
            box-shadow: none;
        }
        .admin-pagination {
            display: flex;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }
        .admin-pagination .pagination {
            border-radius: 1.5rem;
            box-shadow: 0 2px 12px 0 #007bff11;
            padding: 0.5rem 1.2rem;
            background: #fff;
            gap: 0.25rem;
        }
        .admin-pagination .page-item .page-link {
            border-radius: 0.8rem !important;
            border: 1.5px solid #007BFF;
            color: #007BFF;
            font-weight: 500;
            margin: 0 2px;
            transition: all 0.18s;
            box-shadow: none;
        }
        .admin-pagination .page-item.active .page-link,
        .admin-pagination .page-item .page-link:focus,
        .admin-pagination .page-item .page-link:hover {
            background: #007BFF;
            color: #fff;
            border-color: #007BFF;
            box-shadow: 0 2px 8px #007bff22;
        }
        .admin-pagination .page-item.disabled .page-link {
            color: #ccc;
            background: #f8f9fa;
            border-color: #eee;
        }
        @media (max-width: 991px) {
            .admin-sidebar { width: 100px; }
            .admin-content { padding: 16px 4px; }
        }
    </style>
</head>
<body>
<div class="d-flex">
    <nav class="admin-sidebar p-3">
        <button class="sidebar-toggle" onclick="document.querySelector('.admin-sidebar').classList.toggle('collapsed')"><i class="bi bi-list"></i></button>
        <div class="sidebar-logo mb-4"><img src="/logovigilance.jpg" alt="Logo"></div>
        <a href="/admin/products" class="@if(request()->is('admin/products*')) active @endif"><i class="bi bi-box-seam me-2"></i> Sản phẩm</a>
        <a href="/admin/categories" class="@if(request()->is('admin/categories*')) active @endif"><i class="bi bi-list-ul me-2"></i> Danh mục</a>
        <a href="/admin/addons" class="@if(request()->is('admin/addons*')) active @endif"><i class="bi bi-puzzle me-2"></i> Phụ kiện/Mua kèm</a>
        <a href="/admin/banners" class="@if(request()->is('admin/banners*')) active @endif"><i class="bi bi-image me-2"></i> Banner</a>
        <a href="/admin/users" class="@if(request()->is('admin/users*')) active @endif"><i class="bi bi-people me-2"></i> Người dùng</a>
        <a href="/admin/orders" class="@if(request()->is('admin/orders*')) active @endif"><i class="bi bi-receipt me-2"></i> Đơn hàng</a>
        <a href="/" class="mt-4"><i class="bi bi-house-door me-2"></i> Về trang chủ</a>
    </nav>
    <div class="flex-grow-1">
        <div class="admin-topbar">
            <div class="admin-breadcrumb">
                @yield('title', 'Quản trị hệ thống')
            </div>
            <div class="admin-user">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'Admin') }}&background=007BFF&color=fff" class="admin-avatar" alt="Avatar">
                <span class="fw-bold">{{ Auth::user()->name ?? 'Admin' }}</span>
                <a href="/logout" class="btn btn-sm btn-outline-danger ms-2"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
            </div>
        </div>
        <main class="admin-content">
            <div class="mb-3 d-flex gap-2">
                <a href="/" class="btn btn-outline-primary"><i class="bi bi-house-door"></i> Trang chủ</a>
                <button onclick="window.history.back()" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Quay lại</button>
            </div>
            @yield('content')
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Sidebar toggle
    document.querySelector('.sidebar-toggle').onclick = function() {
        document.querySelector('.admin-sidebar').classList.toggle('collapsed');
    };
</script>
</body>
</html> 