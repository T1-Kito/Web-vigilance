@extends('layouts.admin')

@section('title', 'Sửa phiếu mượn hàng')

@section('content')
<style>
    .br-section-card {
        border-radius: 12px;
        border: 1px solid #dee2e6 !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    }
    .br-section-card .card-header {
        background: linear-gradient(180deg, #f8f9fc 0%, #f1f3f9 100%);
        border-bottom: 1px solid #e2e8f0;
        font-size: 0.95rem;
        color: #1e293b;
    }
    .br-product-suggest {
        display: none;
        position: fixed;
        z-index: 2050;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 14px 44px rgba(15, 23, 42, 0.14);
        max-height: 280px;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 4px;
        text-align: left;
    }
    .br-product-suggest__item {
        display: block;
        width: 100%;
        text-align: left;
        border: 0;
        background: transparent;
        padding: 9px 12px;
        border-radius: 7px;
        font-size: 0.875rem;
        color: #334155;
        line-height: 1.35;
        cursor: pointer;
        transition: background .12s ease;
    }
    .br-product-suggest__item:hover,
    .br-product-suggest__item:focus {
        background: #f1f5f9;
        outline: none;
    }
    .br-borrow-items { border-radius: 12px; overflow: hidden; }
    .br-borrow-items thead th {
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        color: #475569;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        border-color: #d8dee6 !important;
        white-space: nowrap;
        padding-top: 0.75rem;
        padding-bottom: 0.75rem;
    }
    .br-borrow-items tbody td {
        border-color: #e2e8f0 !important;
        vertical-align: middle;
        padding: 0.7rem 0.55rem;
    }
    .br-borrow-items .form-control.form-control-sm {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        min-height: 2.55rem;
        padding: 0.5rem 0.7rem;
        font-size: 0.92rem;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .br-borrow-items .form-control.form-control-sm:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.18);
    }
    .br-borrow-items .btn.remove-row {
        border-radius: 8px;
        width: 2.35rem;
        height: 2.35rem;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>
<div class="container-fluid py-4">
    <div class="mb-4" style="display:flex; align-items:center; justify-content:space-between; gap: 12px; flex-wrap: wrap;">
        <h2 class="mb-0">Sửa phiếu mượn hàng</h2>
        <div style="display:flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('admin.borrow-requests.show', $borrowRequest) }}" class="btn btn-outline-primary">Xem</a>
            <a href="{{ route('admin.borrow-requests.index') }}" class="btn btn-secondary">Quay lại</a>
        </div>
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

            <form id="borrowRequestForm" action="{{ route('admin.borrow-requests.update', $borrowRequest) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="card br-section-card h-100">
                            <div class="card-header fw-bold py-3">Thông tin khách hàng</div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Mã số thuế</label>
                                        <input type="text" id="taxCode" name="tax_code" class="form-control" value="{{ old('tax_code', $borrowRequest->tax_code) }}" maxlength="255" placeholder="Nhập MST — tự điền từ danh sách khách hàng">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Tên khách hàng</label>
                                        <input type="text" id="customerName" name="customer_name" class="form-control" value="{{ old('customer_name', $borrowRequest->customer_name) }}" maxlength="255" list="customerDatalist" autocomplete="off">
                                        <datalist id="customerDatalist"></datalist>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Người liên hệ</label>
                                        <input type="text" id="contactName" name="contact_name" class="form-control" value="{{ old('contact_name', $borrowRequest->contact_name) }}" maxlength="255">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Số điện thoại</label>
                                        <input type="text" id="contactPhone" name="contact_phone" class="form-control" value="{{ old('contact_phone', $borrowRequest->contact_phone) }}" maxlength="30">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Email</label>
                                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $borrowRequest->email) }}" maxlength="255">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Người đề nghị</label>
                                        <input type="text" name="requested_by_name" class="form-control" value="{{ old('requested_by_name', $borrowRequest->requested_by_name) }}" maxlength="255">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Bộ phận</label>
                                        @php $dep = old('department', $borrowRequest->department); @endphp
                                        <select name="department" class="form-select">
                                            <option value="" {{ $dep === null || $dep === '' ? 'selected' : '' }}></option>
                                            <option value="Kinh Doanh" {{ $dep === 'Kinh Doanh' ? 'selected' : '' }}>Kinh Doanh</option>
                                            <option value="Kho" {{ $dep === 'Kho' ? 'selected' : '' }}>Kho</option>
                                            <option value="PM" {{ $dep === 'PM' ? 'selected' : '' }}>PM</option>
                                            <option value="Kế toán" {{ $dep === 'Kế toán' ? 'selected' : '' }}>Kế toán</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card br-section-card h-100">
                            <div class="card-header fw-bold py-3">Thông tin mượn hàng</div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Mục đích</label>
                                        <input type="text" name="purpose" class="form-control" value="{{ old('purpose', $borrowRequest->purpose) }}" maxlength="255">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold">Công trình hiện tại</label>
                                        <input type="text" name="current_project" class="form-control" value="{{ old('current_project', $borrowRequest->current_project) }}" maxlength="255">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Thời gian mượn từ</label>
                                        <input type="date" name="borrow_from" class="form-control" value="{{ old('borrow_from', optional($borrowRequest->borrow_from)->format('Y-m-d')) }}" id="borrowFrom">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Đến</label>
                                        <input type="date" name="borrow_to" class="form-control" value="{{ old('borrow_to', optional($borrowRequest->borrow_to)->format('Y-m-d')) }}" id="borrowTo">
                                        <small class="text-muted">Mặc định +7 ngày nếu để trống</small>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Trạng thái</label>
                                        <select name="status" class="form-select" required>
                                            @foreach($statusOptions as $k => $label)
                                                <option value="{{ $k }}" {{ old('status', $borrowRequest->status)===$k ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Đặt cọc</label>
                                        <input type="hidden" name="deposit_text" value="Có cọc">
                                        <div class="text-muted" style="min-height: 38px; display:flex; align-items:center;">Bắt buộc</div>
                                    </div>

                                    @php
                                        $depAmount = old('deposit_amount', $borrowRequest->deposit_amount);
                                        $depAmountVal = $depAmount !== null && $depAmount !== '' ? rtrim(rtrim(number_format((float) $depAmount, 2, '.', ''), '0'), '.') : '';
                                    @endphp
                                    <div class="col-md-6" id="depositAmountWrap">
                                        <label class="form-label fw-bold">Số tiền cọc</label>
                                        <input type="text" inputmode="numeric" name="deposit_amount_display" class="form-control" value="" placeholder="Nhập số tiền" autocomplete="off" required>
                                        <input type="hidden" name="deposit_amount" value="{{ $depAmountVal }}">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Ký duyệt (tên)</label>
                                        <input type="text" name="approved_by_name" class="form-control" value="{{ old('approved_by_name', $borrowRequest->approved_by_name) }}" maxlength="255">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card br-section-card shadow-sm mt-4">
                    <div class="card-header fw-bold py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <span>Danh sách hàng mượn</span>
                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" id="addItemRow"><i class="bi bi-plus-circle"></i> Thêm dòng</button>
                    </div>
                    <div class="card-body p-3 p-md-4">
                        <div class="table-responsive mt-1">
                            <table class="table table-bordered table-sm align-middle mb-0 br-borrow-items" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th style="width:6%; text-align:center;">STT</th>
                                        <th style="width:30%;">Tên hàng</th>
                                        <th style="width:12%;">ĐVT</th>
                                        <th style="width:12%;">Số lượng</th>
                                        <th style="width:16%;">Giá trị</th>
                                        <th style="width:18%;">Ghi chú</th>
                                        <th style="width:6%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $oldItems = old('items'); @endphp
                                    @if(is_array($oldItems) && count($oldItems) > 0)
                                        @foreach($oldItems as $i => $it)
                                            <tr>
                                                <td class="text-center stt text-secondary small fw-semibold"></td>
                                                <td><input type="text" name="items[{{ $i }}][item_name]" class="form-control form-control-sm" value="{{ $it['item_name'] ?? '' }}" maxlength="255" placeholder="Tên sản phẩm"></td>
                                                <td><input type="text" name="items[{{ $i }}][unit]" class="form-control form-control-sm" value="{{ $it['unit'] ?? '' }}" maxlength="50"></td>
                                                <td><input type="number" step="0.01" name="items[{{ $i }}][quantity]" class="form-control form-control-sm" value="{{ $it['quantity'] ?? '' }}"></td>
                                                <td><input type="text" inputmode="numeric" name="items[{{ $i }}][value]" class="form-control form-control-sm text-end" value="{{ $it['value'] ?? '' }}" autocomplete="off" placeholder="0"></td>
                                                <td><input type="text" name="items[{{ $i }}][note]" class="form-control form-control-sm" value="{{ $it['note'] ?? '' }}" maxlength="255"></td>
                                                <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row" title="Xóa dòng"><i class="bi bi-x-lg"></i></button></td>
                                            </tr>
                                        @endforeach
                                    @else
                                        @foreach($borrowRequest->items as $i => $it)
                                            <tr>
                                                <td class="text-center stt text-secondary small fw-semibold"></td>
                                                <td><input type="text" name="items[{{ $i }}][item_name]" class="form-control form-control-sm" value="{{ $it->item_name }}" maxlength="255" placeholder="Tên sản phẩm"></td>
                                                <td><input type="text" name="items[{{ $i }}][unit]" class="form-control form-control-sm" value="{{ $it->unit }}" maxlength="50"></td>
                                                <td><input type="number" step="0.01" name="items[{{ $i }}][quantity]" class="form-control form-control-sm" value="{{ $it->quantity }}"></td>
                                                <td><input type="text" inputmode="numeric" name="items[{{ $i }}][value]" class="form-control form-control-sm text-end" value="{{ $it->value }}" autocomplete="off" placeholder="0"></td>
                                                <td><input type="text" name="items[{{ $i }}][note]" class="form-control form-control-sm" value="{{ $it->note }}" maxlength="255"></td>
                                                <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row" title="Xóa dòng"><i class="bi bi-x-lg"></i></button></td>
                                            </tr>
                                        @endforeach
                                        @if($borrowRequest->items->count() === 0)
                                            <tr>
                                                <td class="text-center stt text-secondary small fw-semibold"></td>
                                                <td><input type="text" name="items[0][item_name]" class="form-control form-control-sm" maxlength="255" placeholder="Tên sản phẩm"></td>
                                                <td><input type="text" name="items[0][unit]" class="form-control form-control-sm" maxlength="50"></td>
                                                <td><input type="number" step="0.01" name="items[0][quantity]" class="form-control form-control-sm"></td>
                                                <td><input type="text" inputmode="numeric" name="items[0][value]" class="form-control form-control-sm text-end" autocomplete="off" placeholder="0"></td>
                                                <td><input type="text" name="items[0][note]" class="form-control form-control-sm" maxlength="255"></td>
                                                <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row" title="Xóa dòng"><i class="bi bi-x-lg"></i></button></td>
                                            </tr>
                                        @endif
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i> Cập nhật</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('borrowRequestForm');
    const tableBody = document.querySelector('#itemsTable tbody');
    const addBtn = document.getElementById('addItemRow');
    const borrowFrom = document.getElementById('borrowFrom');
    const borrowTo = document.getElementById('borrowTo');
    const depositAmountWrap = document.getElementById('depositAmountWrap');

    const depositAmountDisplay = document.querySelector('input[name="deposit_amount_display"]');
    const depositAmountHidden = document.querySelector('input[name="deposit_amount"]');

    const customerName = document.getElementById('customerName');
    const customerDatalist = document.getElementById('customerDatalist');
    const contactName = document.getElementById('contactName');
    const contactPhone = document.getElementById('contactPhone');
    const taxCode = document.getElementById('taxCode');
    const email = document.getElementById('email');
    let customerCache = {};
    let lookupTimer = null;
    let taxLookupTimer = null;

    let productCache = {};
    let productLookupTimer = null;
    let productSuggestEl = null;
    let activeProductInput = null;

    const DEFAULT_PRODUCT_UNIT = 'Cái';

    function ensureProductSuggestEl() {
        if (productSuggestEl) return productSuggestEl;
        productSuggestEl = document.createElement('div');
        productSuggestEl.id = 'brProductSuggest';
        productSuggestEl.className = 'br-product-suggest';
        productSuggestEl.setAttribute('role', 'listbox');
        document.body.appendChild(productSuggestEl);
        return productSuggestEl;
    }

    function hideProductSuggest() {
        if (productSuggestEl) productSuggestEl.style.display = 'none';
        activeProductInput = null;
    }

    function positionProductSuggest(anchor) {
        const el = ensureProductSuggestEl();
        const r = anchor.getBoundingClientRect();
        const w = Math.max(r.width, 288);
        let left = r.left;
        if (left + w > window.innerWidth - 8) left = Math.max(8, window.innerWidth - w - 8);
        el.style.left = left + 'px';
        el.style.top = (r.bottom + 4) + 'px';
        el.style.width = w + 'px';
    }

    function showProductSuggest(anchor, rows) {
        const el = ensureProductSuggestEl();
        if (!rows || !rows.length) {
            hideProductSuggest();
            return;
        }
        positionProductSuggest(anchor);
        el.innerHTML = '';
        rows.forEach(function (p) {
            if (!p || !p.name) return;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'br-product-suggest__item';
            btn.setAttribute('role', 'option');
            btn.textContent = p.name;
            btn.addEventListener('mousedown', function (e) {
                e.preventDefault();
            });
            btn.addEventListener('click', function () {
                const tr = anchor.closest('tr');
                if (tr) applyProductFields(tr, p);
                hideProductSuggest();
                anchor.focus();
            });
            el.appendChild(btn);
        });
        el.style.display = 'block';
        activeProductInput = anchor;
    }

    function syncProductLookupRows(anchorInput, rows) {
        productCache = {};
        rows.forEach(cacheProductRow);
        if (anchorInput && document.activeElement === anchorInput) {
            showProductSuggest(anchorInput, rows);
        }
    }

    function repositionOpenProductSuggest() {
        const panel = productSuggestEl || document.getElementById('brProductSuggest');
        if (!panel || panel.style.display === 'none') return;
        const anchor = activeProductInput;
        if (!anchor || !document.body.contains(anchor)) {
            hideProductSuggest();
            return;
        }
        const r = anchor.getBoundingClientRect();
        if (r.bottom < -80 || r.top > window.innerHeight + 80) {
            hideProductSuggest();
            return;
        }
        positionProductSuggest(anchor);
    }

    window.addEventListener('scroll', repositionOpenProductSuggest, true);
    window.addEventListener('resize', repositionOpenProductSuggest);
    document.addEventListener('click', function (ev) {
        const panel = document.getElementById('brProductSuggest');
        if (!panel || panel.style.display === 'none') return;
        if (ev.target.closest && ev.target.closest('#brProductSuggest')) return;
        if (ev.target.matches && ev.target.matches('input[name^="items["][name$="][item_name]"]')) return;
        hideProductSuggest();
    });

    async function lookupProducts(q) {
        const url = `{{ route('admin.products.lookup') }}?q=${encodeURIComponent(q)}`;
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) return [];
        const data = await res.json();
        return Array.isArray(data) ? data : [];
    }

    function normalizeForMatch(s) {
        return String(s || '')
            .normalize('NFD')
            .replace(/\p{M}/gu, '')
            .toLowerCase()
            .replace(/[\s\-_]+/g, ' ')
            .trim();
    }

    function cacheProductRow(p) {
        if (!p || !p.name) return;
        productCache[String(p.name).trim().toLowerCase()] = p;
        productCache[normalizeForMatch(p.name)] = p;
    }

    function findProductInCache(raw) {
        const t = String(raw || '').trim();
        if (!t) return null;
        return productCache[t.toLowerCase()] || productCache[normalizeForMatch(t)] || null;
    }

    function pickProductFromRows(rows, typed) {
        if (!rows || !rows.length) return null;
        const nt = normalizeForMatch(typed);
        const exact = rows.find(function (r) { return normalizeForMatch(r.name) === nt; });
        if (exact) return exact;
        if (rows.length === 1) return rows[0];
        const narrowed = rows.filter(function (r) {
            const rn = normalizeForMatch(r.name);
            return rn.indexOf(nt) !== -1 || nt.indexOf(rn) !== -1;
        });
        if (narrowed.length === 1) return narrowed[0];
        return null;
    }

    function applyProductFields(tr, p) {
        if (!tr || !p) return;
        const nameInp = tr.querySelector('input[name^="items["][name$="][item_name]"]');
        const unitInp = tr.querySelector('input[name^="items["][name$="][unit]"]');
        const qtyInp = tr.querySelector('input[name^="items["][name$="][quantity]"]');
        const valInp = tr.querySelector('input[name^="items["][name$="][value]"]');
        if (nameInp && p.name) nameInp.value = p.name;
        if (unitInp) unitInp.value = DEFAULT_PRODUCT_UNIT;
        if (qtyInp) qtyInp.value = '1';
        if (valInp) {
            const v = p.final_price ?? p.price ?? '';
            valInp.value = formatVnNumber(String(v));
        }
    }

    async function resolveAndApplyProduct(inputEl) {
        if (!inputEl) return;
        const typed = String(inputEl.value || '').trim();
        if (typed.length < 2) return;
        const tr = inputEl.closest('tr');
        if (!tr) return;
        let p = findProductInCache(typed);
        if (!p) {
            const rows = await lookupProducts(typed);
            rows.forEach(cacheProductRow);
            p = pickProductFromRows(rows, typed) || findProductInCache(typed);
        }
        if (!p) return;
        applyProductFields(tr, p);
    }

    async function lookupCustomers(q) {
        const url = `{{ route('admin.customers.lookup') }}?q=${encodeURIComponent(q)}`;
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) return [];
        const data = await res.json();
        return Array.isArray(data) ? data : [];
    }

    function renderCustomerDatalist(rows) {
        if (!customerDatalist) return;
        customerDatalist.innerHTML = '';
        customerCache = {};
        rows.forEach((c) => {
            if (!c || !c.name) return;
            const key = String(c.name).trim().toLowerCase();
            customerCache[key] = c;
            const opt = document.createElement('option');
            opt.value = c.name;
            customerDatalist.appendChild(opt);
        });
    }

    function applyCustomerIfMatched() {
        if (!customerName) return;
        const key = String(customerName.value || '').trim().toLowerCase();
        const c = customerCache[key];
        if (!c) return;

        if (taxCode && (!taxCode.value || taxCode.value.trim() === '')) taxCode.value = c.tax_id || '';
        if (email && (!email.value || email.value.trim() === '')) email.value = c.email || '';
        if (contactPhone && (!contactPhone.value || contactPhone.value.trim() === '')) contactPhone.value = c.phone || '';
        if (contactName && (!contactName.value || contactName.value.trim() === '')) {
            contactName.value = (c.invoice_recipient || c.representative || '').trim();
        }
    }

    function normalizeTaxDigits(s) {
        return String(s || '').replace(/\D/g, '');
    }

    function isTaxCodeLikeQuery(q) {
        const compact = String(q || '').replace(/\s+/g, '');
        return /^[\d\-]{8,}$/.test(compact);
    }

    function pickCustomerRowForTax(rows, typedDigits) {
        if (!rows || !rows.length) return null;
        const exact = rows.find(function (r) {
            return normalizeTaxDigits(r.tax_id || '') === typedDigits;
        });
        return exact || rows[0];
    }

    function applyCustomerFromTaxLookup(c) {
        if (!c) return;
        if (customerName && c.name) customerName.value = c.name;
        if (taxCode && c.tax_id) taxCode.value = c.tax_id;
        if (email && c.email) email.value = c.email;
        if (contactPhone && c.phone) contactPhone.value = c.phone;
        const rep = (c.invoice_recipient || c.representative || '').trim();
        if (contactName && rep) contactName.value = rep;
    }

    if (taxCode) {
        taxCode.addEventListener('input', function () {
            const q = String(taxCode.value || '').trim();
            if (taxLookupTimer) clearTimeout(taxLookupTimer);
            if (!isTaxCodeLikeQuery(q)) return;
            taxLookupTimer = setTimeout(async function () {
                const rows = await lookupCustomers(q);
                const c = pickCustomerRowForTax(rows, normalizeTaxDigits(q));
                applyCustomerFromTaxLookup(c);
            }, 320);
        });
        taxCode.addEventListener('change', async function () {
            const q = String(taxCode.value || '').trim();
            if (!isTaxCodeLikeQuery(q)) return;
            const rows = await lookupCustomers(q);
            const c = pickCustomerRowForTax(rows, normalizeTaxDigits(q));
            applyCustomerFromTaxLookup(c);
        });
    }

    if (customerName) {
        customerName.addEventListener('input', function() {
            const q = String(customerName.value || '').trim();
            if (lookupTimer) clearTimeout(lookupTimer);
            if (q.length < 2) return;
            lookupTimer = setTimeout(async function() {
                const rows = await lookupCustomers(q);
                renderCustomerDatalist(rows);
            }, 250);
        });

        customerName.addEventListener('change', applyCustomerIfMatched);
        customerName.addEventListener('blur', applyCustomerIfMatched);
    }

    function normalizeNumberString(s) {
        return String(s || '').replace(/[^0-9]/g, '');
    }

    function formatVnNumber(s) {
        const raw = normalizeNumberString(s);
        if (raw === '') return '';
        const n = parseInt(raw, 10);
        if (Number.isNaN(n)) return '';
        return new Intl.NumberFormat('vi-VN').format(n);
    }

    function formatMoneyInput(inp) {
        if (!inp) return;
        const raw = normalizeNumberString(inp.value);
        inp.value = formatVnNumber(raw);
    }

    function syncDepositAmount() {
        if (!depositAmountDisplay || !depositAmountHidden) return;
        const raw = normalizeNumberString(depositAmountDisplay.value);
        depositAmountHidden.value = raw !== '' ? raw : '';
        depositAmountDisplay.value = formatVnNumber(raw);
    }

    if (depositAmountDisplay && depositAmountHidden) {
        if (depositAmountHidden.value) {
            depositAmountDisplay.value = formatVnNumber(depositAmountHidden.value);
        }

        depositAmountDisplay.addEventListener('input', function() {
            const raw = normalizeNumberString(depositAmountDisplay.value);
            depositAmountHidden.value = raw !== '' ? raw : '';
            depositAmountDisplay.value = formatVnNumber(raw);
        });

        depositAmountDisplay.addEventListener('blur', syncDepositAmount);
    }

    if (form) {
        form.addEventListener('submit', function() {
            syncDepositAmount();

            document.querySelectorAll('input[name^="items["][name$="][value]"]').forEach(function(inp) {
                inp.value = normalizeNumberString(inp.value);
            });
        });
    }

    if (tableBody) {
        tableBody.addEventListener('input', function(e) {
            const t = e.target;
            if (!t || !t.matches('input[name^="items["][name$="][item_name]"]')) return;
            const q = String(t.value || '').trim();
            if (productLookupTimer) clearTimeout(productLookupTimer);
            if (q.length < 2) {
                hideProductSuggest();
                return;
            }
            productLookupTimer = setTimeout(async function() {
                const rows = await lookupProducts(q);
                syncProductLookupRows(t, rows);
                const tr = t.closest('tr');
                if (tr && rows.length === 1) {
                    applyProductFields(tr, rows[0]);
                    hideProductSuggest();
                }
            }, 250);
        });

        tableBody.addEventListener('focusin', function(e) {
            const t = e.target;
            if (!t || !t.matches('input[name^="items["][name$="][item_name]"]')) return;
            const q = String(t.value || '').trim();
            if (q.length < 2) return;
            void (async function () {
                const rows = await lookupProducts(q);
                syncProductLookupRows(t, rows);
            })();
        }, true);

        tableBody.addEventListener('change', function(e) {
            const t = e.target;
            if (!t || !t.matches('input[name^="items["][name$="][item_name]"]')) return;
            void resolveAndApplyProduct(t);
        });

        tableBody.addEventListener('blur', function(e) {
            const t = e.target;
            if (!t || !t.matches('input[name^="items["][name$="][item_name]"]')) return;
            window.setTimeout(function () {
                const panel = document.getElementById('brProductSuggest');
                if (panel && panel.style.display !== 'none' && panel.matches(':focus-within')) return;
                hideProductSuggest();
            }, 120);
            void resolveAndApplyProduct(t);
        }, true);

        tableBody.querySelectorAll('input[name^="items["][name$="][value]"]').forEach(function(inp) {
            formatMoneyInput(inp);
        });

        tableBody.addEventListener('input', function(e) {
            const t = e.target;
            if (!t || !t.matches('input[name^="items["][name$="][value]"]')) return;
            const raw = normalizeNumberString(t.value);
            t.value = formatVnNumber(raw);
        });

        tableBody.addEventListener('blur', function(e) {
            const t = e.target;
            if (!t || !t.matches('input[name^="items["][name$="][value]"]')) return;
            formatMoneyInput(t);
        }, true);
    }

    function pad(n){ return String(n).padStart(2,'0'); }

    function addDays(date, days) {
        const d = new Date(date.getTime());
        d.setDate(d.getDate() + days);
        return d;
    }

    function toYmd(date) {
        return date.getFullYear() + '-' + pad(date.getMonth()+1) + '-' + pad(date.getDate());
    }

    function reindex() {
        const rows = Array.from(tableBody.querySelectorAll('tr'));
        rows.forEach((tr, idx) => {
            const stt = tr.querySelector('.stt');
            if (stt) stt.textContent = String(idx + 1);

            tr.querySelectorAll('input[name^="items["]').forEach(inp => {
                inp.name = inp.name.replace(/items\[\d+\]/, 'items[' + idx + ']');
            });
        });
    }

    function bindRemove() {
        tableBody.querySelectorAll('.remove-row').forEach(btn => {
            btn.onclick = function() {
                const tr = btn.closest('tr');
                if (tr) tr.remove();
                if (tableBody.querySelectorAll('tr').length === 0) {
                    const tr2 = document.createElement('tr');
                    tr2.innerHTML = `
                        <td class="text-center stt text-secondary small fw-semibold"></td>
                        <td><input type="text" name="items[0][item_name]" class="form-control form-control-sm" maxlength="255" placeholder="Tên sản phẩm"></td>
                        <td><input type="text" name="items[0][unit]" class="form-control form-control-sm" maxlength="50"></td>
                        <td><input type="number" step="0.01" name="items[0][quantity]" class="form-control form-control-sm"></td>
                        <td><input type="text" inputmode="numeric" name="items[0][value]" class="form-control form-control-sm text-end" autocomplete="off" placeholder="0"></td>
                        <td><input type="text" name="items[0][note]" class="form-control form-control-sm" maxlength="255"></td>
                        <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row" title="Xóa dòng"><i class="bi bi-x-lg"></i></button></td>
                    `;
                    tableBody.appendChild(tr2);
                }
                reindex();
                bindRemove();
            };
        });
    }

    addBtn.addEventListener('click', function() {
        const idx = tableBody.querySelectorAll('tr').length;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="text-center stt text-secondary small fw-semibold"></td>
            <td><input type="text" name="items[${idx}][item_name]" class="form-control form-control-sm" maxlength="255" placeholder="Tên sản phẩm"></td>
            <td><input type="text" name="items[${idx}][unit]" class="form-control form-control-sm" maxlength="50"></td>
            <td><input type="number" step="0.01" name="items[${idx}][quantity]" class="form-control form-control-sm"></td>
            <td><input type="text" inputmode="numeric" name="items[${idx}][value]" class="form-control form-control-sm text-end" autocomplete="off" placeholder="0"></td>
            <td><input type="text" name="items[${idx}][note]" class="form-control form-control-sm" maxlength="255"></td>
            <td class="text-center"><button type="button" class="btn btn-outline-danger btn-sm remove-row" title="Xóa dòng"><i class="bi bi-x-lg"></i></button></td>
        `;
        tableBody.appendChild(tr);  
        bindRemove();
        reindex();
    });

    function maybeFillBorrowTo() {
        if (!borrowFrom || !borrowTo) return;
        if (!borrowFrom.value) return;
        if (borrowTo.value) return;
        const from = new Date(borrowFrom.value + 'T00:00:00');
        const to = addDays(from, 7);
        borrowTo.value = toYmd(to);
    }

    if (borrowFrom) borrowFrom.addEventListener('change', maybeFillBorrowTo);

    reindex();
    bindRemove();
    maybeFillBorrowTo();
    if (depositAmountWrap) depositAmountWrap.style.display = '';
});
</script>
@endsection
