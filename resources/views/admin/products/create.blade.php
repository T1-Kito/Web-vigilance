@extends('layouts.admin')

@section('title', 'Thêm sản phẩm mới')

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
        .product-form-misa .section-note {
            color: #64748b;
            font-size: .82rem;
            margin-top: .2rem;
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
            grid-template-columns: 160px 1fr;
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
            <div class="step-chip mb-1"><i class="bi bi-grid"></i> Quy trình nhập sản phẩm</div>
            <h2 class="mb-0">Thêm sản phẩm mới</h2>
        </div>
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
            <div class="card-header bg-white fw-bold">
                1) Thông tin chung
                <div class="section-note">Nhập mã/seri, tên sản phẩm, danh mục và hãng trước.</div>
            </div>
            <div class="card-body">
                <div class="row g-3 misa-form-grid">
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
            <div class="card-header bg-white fw-bold">
                2) Thiết lập giá bán
                <div class="section-note">Chỉ cần nhập Giá vốn hoặc Giá niêm yết, hệ thống tự tính phần còn lại.</div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Giá bán niêm yết <span class="text-danger">*</span></label>
                        <input type="text" inputmode="numeric" id="price" name="price" class="form-control money-input @error('price') is-invalid @enderror" value="{{ old('price', 0) }}" placeholder="Nhập giá bán hoặc giá vốn">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Giá vốn</label>
                        <input type="text" inputmode="numeric" id="cost_price" name="cost_price" class="form-control money-input" value="{{ old('cost_price') }}" placeholder="Nhập giá vốn để tự tính">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Giảm giá (%)</label>
                        <input type="number" name="discount_percent" class="form-control" value="{{ old('discount_percent') }}" min="0" max="100">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Thuế GTGT (%)</label>
                        <input type="number" step="0.01" name="vat_percent" class="form-control" value="{{ old('vat_percent', 0) }}" min="0" max="100">
                    </div>

                    <div class="col-md-4"><label class="form-label">Đơn giá nhà máy (auto)</label><input type="text" inputmode="numeric" id="factory_price" name="factory_price" class="form-control money-input" value="{{ old('factory_price') }}" readonly></div>
                    <div class="col-md-4"><label class="form-label">Giá bán cho Đại lý 1-5 (auto)</label><input type="text" inputmode="numeric" id="agency_price" name="agency_price" class="form-control money-input" value="{{ old('agency_price') }}" readonly></div>
                    <div class="col-md-4"><label class="form-label">Giá bán cho Khách lẻ (auto)</label><input type="text" inputmode="numeric" id="retail_price" name="retail_price" class="form-control money-input" value="{{ old('retail_price') }}" readonly></div>
                    <div class="col-12">
                        <div class="alert alert-info py-2 mb-0 small">
                            Công thức hiện tại: Giá niêm yết = Giá vốn x{{ number_format((float) ($pricingSetting->list_multiplier ?? 2), 2, '.', '') }}, Khách lẻ = Giá niêm yết -{{ number_format((float) ($pricingSetting->retail_discount_percent ?? 15), 2, '.', '') }}%, Đại lý: 1-5 (+{{ number_format((float) ($pricingSetting->agent_markup_1_5_percent ?? 30), 2, '.', '') }}%), 6-10 (+{{ number_format((float) ($pricingSetting->agent_markup_6_10_percent ?? 25), 2, '.', '') }}%), >10 (+{{ number_format((float) ($pricingSetting->agent_markup_over_10_percent ?? 15), 2, '.', '') }}%).
                            <a href="{{ route('admin.pricing-formula.edit') }}" class="ms-1">Sửa công thức</a>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">
                3) Quy tắc áp giá tự động
                <div class="section-note">Admin chỉ cần kiểm tra quy tắc, hệ thống tự tạo bảng giá khi lưu.</div>
            </div>
            <div class="card-body">
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
                <div class="form-text mt-2">Khi lưu sản phẩm, hệ thống sẽ tự sinh và đồng bộ bảng giá theo công thức này.</div>
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
            <div class="card-header bg-white fw-bold">
                4) Ảnh & nội dung hiển thị
                <div class="section-note">Nhập mô tả ngắn gọn, rõ thông số để sale dễ tư vấn.</div>
            </div>
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
            <div class="card-header bg-white fw-bold">
                5) Trạng thái hiển thị
                <div class="section-note">Bật/tắt hiển thị sản phẩm và gắn nhãn nổi bật nếu cần.</div>
            </div>
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
