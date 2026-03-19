@extends('layouts.admin')

@section('title', 'Thêm sản phẩm mới')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h2 class="mb-0">Thêm sản phẩm mới</h2>
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
            
            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-4">
                                                  <label class="form-label fw-bold">Số seri (SN)</label>
                        <input type="text" name="serial_number" class="form-control" value="{{ old('serial_number') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Tên sản phẩm <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required value="{{ old('name') }}">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Danh mục <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">-- Chọn danh mục --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @if(old('category_id') == $cat->id) selected @endif>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Hãng</label>
                        <input type="text" id="brand" name="brand" class="form-control" value="{{ old('brand') }}" list="brandOptions" placeholder="Chọn hoặc nhập hãng">
                        <datalist id="brandOptions">
                            <option value="ZKTeco"></option>
                            <option value="Dahua"></option>
                            <option value="Hikvision"></option>
                            <option value="KBVision"></option>
                            <option value="Imou"></option>
                            <option value="Ezviz"></option>
                            <option value="Jieshun"></option>
                            <option value="Vigilance"></option>
                            <option value="Hytera"></option>
                            <option value="Commax"></option>
                            <option value="RISCO"></option>
                            <option value="TYSSO"></option>
                        </datalist>

                        <div class="mt-2" style="display:flex; flex-wrap:wrap; gap:8px;">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setBrand('ZKTeco')">ZKTeco</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setBrand('Dahua')">Dahua</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setBrand('Hikvision')">Hikvision</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setBrand('KBVision')">KBVision</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setBrand('Imou')">Imou</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setBrand('Ezviz')">Ezviz</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setBrand('Jieshun')">Jieshun</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setBrand('Vigilance')">Vigilance</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setBrand('Hytera')">Hytera</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setBrand('Commax')">Commax</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setBrand('RISCO')">RISCO</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setBrand('TYSSO')">TYSSO</button>
                        </div>
                    </div>
                </div>
                <script>
                    function setBrand(brand) {
                        var input = document.getElementById('brand');
                        if (input) input.value = brand;
                    }
                </script>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Giá bán <span class="text-danger">*</span></label>
                        <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', 0) }}">
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Giảm giá (%)</label>
                        <input type="number" name="discount_percent" class="form-control" value="{{ old('discount_percent') }}" min="0" max="100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Thứ tự hiển thị <i class="bi bi-info-circle" data-bs-toggle="tooltip" title="Số càng nhỏ càng ưu tiên (1 = lên đầu tiên)"></i></label>
                        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', 999) }}" min="1">
                        <small class="text-muted">Số nhỏ = ưu tiên cao</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Ảnh chính sản phẩm</label>
                        <input type="file" name="image" class="form-control">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">Ảnh bổ sung (có thể chọn nhiều)</label>
                        <input type="file" name="additional_images[]" class="form-control" multiple accept="image/*">
                        <small class="text-muted">Có thể chọn nhiều ảnh cùng lúc. Ảnh đầu tiên sẽ là ảnh chính.</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Mô tả</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Thông tin sản phẩm</label>
                    <textarea name="information" class="form-control" rows="2">{{ old('information') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Thông số kỹ thuật</label>
                    <textarea name="specifications" class="form-control" rows="2">{{ old('specifications') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Hướng dẫn sử dụng</label>
                    <textarea name="instruction" class="form-control" rows="2">{{ old('instruction') }}</textarea>
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
                            <td><input type="text" name="colors[][color_name]" class="form-control" required></td>
                            <td><input type="color" name="colors[][color_code]" class="form-control form-control-color" value="#000000"></td>
                            <td><input type="number" name="colors[][price]" class="form-control" min="0"></td>
                            <td><input type="number" name="colors[][quantity]" class="form-control" min="0" value="0"></td>
                            <td><button type="button" class="btn btn-danger btn-sm remove-color-row"><i class="bi bi-x"></i></button></td>
                        `;
                        row.querySelector('.remove-color-row').onclick = function() {
                            row.remove();
                        };
                    };
                });
                </script>
                <div class="row mb-3">
                    <div class="col-md-6 d-flex align-items-center gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1" @if(old('is_featured')) checked @endif>
                            <label class="form-check-label" for="is_featured">Nổi bật</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="status" id="status" value="1" @if(old('status', 1)) checked @endif>
                            <label class="form-check-label" for="status">Hiển thị</label>
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Quay lại</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Lưu sản phẩm</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 