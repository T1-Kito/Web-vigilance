@extends('layouts.admin')

@section('title', 'Thêm sản phẩm mới')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="mb-0">Thêm sản phẩm mới</h2>
        <a href="{{ route('admin.products.index') }}" class="btn btn-light border">Quay lại</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <h6 class="mb-2"><i class="bi bi-exclamation-triangle-fill me-1"></i>Vui lòng kiểm tra lại thông tin</h6>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Thông tin chung</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Số seri (SN)</label>
                        <input type="text" name="serial_number" class="form-control" value="{{ old('serial_number') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required value="{{ old('name') }}">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">-- Chọn danh mục --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Hãng</label>
                        @php
                            $brandOptions = ['ZKTeco','Dahua','Hikvision','KBVision','Imou','Ezviz','Jieshun','Vigilance','Hytera','Commax','RISCO','TYSSO'];
                            $oldBrand = (string) old('brand', '');
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
                    <div class="col-md-4">
                        <label class="form-label">Đơn vị tính chính</label>
                        <input type="text" name="unit_name" class="form-control" value="{{ old('unit_name', 'Cái') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Thứ tự hiển thị</label>
                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 999) }}" min="1">
                    </div>

                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Thông tin giá</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Giá bán <span class="text-danger">*</span></label>
                        <input type="text" inputmode="numeric" name="price" class="form-control money-input @error('price') is-invalid @enderror" value="{{ old('price', 0) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Giá vốn</label>
                        <input type="text" inputmode="numeric" name="cost_price" class="form-control money-input" value="{{ old('cost_price') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Giảm giá (%)</label>
                        <input type="number" name="discount_percent" class="form-control" value="{{ old('discount_percent') }}" min="0" max="100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Thuế GTGT (%)</label>
                        <input type="number" step="0.01" name="vat_percent" class="form-control" value="{{ old('vat_percent', 0) }}" min="0" max="100">
                    </div>

                    <div class="col-md-3"><label class="form-label">Đơn giá nhà máy</label><input type="text" inputmode="numeric" name="factory_price" class="form-control money-input" value="{{ old('factory_price') }}"></div>
                    <div class="col-md-3"><label class="form-label">Giá đề nghị bán đại lý</label><input type="text" inputmode="numeric" name="agency_suggested_price" class="form-control money-input" value="{{ old('agency_suggested_price') }}"></div>
                    <div class="col-md-3"><label class="form-label">Giá bán cho Đại lý</label><input type="text" inputmode="numeric" name="agency_price" class="form-control money-input" value="{{ old('agency_price') }}"></div>
                    <div class="col-md-3"><label class="form-label">Giá bán cho Khách lẻ</label><input type="text" inputmode="numeric" name="retail_price" class="form-control money-input" value="{{ old('retail_price') }}"></div>

                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Bảo hành</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3"><label class="form-label">Thời hạn bảo hành (tháng)</label><input type="number" name="warranty_months" class="form-control" value="{{ old('warranty_months', 12) }}" min="0"></div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Ảnh & Nội dung</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Ảnh chính sản phẩm</label>
                        <input type="file" name="image" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ảnh bổ sung (nhiều ảnh)</label>
                        <input type="file" name="additional_images[]" class="form-control" multiple accept="image/*">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Mô tả</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Thông tin sản phẩm</label>
                        <textarea name="information" class="form-control" rows="2">{{ old('information') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Thông số kỹ thuật</label>
                        <textarea name="specifications" class="form-control" rows="2">{{ old('specifications') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Hướng dẫn sử dụng</label>
                        <textarea name="instruction" class="form-control" rows="2">{{ old('instruction') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Thuộc tính hiển thị</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 d-flex align-items-center gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1" @checked(old('is_featured'))>
                            <label class="form-check-label" for="is_featured">Nổi bật</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="status" id="status" value="1" @checked(old('status', 1))>
                            <label class="form-check-label" for="status">Hiển thị</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Màu sắc sản phẩm</div>
            <div class="card-body">
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
                    <tbody></tbody>
                </table>
                <button type="button" class="btn btn-outline-primary" id="add-color-row"><i class="bi bi-plus-circle"></i> Thêm màu</button>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mb-4">
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Hủy</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Lưu sản phẩm</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectEl = document.getElementById('brand_select');
    const hiddenEl = document.getElementById('brand');
    const customEl = document.getElementById('brand_custom');
    if (selectEl && hiddenEl && customEl) {
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
    }

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

    const table = document.getElementById('colors-table').getElementsByTagName('tbody')[0];
    document.getElementById('add-color-row').onclick = function() {
        const row = table.insertRow();
        row.innerHTML = `
            <td><input type="text" name="colors[][color_name]" class="form-control" required></td>
            <td><input type="color" name="colors[][color_code]" class="form-control form-control-color" value="#000000"></td>
            <td><input type="text" inputmode="numeric" name="colors[][price]" class="form-control money-input" min="0"></td>
            <td><input type="number" name="colors[][quantity]" class="form-control" min="0" value="0"></td>
            <td><button type="button" class="btn btn-danger btn-sm remove-color-row"><i class="bi bi-x"></i></button></td>
        `;
        row.querySelector('.remove-color-row').onclick = function() { row.remove(); };
        bindMoneyInput(row.querySelector('.money-input'));
    };

    const form = document.querySelector('form[action="{{ route('admin.products.store') }}"]');
    if (form) {
        form.addEventListener('submit', function () {
            form.querySelectorAll('.money-input').forEach(function (el) {
                el.value = digitsOnly(el.value);
            });
        });
    }
});
</script>
@endsection
