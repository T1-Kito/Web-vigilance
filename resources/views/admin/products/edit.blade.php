@extends('layouts.admin')

@section('title', 'Sửa sản phẩm')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h2 class="mb-0">Sửa sản phẩm</h2>
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
                <div class="row mb-3">
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
                <div class="row mb-3">
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
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Giá bán <span class="text-danger">*</span></label>
                        <input type="text" inputmode="numeric" name="price" class="form-control money-input @error('price') is-invalid @enderror" value="{{ old('price', $product->price ?? 0) }}">
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Giá vốn</label>
                        <input type="text" inputmode="numeric" name="cost_price" class="form-control money-input" value="{{ old('cost_price', $product->cost_price) }}">
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
                    <div class="col-md-3"><label class="form-label fw-bold">Đơn giá nhà máy</label><input type="text" inputmode="numeric" name="factory_price" class="form-control money-input" value="{{ old('factory_price', $product->factory_price) }}"></div>
                    <div class="col-md-3"><label class="form-label fw-bold">Giá đề nghị bán đại lý</label><input type="text" inputmode="numeric" name="agency_suggested_price" class="form-control money-input" value="{{ old('agency_suggested_price', $product->agency_suggested_price) }}"></div>
                    <div class="col-md-3"><label class="form-label fw-bold">Giá bán cho Đại lý</label><input type="text" inputmode="numeric" name="agency_price" class="form-control money-input" value="{{ old('agency_price', $product->agency_price) }}"></div>
                    <div class="col-md-3"><label class="form-label fw-bold">Giá bán cho Khách lẻ</label><input type="text" inputmode="numeric" name="retail_price" class="form-control money-input" value="{{ old('retail_price', $product->retail_price) }}"></div>
                </div>

                <div class="row mb-3">
                </div>

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
                function digitsOnly(value) {
                    return (value || '').toString().replace(/\D+/g, '');
                }

                function formatThousands(value) {
                    const digits = digitsOnly(value);
                    if (!digits) return '';
                    return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
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