@extends('layouts.admin')

@section('title', 'Sửa danh mục')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h2 class="mb-0">Sửa danh mục</h2>
    </div>
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label fw-bold">Tên danh mục <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required value="{{ old('name', $category->name) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Danh mục cha</label>
                    <select name="parent_id" class="form-select">
                        <option value="">-- Không chọn --</option>
                        @php
                        function renderCategoryOptionsEdit($categories, $parentId = null, $prefix = '', $currentId = null, $selectedId = null) {
                            foreach($categories->where('parent_id', $parentId) as $cat) {
                                if($cat->id == $currentId) continue;
                                echo '<option value="'.$cat->id.'"'.(($selectedId == $cat->id) ? ' selected' : '').'>'.$prefix.$cat->name.'</option>';
                                renderCategoryOptionsEdit($categories, $cat->id, $prefix.'--- ', $currentId, $selectedId);
                            }
                        }
                        renderCategoryOptionsEdit($parents, null, '', $category->id, old('parent_id', $category->parent_id));
                        @endphp
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Thứ tự ưu tiên</label>
                    <input type="number" name="sort_order" class="form-control" min="0" value="{{ old('sort_order', $category->sort_order ?? 0) }}">
                    <small class="text-muted">Số nhỏ hơn sẽ hiển thị trước.</small>
                </div>

                {{-- Banner cho trang chủ --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Banner 1 (Trang chủ)</label>
                        @if($category->banner_image_1)
                            <input type="hidden" name="remove_banner_image_1" value="0" id="remove_banner_image_1">
                            <div class="mb-2 position-relative d-inline-block" id="banner_image_1_preview">
                                <img src="{{ asset('images/categories/' . $category->banner_image_1) }}" alt="Banner 1" style="max-height:120px; border-radius:8px;">
                                <button type="button" class="btn btn-sm btn-danger position-absolute" data-remove-preview="#banner_image_1_preview" data-remove-input="#remove_banner_image_1" style="top:-8px; right:-8px; width:24px; height:24px; padding:0; border-radius:999px; line-height:1;">×</button>
                            </div>
                        @endif
                        <input type="file" name="banner_image_1" class="form-control" accept="image/*" data-reset-remove="#remove_banner_image_1">
                        <small class="text-muted">Kích thước đề xuất: 300x400px</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Banner 2 (Trang chủ)</label>
                        @if($category->banner_image_2)
                            <input type="hidden" name="remove_banner_image_2" value="0" id="remove_banner_image_2">
                            <div class="mb-2 position-relative d-inline-block" id="banner_image_2_preview">
                                <img src="{{ asset('images/categories/' . $category->banner_image_2) }}" alt="Banner 2" style="max-height:120px; border-radius:8px;">
                                <button type="button" class="btn btn-sm btn-danger position-absolute" data-remove-preview="#banner_image_2_preview" data-remove-input="#remove_banner_image_2" style="top:-8px; right:-8px; width:24px; height:24px; padding:0; border-radius:999px; line-height:1;">×</button>
                            </div>
                        @endif
                        <input type="file" name="banner_image_2" class="form-control" accept="image/*" data-reset-remove="#remove_banner_image_2">
                        <small class="text-muted">Kích thước đề xuất: 300x400px</small>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Banner promo (Trang chi tiết sản phẩm)</label>
                    @if(!empty($category->promo_banner))
                        <input type="hidden" name="remove_promo_banner" value="0" id="remove_promo_banner">
                        <div class="mb-2 position-relative d-inline-block" id="promo_banner_preview">
                            <img src="{{ asset('images/banners/' . $category->promo_banner) }}" alt="Promo banner" style="max-height:120px; border-radius:8px;">
                            <button type="button" class="btn btn-sm btn-danger position-absolute" data-remove-preview="#promo_banner_preview" data-remove-input="#remove_promo_banner" style="top:-8px; right:-8px; width:24px; height:24px; padding:0; border-radius:999px; line-height:1;">×</button>
                        </div>
                    @endif
                    <input type="file" name="promo_banner" class="form-control" accept="image/*" data-reset-remove="#remove_promo_banner">
                    <small class="text-muted">Kích thước đề xuất: ngang (vd 800x300px)</small>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        document.querySelectorAll('[data-remove-preview][data-remove-input]').forEach(function (btn) {
                            btn.addEventListener('click', function () {
                                const previewSel = btn.getAttribute('data-remove-preview');
                                const inputSel = btn.getAttribute('data-remove-input');
                                const previewEl = previewSel ? document.querySelector(previewSel) : null;
                                const inputEl = inputSel ? document.querySelector(inputSel) : null;
                                if (inputEl) inputEl.value = '1';
                                if (previewEl) previewEl.style.display = 'none';
                            });
                        });

                        document.querySelectorAll('input[type="file"][data-reset-remove]').forEach(function (fileInput) {
                            fileInput.addEventListener('change', function () {
                                const inputSel = fileInput.getAttribute('data-reset-remove');
                                const inputEl = inputSel ? document.querySelector(inputSel) : null;
                                if (inputEl && fileInput.files && fileInput.files.length) {
                                    inputEl.value = '0';
                                }
                            });
                        });
                    });
                </script>

                <div class="text-end">
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Quay lại</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 