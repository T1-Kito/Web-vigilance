@extends('layouts.admin')

@section('title', 'Thêm danh mục mới')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h2 class="mb-0">Thêm danh mục mới</h2>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold">Tên danh mục <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Danh mục cha</label>
                    <select name="parent_id" class="form-select">
                        <option value="">-- Không chọn --</option>
                        @php
                        function renderCategoryOptions($categories, $parentId = null, $prefix = '') {
                            foreach($categories->where('parent_id', $parentId) as $cat) {
                                echo '<option value="'.$cat->id.'"'.(old('parent_id') == $cat->id ? ' selected' : '').'>'.$prefix.$cat->name.'</option>';
                                renderCategoryOptions($categories, $cat->id, $prefix.'--- ');
                            }
                        }
                        renderCategoryOptions($parents);
                        @endphp
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Thứ tự ưu tiên</label>
                    <input type="number" name="sort_order" class="form-control" min="0" value="{{ old('sort_order', 0) }}">
                    <small class="text-muted">Số nhỏ hơn sẽ hiển thị trước.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Banner promo (Trang chi tiết sản phẩm)</label>
                    <input type="file" name="promo_banner" class="form-control" accept="image/*">
                    <small class="text-muted">Kích thước đề xuất: ngang (vd 800x300px)</small>
                </div>
                <div class="text-end">
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Quay lại</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Lưu danh mục</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 