@extends('layouts.admin')

@section('title', 'Quản lý sản phẩm')

@section('content')
<div class="content-card">
    <!-- Action Bar -->
    <div style="padding: 25px 30px; border-bottom: 1px solid #e5e7eb;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; padding: 12px 20px; border-radius: 12px; font-weight: 600; font-size: 1.1em;">
                    <i class="bi bi-tags me-2"></i>Tổng: {{ $products->total() }} sản phẩm
    </div>
    @if(session('status'))
                    <div style="background: #dbeafe; color: #1e40af; padding: 10px 15px; border-radius: 8px; font-weight: 500;">
                        <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
                    </div>
    @endif
            </div>
            <a href="{{ route('admin.products.create') }}" style="background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 12px 24px; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 1.1em; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3); transition: all 0.3s ease;">
                <i class="bi bi-plus-circle"></i>Thêm sản phẩm
            </a>
        </div>
        
        <!-- Search Section -->
        <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 20px;">
            <h6 style="margin: 0 0 15px 0; color: #374151; font-weight: 600;">
                <i class="bi bi-search me-2"></i>Tìm kiếm sản phẩm
            </h6>
            
            @php
                $selectedCategory = null;
                if (request('search_category')) {
                    $selectedCategory = $categories->firstWhere('id', request('search_category'));
                }
            @endphp
            @if(request('search_name') || request('search_serial') || request('search_category'))
                <div style="background: #dbeafe; color: #1e40af; padding: 10px 15px; border-radius: 8px; margin-bottom: 15px; font-weight: 500;">
                    <i class="bi bi-info-circle me-2"></i>
                    Kết quả tìm kiếm: 
                    @if(request('search_name')) <strong>Tên: "{{ request('search_name') }}"</strong> @endif
                    @if(request('search_serial')) <strong>Số seri: "{{ request('search_serial') }}"</strong> @endif
                    @if($selectedCategory) <strong>Danh mục: "{{ $selectedCategory->name }}"</strong> @endif
                    - Tìm thấy {{ $products->total() }} sản phẩm
                </div>
            @endif
            <form method="GET" action="{{ route('admin.products.index') }}" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <div style="position: relative;">
                    <input type="text" 
                           name="search_name" 
                           value="{{ request('search_name') }}"
                           placeholder="Tìm theo tên sản phẩm..." 
                           style="padding: 10px 15px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 1em; min-width: 200px;">
                </div>
                <div style="position: relative;">
                    <select name="search_category" style="padding: 10px 15px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 1em; min-width: 200px;">
                        <option value="">Tất cả danh mục</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (string)request('search_category') === (string)$category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 1em;">
                    <i class="bi bi-search me-2"></i>Tìm kiếm
                </button>
                @if(request('search_name') || request('search_serial') || request('search_category'))
                    <a href="{{ route('admin.products.index') }}" style="background: linear-gradient(135deg, #6b7280, #4b5563); color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 1em;">
                        <i class="bi bi-x-circle me-2"></i>Xóa tìm kiếm
                    </a>
                @endif
            </form>
        </div>

        <!-- Import/Export Section -->
        <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0;">
            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                <form action="{{ route('admin.products.importExcel') }}" method="POST" enctype="multipart/form-data" style="display: flex; align-items: center; gap: 10px;">
        @csrf
                    <div style="position: relative;">
                        <input type="file" name="file" accept=".xlsx,.xls" required style="padding: 10px 15px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 1em; min-width: 200px;">
                    </div>
                    <button type="submit" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 1em;">
                        <i class="bi bi-upload me-2"></i>Import Excel
                    </button>
                </form>
                <a href="{{ route('admin.products.exportExcel') }}" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 1em;">
                    <i class="bi bi-file-earmark-excel me-2"></i>Export Excel
                </a>
            </div>
        </div>
    </div>
    
    <!-- Table -->
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 1.05em;">
            <thead>
                <tr style="background: linear-gradient(135deg, #1e3a8a, #1e40af); color: white;">
                    <th style="padding: 18px 15px; text-align: center; font-weight: 600; border-bottom: 2px solid #3b82f6;">#</th>
                    <th style="padding: 18px 15px; text-align: center; font-weight: 600; border-bottom: 2px solid #3b82f6;">Mã sản phẩm</th>
                    <th style="padding: 18px 15px; text-align: center; font-weight: 600; border-bottom: 2px solid #3b82f6;">Ảnh</th>
                    <th style="padding: 18px 15px; text-align: left; font-weight: 600; border-bottom: 2px solid #3b82f6;">Tên sản phẩm</th>
                    <th style="padding: 18px 15px; text-align: center; font-weight: 600; border-bottom: 2px solid #3b82f6;">Hãng</th>
                    <th style="padding: 18px 15px; text-align: right; font-weight: 600; border-bottom: 2px solid #3b82f6;">Giá bán</th>
                    <th style="padding: 18px 15px; text-align: center; font-weight: 600; border-bottom: 2px solid #3b82f6;">Nổi bật</th>
                    <th style="padding: 18px 15px; text-align: center; font-weight: 600; border-bottom: 2px solid #3b82f6;">Trạng thái</th>
                    <th style="padding: 18px 15px; text-align: center; font-weight: 600; border-bottom: 2px solid #3b82f6; white-space: nowrap;">Ngày tạo / cập nhật</th>
                    <th style="padding: 18px 15px; text-align: center; font-weight: 600; border-bottom: 2px solid #3b82f6;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                <tr id="product-{{ $product->id }}" style="border-bottom: 1px solid #e5e7eb; transition: all 0.3s ease;" onmouseover="this.style.backgroundColor='#f8fafc'" onmouseout="this.style.backgroundColor='white'">
                    <td style="padding: 15px; text-align: center; font-weight: 600; color: #6b7280;">{{ $loop->iteration }}</td>
                    <td style="padding: 15px; text-align: center; font-weight: 700; color: #1f2937; font-size: 0.95em; white-space: nowrap;">
                        {{ $product->serial_number ?: ('SP-' . str_pad($product->id, 4, '0', STR_PAD_LEFT)) }}
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        <img src="{{ asset('images/products/' . $product->image) }}" alt="{{ $product->name }}" style="width: 70px; height: 70px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            </td>
                    <td style="padding: 15px;">
                        <div style="font-weight: 700; color: #1f2937; margin-bottom: 5px; font-size: 1.1em;">{{ $product->name }}</div>
                        <div style="color: #6b7280; font-size: 0.9em;">{{ Str::limit($product->description, 60) }}</div>
                            </td>
                    <td style="padding: 15px; text-align: center; font-weight: 700; color: #1f2937;">
                        {{ $product->brand ?? '-' }}
                    </td>
                    <td style="padding: 15px; text-align: right; font-weight: 700; font-size: 1.1em;">
                        @if($product->price)
                            <button
                                type="button"
                                class="btn btn-link p-0 product-activity-hist product-activity-hist--price"
                                style="font-weight: 700; color: #059669; font-size: inherit; text-decoration: underline; text-underline-offset: 2px;"
                                data-url="{{ route('admin.products.activity-history', $product) }}"
                                title="Xem lịch sử thay đổi (tên, hãng, giá…)"
                            >{{ number_format($product->price, 0, ',', '.') }}đ</button>
                        @else
                            <span style="color: #9ca3af;">-</span>
                        @endif
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        @if($product->is_featured)
                            <span style="background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #1f2937; padding: 6px 12px; border-radius: 20px; font-weight: 700; font-size: 0.9em;">Nổi bật</span>
                        @else
                            <span style="color: #9ca3af;">-</span>
                        @endif
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        @if($product->status)
                            <span style="background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 6px 12px; border-radius: 20px; font-weight: 700; font-size: 0.9em;">Hiện</span>
                                @else
                            <span style="background: linear-gradient(135deg, #6b7280, #4b5563); color: white; padding: 6px 12px; border-radius: 20px; font-weight: 700; font-size: 0.9em;">Ẩn</span>
                                @endif
                            </td>
                    <td style="padding: 15px; text-align: center; font-size: 0.85em; color: #4b5563; vertical-align: middle;">
                        <div style="line-height: 1.45; white-space: nowrap;">{{ $product->created_at ? $product->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') : '—' }}</div>
                        @if($product->updated_at)
                            <button
                                type="button"
                                class="btn btn-link p-0 product-activity-hist product-activity-hist--date"
                                style="line-height: 1.45; font-size: inherit; color: #2563eb; text-decoration: underline; text-underline-offset: 2px; white-space: nowrap;"
                                data-url="{{ route('admin.products.activity-history', $product) }}"
                                title="Xem lịch sử thay đổi (tên, hãng, giá…)"
                            >{{ $product->updated_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i') }}</button>
                        @else
                            <div style="line-height: 1.45; white-space: nowrap;">—</div>
                        @endif
                    </td>
                    <td style="padding: 15px; text-align: center;">
                        <div class="dropdown">
                            <button
                                class="btn btn-link text-white p-1 rounded-2"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                title="Thao tác"
                                style="background: rgba(15,23,42,0.08); border-radius: 999px; width: 36px; height: 36px; display:inline-flex; align-items:center; justify-content:center;"
                            >
                                <i class="bi bi-three-dots-vertical fs-5 lh-1"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 small">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.products.edit', $product->id) . '?return_url=' . urlencode(request()->fullUrl()) }}">
                                        <i class="bi bi-pencil-square me-2 text-primary"></i>Sửa
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-trash me-2"></i>Xóa
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
    
    <!-- Pagination -->
    <div style="padding: 25px 30px; border-top: 1px solid #e5e7eb; display: flex; justify-content: center;">
        {{ $products->appends(request()->query())->links('pagination::bootstrap-4') }}
    </div>
</div>

<style>
/* Custom pagination styles */
.pagination {
    gap: 5px;
}

.page-link {
    border-radius: 8px !important;
    border: none !important;
    padding: 10px 15px !important;
    font-weight: 600 !important;
    color: #374151 !important;
    background: #f8fafc !important;
    transition: all 0.3s ease !important;
}

.page-link:hover {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8) !important;
    color: white !important;
    transform: translateY(-2px) !important;
}

.page-item.active .page-link {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8) !important;
    color: white !important;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3) !important;
}

/* Hover effects for buttons */
a:hover, button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

button.product-activity-hist:hover {
    transform: none !important;
    box-shadow: none !important;
}
button.product-activity-hist--date:hover {
    color: #1d4ed8 !important;
}
button.product-activity-hist--price:hover {
    color: #047857 !important;
}
</style>

<div class="modal fade" id="productActivityModal" tabindex="-1" aria-labelledby="productActivityModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="productActivityModalTitle">Lịch sử cập nhật</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body pt-2" id="productActivityModalBody">
                <div class="text-muted">Đang tải…</div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('productActivityModal');
    var bodyEl = document.getElementById('productActivityModalBody');
    var titleEl = document.getElementById('productActivityModalTitle');
    if (!modalEl || !bodyEl || typeof bootstrap === 'undefined') return;
    var modal = new bootstrap.Modal(modalEl);

    function escapeHtml(s) {
        if (s == null || s === '') return '';
        var d = document.createElement('div');
        d.textContent = String(s);
        return d.innerHTML;
    }

    document.querySelectorAll('.product-activity-hist').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var url = btn.getAttribute('data-url');
            bodyEl.innerHTML = '<div class="text-muted py-3">Đang tải…</div>';
            titleEl.textContent = 'Lịch sử cập nhật sản phẩm';
            modal.show();

            fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (!data.ok) {
                        bodyEl.innerHTML = '<div class="alert alert-warning mb-0">Không tải được lịch sử.</div>';
                        return;
                    }
                    titleEl.textContent = 'Lịch sử: ' + (data.product_name || 'Sản phẩm');
                    var items = data.items || [];
                    if (items.length === 0) {
                        bodyEl.innerHTML = '<p class="text-muted mb-0 small">Chưa có nhật ký. Các lần chỉnh sửa trước khi hệ thống ghi log có thể không hiển thị ở đây.</p>';
                        return;
                    }
                    var html = '';
                    items.forEach(function (ev) {
                        html += '<div class="border rounded-3 p-3 mb-3" style="background:#f8fafc;">';
                        html += '<div class="d-flex flex-wrap justify-content-between gap-2 mb-2 align-items-center">';
                        html += '<div><span class="fw-bold" style="color:#1d4ed8;">' + escapeHtml(ev.at) + '</span> ';
                        if (ev.action === 'product.create') {
                            html += '<span class="badge bg-success">Tạo mới</span>';
                        } else {
                            html += '<span class="badge bg-secondary">Cập nhật</span>';
                        }
                        html += '</div>';
                        if (ev.user_email) {
                            html += '<div class="small text-muted">' + escapeHtml(ev.user_email) + '</div>';
                        }
                        html += '</div>';
                        var ch = ev.changes || [];
                        if (ch.length === 0) {
                            html += '<div class="small text-muted">' + escapeHtml(ev.description || '') + '</div>';
                        } else {
                            html += '<div class="table-responsive"><table class="table table-sm table-bordered mb-0 bg-white small">';
                            html += '<thead class="table-light"><tr><th>Trường</th><th>Trước</th><th>Sau</th></tr></thead><tbody>';
                            ch.forEach(function (row) {
                                html += '<tr><td>' + escapeHtml(row.label) + '</td><td>' + escapeHtml(row.from) + '</td><td>' + escapeHtml(row.to) + '</td></tr>';
                            });
                            html += '</tbody></table></div>';
                        }
                        html += '</div>';
                    });
                    bodyEl.innerHTML = html;
                })
                .catch(function () {
                    bodyEl.innerHTML = '<div class="alert alert-danger mb-0">Lỗi tải dữ liệu.</div>';
                });
        });
    });
});
</script>
@endsection 