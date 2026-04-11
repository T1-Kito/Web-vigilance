@extends('layouts.admin')

@section('title', 'Sửa sản phẩm')

@section('content')
<div class="container-fluid py-4 product-form-misa">
    <style>
        .product-form-misa .misa-head {
            background: linear-gradient(180deg,#f8fafc 0%,#f1f5f9 100%);
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px 16px;
        }
        .product-form-misa .misa-head .step-chip {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            border-radius: 999px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: .78rem;
            font-weight: 600;
            padding: .2rem .55rem;
        }
        .product-form-misa .card {
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px;
            overflow: hidden;
        }
        .product-form-misa .card-header {
            background: #f8fafc !important;
            font-weight: 700;
            color: #0f172a;
        }
        .product-form-misa .section-title {
            font-size: .96rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: .2rem;
        }
        .product-form-misa .section-note {
            color: #64748b;
            font-size: .82rem;
            margin-bottom: .8rem;
        }
        .product-form-misa .misa-form-grid > .col-12 {
            flex: 0 0 100%;
            max-width: 100%;
            display: block;
        }
        .product-form-misa .misa-form-grid > [class*='col-md-'] {
            flex: 0 0 50%;
            max-width: 50%;
            display: grid;
            grid-template-columns: 170px 1fr;
            gap: .55rem;
            align-items: center;
        }
        .product-form-misa .misa-form-grid > [class*='col-md-'] .form-label {
            margin-bottom: 0;
            font-size: .87rem;
            color: #334155;
        }
        .product-form-misa .misa-form-grid > [class*='col-md-'] .form-check {
            grid-column: 2;
        }
        @media (max-width: 992px) {
            .product-form-misa .misa-form-grid > [class*='col-md-'] {
                flex: 0 0 100%;
                max-width: 100%;
                grid-template-columns: 1fr;
            }
            .product-form-misa .misa-form-grid > [class*='col-md-'] .form-check {
                grid-column: auto;
            }
            .product-form-misa .misa-form-grid > [class*='col-md-'] .form-label {
                margin-bottom: .25rem;
            }
        }
    </style>

    <div class="misa-head d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="step-chip mb-1"><i class="bi bi-grid"></i> Quy trình chỉnh sửa sản phẩm</div>
            <h2 class="mb-0">Sửa sản phẩm</h2>
        </div>
        <a href="{{ route('admin.products.index') }}" class="btn btn-light border">Quay lại</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill me-2"></i>Có lỗi xảy ra!</h5>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="return_url" value="{{ request('return_url') }}">
                <div class="section-title">1) Thông tin chung</div>
                <div class="section-note">Nhập mã/seri, tên sản phẩm, danh mục và hãng.</div>
                <div class="row g-3 misa-form-grid mb-3">
                    <div class="col-md-6">
                                                  <label class="form-label fw-bold">Số seri (SN)</label>
                        <input type="text" name="serial_number" class="form-control" value="{{ old('serial_number', $product->serial_number) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tên sản phẩm <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required value="{{ old('name', $product->name) }}">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Danh mục <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">-- Chọn danh mục --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @if(old('category_id', $product->category_id) == $cat->id) selected @endif>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row g-3 misa-form-grid mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Hãng</label>
                        @php
                            $brandOptions = ['ZKTeco','Dahua','Hikvision','KBVision','Imou','Ezviz','Jieshun','Vigilance','Hytera','Commax','RISCO','TYSSO'];
                            $oldBrand = (string) old('brand', $product->brand);
                            $isCustomBrand = $oldBrand !== '' && !in_array($oldBrand, $brandOptions, true);
                        @endphp
                        <select id="brand_select" class="form-select">
                            <option value="">-- Chọn hãng --</option>
                            @foreach($brandOptions as $b)
                                <option value="{{ $b }}" @selected($oldBrand === $b)>{{ $b }}</option>
                            @endforeach
                            <option value="__custom__" @selected($isCustomBrand)>Khác...</option>
                        </select>
                        <input type="hidden" id="brand" name="brand" value="{{ $oldBrand }}">
                        <input type="text" id="brand_custom" class="form-control mt-2" placeholder="Nhập hãng khác..." value="{{ $isCustomBrand ? $oldBrand : '' }}" style="display: {{ $isCustomBrand ? 'block' : 'none' }};">
                    </div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const selectEl = document.getElementById('brand_select');
                        const hiddenEl = document.getElementById('brand');
                        const customEl = document.getElementById('brand_custom');
                        if (!selectEl || !hiddenEl || !customEl) return;

                        function syncBrand() {
                            if (selectEl.value === '__custom__') {
                                customEl.style.display = 'block';
                                hiddenEl.value = customEl.value || '';
                            } else {
                                customEl.style.display = 'none';
                                hiddenEl.value = selectEl.value || '';
                            }
                        }

                        selectEl.addEventListener('change', syncBrand);
                        customEl.addEventListener('input', function () {
                            if (selectEl.value === '__custom__') hiddenEl.value = customEl.value || '';
                        });
                        syncBrand();
                    });
                </script>
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white fw-bold">
                        2) Thiết lập giá bán
                        <div class="section-note">Nhập Giá vốn hoặc Giá niêm yết, hệ thống tự tính các giá liên quan.</div>
                    </div>
                    <div class="card-body">
                <div class="row g-3 misa-form-grid mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Giá bán niêm yết <span class="text-danger">*</span></label>
                        <input type="text" inputmode="numeric" id="price" name="price" class="form-control money-input @error('price') is-invalid @enderror" value="{{ old('price', $product->price ?? 0) }}" placeholder="Nhập giá bán hoặc giá vốn">
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Giá vốn</label>
                        <input type="text" inputmode="numeric" id="cost_price" name="cost_price" class="form-control money-input" value="{{ old('cost_price', $product->cost_price) }}" placeholder="Nhập giá vốn để tự tính">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Giảm giá (%)</label>
                        <input type="number" name="discount_percent" class="form-control" value="{{ old('discount_percent', $product->discount_percent) }}" min="0" max="100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Thuế GTGT (%)</label>
                        <input type="number" step="0.01" name="vat_percent" class="form-control" value="{{ old('vat_percent', $product->vat_percent ?? 0) }}" min="0" max="100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Thứ tự hiển thị</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $product->sort_order ?? 999) }}" min="1">
                        <small class="text-muted">Số nhỏ = ưu tiên</small>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3"><label class="form-label fw-bold">Đơn vị tính chính</label><input type="text" name="unit_name" class="form-control" value="{{ old('unit_name', $product->unit_name ?? 'Cái') }}"></div>
                    <div class="col-md-3 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="price_includes_tax" id="price_includes_tax" value="1" @checked(old('price_includes_tax', $product->price_includes_tax))>
                            <label class="form-check-label" for="price_includes_tax">Giá bán gồm thuế</label>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4"><label class="form-label fw-bold">Đơn giá nhà máy (auto)</label><input type="text" inputmode="numeric" id="factory_price" name="factory_price" class="form-control money-input" value="{{ old('factory_price', $product->factory_price) }}" readonly></div>
                    <div class="col-md-4"><label class="form-label fw-bold">Giá bán cho Đại lý 1-5 (auto)</label><input type="text" inputmode="numeric" id="agency_price" name="agency_price" class="form-control money-input" value="{{ old('agency_price', $product->agency_price) }}" readonly></div>
                    <div class="col-md-4"><label class="form-label fw-bold">Giá bán cho Khách lẻ (auto)</label><input type="text" inputmode="numeric" id="retail_price" name="retail_price" class="form-control money-input" value="{{ old('retail_price', $product->retail_price) }}" readonly></div>
                    <div class="col-12 mt-2">
                        <div class="alert alert-info py-2 mb-0 small">
                            Công thức hiện tại: Giá niêm yết = Giá vốn x{{ number_format((float) ($pricingSetting->list_multiplier ?? 2), 2, '.', '') }}, Khách lẻ = Giá niêm yết -{{ number_format((float) ($pricingSetting->retail_discount_percent ?? 15), 2, '.', '') }}%, Đại lý: 1-5 (+{{ number_format((float) ($pricingSetting->agent_markup_1_5_percent ?? 30), 2, '.', '') }}%), 6-10 (+{{ number_format((float) ($pricingSetting->agent_markup_6_10_percent ?? 25), 2, '.', '') }}%), >10 (+{{ number_format((float) ($pricingSetting->agent_markup_over_10_percent ?? 15), 2, '.', '') }}%).
                            <a href="{{ route('admin.pricing-formula.edit') }}" class="ms-1">Sửa công thức</a>
                        </div>
                    </div>
                </div>

                </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white fw-bold">
                        3) Quy tắc áp giá tự động
                        <div class="section-note">Bảng giá này được sinh tự động khi cập nhật sản phẩm.</div>
                    </div>
                    <div class="card-body">
                <div class="mb-4">
                    <label class="form-label fw-bold">Bảng giá tự động theo công thức</label>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                <div class="fw-semibold mb-2">Khách lẻ</div>
                                <div class="small text-muted">Áp dụng cho mọi số lượng</div>
                                <div class="mt-2"><span class="badge text-bg-primary">Giá niêm yết - 15%</span></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                <div class="fw-semibold mb-2">Đại lý (theo số lượng)</div>
                                <ul class="small mb-0 ps-3">
                                    <li>1-5 cái: Giá vốn + {{ number_format((float) ($pricingSetting->agent_markup_1_5_percent ?? 30), 2, '.', '') }}%</li>
                                    <li>6-10 cái: Giá vốn + {{ number_format((float) ($pricingSetting->agent_markup_6_10_percent ?? 25), 2, '.', '') }}%</li>
                                    <li>&gt;10 cái: Giá vốn + {{ number_format((float) ($pricingSetting->agent_markup_over_10_percent ?? 15), 2, '.', '') }}%</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="form-text mt-2">Khi cập nhật sản phẩm, hệ thống sẽ tự sinh và đồng bộ bảng giá theo công thức này.</div>
                </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white fw-bold">
                        4) Bảo hành & thuộc tính hiển thị
                        <div class="section-note">Nhập thông tin bảo hành, kích thước và trạng thái hiển thị.</div>
                    </div>
                    <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3"><label class="form-label fw-bold">Thời hạn bảo hành (tháng)</label><input type="number" name="warranty_months" class="form-control" value="{{ old('warranty_months', $product->warranty_months ?? 12) }}" min="0"></div>
                    <div class="col-md-9"><label class="form-label fw-bold">Nội dung bảo hành</label><input type="text" name="warranty_content" class="form-control" value="{{ old('warranty_content', $product->warranty_content) }}"></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-2"><label class="form-label fw-bold">Chiều cao</label><input type="number" step="0.01" name="height" class="form-control" value="{{ old('height', $product->height ?? 0) }}"></div>
                    <div class="col-md-2"><label class="form-label fw-bold">Chiều dài</label><input type="number" step="0.01" name="length" class="form-control" value="{{ old('length', $product->length ?? 0) }}"></div>
                    <div class="col-md-2"><label class="form-label fw-bold">Chiều rộng</label><input type="number" step="0.01" name="width" class="form-control" value="{{ old('width', $product->width ?? 0) }}"></div>
                    <div class="col-md-2"><label class="form-label fw-bold">Bán kính</label><input type="number" step="0.01" name="radius" class="form-control" value="{{ old('radius', $product->radius ?? 0) }}"></div>
                    <div class="col-md-2"><label class="form-label fw-bold">Trọng lượng</label><input type="number" step="0.01" name="weight" class="form-control" value="{{ old('weight', $product->weight ?? 0) }}"></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4 d-flex align-items-center gap-3 mt-1">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1" @if(old('is_featured', $product->is_featured)) checked @endif>
                            <label class="form-check-label" for="is_featured">Nổi bật</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="status" id="status" value="1" @if(old('status', $product->status)) checked @endif>
                            <label class="form-check-label" for="status">Hiển thị</label>
                        </div>
                    </div>
                </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white fw-bold">
                        5) Ảnh & nội dung sản phẩm
                        <div class="section-note">Quản lý mô tả, ảnh hiện tại và gallery bổ sung.</div>
                    </div>
                    <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Mô tả ngắn</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $product->description) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Thông tin sản phẩm</label>
                    <textarea name="information" class="form-control" rows="2">{{ old('information', $product->information) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Hướng dẫn sử dụng</label>
                    <textarea name="instruction" class="form-control" rows="2">{{ old('instruction', $product->instruction) }}</textarea>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ảnh chính sản phẩm</label>
                        @if($product->image)
                            <div class="mb-2">
                                <img src="{{ asset('images/products/' . $product->image) }}" alt="Ảnh hiện tại" style="max-width:200px; max-height:150px; object-fit:cover;" class="border rounded">
                            </div>
                        @endif
                        <input type="file" name="image" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ảnh bổ sung (có thể chọn nhiều)</label>
                        @if($product->images && $product->images->count() > 0)
                            <div class="mb-2">
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($product->images as $image)
                                        <div class="position-relative" style="display:inline-block;">
                                            <img src="{{ asset('images/products/' . $image->image_path) }}" alt="Ảnh bổ sung" style="width:80px; height:60px; object-fit:cover;" class="border rounded">
                                            <button type="button" class="btn btn-danger btn-sm position-absolute" style="top:-8px; right:-8px; width:20px; height:20px; border-radius:50%; padding:0; font-size:10px; line-height:1; z-index:10;" onclick="deleteImage({{ $image->id }}, this)">
                                                ×
                                            </button>
                                            <small class="d-block text-center mt-1">{{ $image->alt_text }}</small>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <input type="file" name="additional_images[]" class="form-control" multiple accept="image/*">
                        <small class="text-muted">Có thể chọn nhiều ảnh cùng lúc để thêm vào gallery.</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Thông số kỹ thuật</label>
                    <textarea name="specifications" class="form-control" rows="2">{{ old('specifications', $product->specifications) }}</textarea>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-bold">Màu sắc sản phẩm</label>
                    <table class="table table-bordered align-middle" id="colors-table">
                        <thead>
                            <tr>
                                <th style="width:22%">Tên màu</th>
                                <th style="width:18%">Mã màu</th>
                                <th style="width:22%">Giá riêng (nếu có)</th>
                                <th style="width:18%">Tồn kho</th>
                                <th style="width:10%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->colors as $color)
                                <tr>
                                    <td><input type="text" name="colors[{{ $color->id }}][color_name]" class="form-control" value="{{ $color->color_name }}" required></td>
                                    <td><input type="color" name="colors[{{ $color->id }}][color_code]" class="form-control form-control-color" value="{{ $color->color_code ?? '#000000' }}"></td>
                                    <td><input type="text" inputmode="numeric" name="colors[{{ $color->id }}][price]" class="form-control money-input" min="0" value="{{ $color->price }}"></td>
                                    <td><input type="number" name="colors[{{ $color->id }}][quantity]" class="form-control" min="0" value="{{ $color->quantity }}"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-color-row"><i class="bi bi-x"></i></button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-outline-primary" id="add-color-row"><i class="bi bi-plus-circle"></i> Thêm màu</button>
                </div>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const table = document.getElementById('colors-table').getElementsByTagName('tbody')[0];
                    document.getElementById('add-color-row').onclick = function() {
                        const row = table.insertRow();
                        row.innerHTML = `
                            <td><input type="text" name="colors[new][color_name][]" class="form-control" required></td>
                            <td><input type="color" name="colors[new][color_code][]" class="form-control form-control-color" value="#000000"></td>
                            <td><input type="text" inputmode="numeric" name="colors[new][price][]" class="form-control money-input" min="0"></td>
                            <td><input type="number" name="colors[new][quantity][]" class="form-control" min="0" value="0"></td>
                            <td><button type="button" class="btn btn-danger btn-sm remove-color-row"><i class="bi bi-x"></i></button></td>
                        `;
                        row.querySelector('.remove-color-row').onclick = function() {
                            row.remove();
                        };
                    };
                    // Gán sự kiện xóa cho các dòng có sẵn
                    table.querySelectorAll('.remove-color-row').forEach(btn => {
                        btn.onclick = function() {
                            btn.closest('tr').remove();
                        };
                    });
                });
                </script>
                <div class="mb-4">
                    <label class="form-label fw-bold">Sản phẩm mua kèm (phụ kiện, combo...)</label>
                    <table class="table table-bordered align-middle" id="addons-table">
                        <thead>
                            <tr>
                                <th style="width:30%">Chọn sản phẩm</th>
                                <th style="width:18%">Giá ưu đãi</th>
                                <th style="width:18%">% Giảm</th>
                                <th style="width:22%">Mô tả</th>
                                <th style="width:10%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->addonsWithProduct as $addon)
                                <tr>
                                    <td>
                                        <select name="addons[{{ $addon->id }}][addon_product_id]" class="form-select" required>
                                            <option value="">-- Chọn sản phẩm --</option>
                                            @foreach(\App\Models\Product::where('id', '!=', $product->id)->get() as $p)
                                                <option value="{{ $p->id }}" @if($addon->addon_product_id == $p->id) selected @endif>{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" name="addons[{{ $addon->id }}][addon_price]" class="form-control" min="0" value="{{ $addon->addon_price }}"></td>
                                    <td><input type="number" name="addons[{{ $addon->id }}][discount_percent]" class="form-control" min="0" max="100" value="{{ $addon->discount_percent }}"></td>
                                    <td><input type="text" name="addons[{{ $addon->id }}][description]" class="form-control" value="{{ $addon->description }}"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-addon-row"><i class="bi bi-x"></i></button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-outline-primary" id="add-addon-row"><i class="bi bi-plus-circle"></i> Thêm sản phẩm mua kèm</button>
                </div>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Addons
                    const addonsTable = document.getElementById('addons-table').getElementsByTagName('tbody')[0];
                    document.getElementById('add-addon-row').onclick = function() {
                        const row = addonsTable.insertRow();
                        row.innerHTML = `
                            <td>
                                <select name="addons[new][addon_product_id][]" class="form-select" required>
                                    <option value="">-- Chọn sản phẩm --</option>
                                    @foreach(\App\Models\Product::where('id', '!=', $product->id)->get() as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" name="addons[new][addon_price][]" class="form-control" min="0"></td>
                            <td><input type="number" name="addons[new][discount_percent][]" class="form-control" min="0" max="100"></td>
                            <td><input type="text" name="addons[new][description][]" class="form-control"></td>
                            <td><button type="button" class="btn btn-danger btn-sm remove-addon-row"><i class="bi bi-x"></i></button></td>
                        `;
                        row.querySelector('.remove-addon-row').onclick = function() {
                            row.remove();
                        };
                    };
                    // Gán sự kiện xóa cho các dòng có sẵn
                    addonsTable.querySelectorAll('.remove-addon-row').forEach(btn => {
                        btn.onclick = function() {
                            btn.closest('tr').remove();
                        };
                    });
                });
                </script>

                <script>
                function deleteImage(imageId, button) {
                    if (confirm('Bạn có chắc chắn muốn xóa ảnh này?')) {
                        const productId = {{ $product->id }};
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        
                        console.log('Đang xóa ảnh:', { imageId, productId, csrfToken });
                        
                        fetch(`/admin/products/${productId}/delete-image`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                image_id: imageId
                            })
                        })
                        .then(response => {
                            console.log('Response status:', response.status);
                            return response.json();
                        })
                        .then(data => {
                            console.log('Response data:', data);
                            if (data.success) {
                                // Xóa ảnh khỏi giao diện
                                button.closest('.position-relative').remove();
                                alert('Đã xóa ảnh thành công!');
                            } else {
                                alert('Có lỗi xảy ra: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Có lỗi xảy ra khi xóa ảnh! Vui lòng kiểm tra console để biết chi tiết.');
                        });
                    }
                }
                </script>

                <div class="text-end">
                    <a href="{{ request('return_url') ?: route('admin.products.index') }}" class="btn btn-secondary">Quay lại</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Cập nhật</button>
                </div>
            </form>

            <script>
            document.addEventListener('DOMContentLoaded', function () {
                function toMoneyInteger(value) {
                    const raw = (value || '').toString().trim();
                    if (!raw) return 0;

                    if (/^\d+(\.\d+)?$/.test(raw)) {
                        const n = Number(raw);
                        return Number.isFinite(n) ? Math.round(n) : 0;
                    }

                    const digits = raw.replace(/\D+/g, '');
                    return digits ? Number(digits) : 0;
                }

                function digitsOnly(value) {
                    return String(toMoneyInteger(value));
                }

                function formatThousands(value) {
                    const n = toMoneyInteger(value);
                    if (!n) return '';
                    return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                }

                function bindMoneyInput(el) {
                    if (!el) return;
                    el.addEventListener('input', function () {
                        const oldPos = el.selectionStart || el.value.length;
                        const oldLen = el.value.length;
                        el.value = formatThousands(el.value);
                        const diff = el.value.length - oldLen;
                        const newPos = Math.max(0, oldPos + diff);
                        try { el.setSelectionRange(newPos, newPos); } catch (e) {}
                    });
                    el.value = formatThousands(el.value);
                }

                document.querySelectorAll('.money-input').forEach(bindMoneyInput);

                const costInput = document.getElementById('cost_price');
                const listInput = document.getElementById('price');
                const factoryInput = document.getElementById('factory_price');
                const agencyInput = document.getElementById('agency_price');
                const retailInput = document.getElementById('retail_price');

                function parseMoneyInput(v) {
                    const n = Number((v || '').toString().replace(/\D+/g, ''));
                    return Number.isFinite(n) ? n : 0;
                }

                function setMoney(el, value) {
                    if (!el) return;
                    el.value = formatThousands(String(Math.max(0, Math.round(value || 0))));
                }

                const formula = {
                    listMultiplier: {{ (float) ($pricingSetting->list_multiplier ?? 2) }},
                    retailDiscountPercent: {{ (float) ($pricingSetting->retail_discount_percent ?? 15) }},
                    agentMarkup1To5Percent: {{ (float) ($pricingSetting->agent_markup_1_5_percent ?? 30) }},
                };

                function applyFromCost() {
                    const cost = parseMoneyInput(costInput?.value || '');
                    if (cost <= 0) return;
                    const list = cost * formula.listMultiplier;
                    const retail = list * (1 - formula.retailDiscountPercent / 100);
                    const agency = cost * (1 + formula.agentMarkup1To5Percent / 100);

                    setMoney(factoryInput, cost);
                    setMoney(listInput, list);
                    setMoney(retailInput, retail);
                    setMoney(agencyInput, agency);
                }

                function applyFromList() {
                    const list = parseMoneyInput(listInput?.value || '');
                    if (list <= 0) return;
                    const cost = list / Math.max(0.01, formula.listMultiplier);
                    const retail = list * (1 - formula.retailDiscountPercent / 100);
                    const agency = cost * (1 + formula.agentMarkup1To5Percent / 100);

                    setMoney(costInput, cost);
                    setMoney(factoryInput, cost);
                    setMoney(retailInput, retail);
                    setMoney(agencyInput, agency);
                    setMoney(listInput, list);
                }

                if (costInput) {
                    costInput.addEventListener('input', applyFromCost);
                }
                if (listInput) {
                    listInput.addEventListener('input', applyFromList);
                }

                if (parseMoneyInput(costInput?.value || '') > 0) {
                    applyFromCost();
                } else {
                    applyFromList();
                }


                const form = document.querySelector('form[action="{{ route('admin.products.update', $product->id) }}"]');
                if (form) {
                    form.addEventListener('submit', function () {
                        form.querySelectorAll('.money-input').forEach(function (el) {
                            el.value = digitsOnly(el.value);
                        });
                    });
                }
            });
            </script>
        </div>
    </div>
</div>
@endsection 