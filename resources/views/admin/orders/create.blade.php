@extends('layouts.admin')

@section('title', isset($isQuote) && $isQuote ? 'Tạo báo giá' : 'Tạo đơn hàng')

@section('content')
<div class="container-fluid py-4 ao-order-create">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold" style="color: #0f172a; letter-spacing: -0.02em;">
                <span class="ao-hero-icon me-2"><i class="bi bi-cart-plus"></i></span>{{ isset($isQuote) && $isQuote ? 'Tạo báo giá' : 'Tạo đơn hàng' }}
            </h1>
            <p class="text-muted mb-0 small">
                MST: ưu tiên khớp trong <strong>danh sách khách hàng</strong>, không có mới tra cứu ngoài · Sau đó chọn sản phẩm và đơn giá từng dòng.
            </p>
        </div>
        <a href="{{ (isset($isQuote) && $isQuote) ? route('admin.quotes.index') : route('admin.orders.index') }}" class="btn btn-outline-secondary rounded-pill btn-sm px-3">
            <i class="bi bi-arrow-left me-1"></i>Quay lại
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger rounded-3 border-0 shadow-sm mb-4">
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ route('admin.orders.store') }}" id="admin-order-create-form">
        @csrf
        @if(isset($isQuote) && $isQuote)
            <input type="hidden" name="quote_mode" value="1">
        @endif

        <div class="row g-4">
            {{-- Trái: Hóa đơn & liên hệ --}}
            <div class="col-lg-6 order-lg-1">
                <div class="ao-card h-100">
                    <div class="ao-card-head">
                        <i class="bi bi-receipt-cutoff me-2"></i>Xuất hóa đơn &amp; liên hệ
                    </div>
                    <div class="ao-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="ao-label">Mã số thuế</label>
                                <input type="text" name="customer_tax_code" id="ao-tax-code" class="form-control form-control-lg ao-input" value="{{ old('customer_tax_code') }}" maxlength="50" autocomplete="off" placeholder="VD: 0101234567">
                                <div id="ao-tax-hint" class="form-text small mt-1" style="min-height: 1.25rem;"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="ao-label">Người liên hệ</label>
                                <input type="text" name="customer_contact_person" id="ao-contact-person" class="form-control form-control-lg ao-input" value="{{ old('customer_contact_person') }}" maxlength="100" placeholder="Họ tên người liên hệ">
                            </div>
                            <div class="col-12">
                                <label class="ao-label">Tên công ty (HĐ)</label>
                                <input type="text" name="invoice_company_name" id="ao-invoice-company" class="form-control ao-input" value="{{ old('invoice_company_name') }}" maxlength="255" placeholder="Tên đơn vị trên hóa đơn">
                            </div>
                            <div class="col-12">
                                <label class="ao-label">Địa chỉ HĐ</label>
                                <textarea name="invoice_address" id="ao-invoice-address" class="form-control ao-input" rows="2" maxlength="2000" placeholder="Địa chỉ trên hóa đơn VAT">{{ old('invoice_address') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="ao-label">SĐT liên hệ</label>
                                <input type="text" name="customer_phone" id="ao-customer-phone" class="form-control ao-input" value="{{ old('customer_phone') }}" maxlength="50">
                            </div>
                            <div class="col-md-6">
                                <label class="ao-label">Email</label>
                                <input type="email" name="customer_email" id="ao-customer-email" class="form-control ao-input" value="{{ old('customer_email') }}" maxlength="255">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Phải: Giao hàng --}}
            <div class="col-lg-6 order-lg-2">
                <div class="ao-card h-100">
                    <div class="ao-card-head ao-card-head--ship">
                        <i class="bi bi-truck me-2"></i>Giao hàng
                    </div>
                    <div class="ao-card-body">
                        <div class="mb-3">
                            <label class="ao-label">Tên người nhận <span class="text-danger">*</span></label>
                            <input type="text" name="receiver_name" id="ao-receiver-name" class="form-control ao-input" value="{{ old('receiver_name') }}" required maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label class="ao-label">SĐT người nhận <span class="text-danger">*</span></label>
                            <input type="text" name="receiver_phone" id="ao-receiver-phone" class="form-control ao-input" value="{{ old('receiver_phone') }}" required maxlength="50">
                        </div>
                        <div class="mb-0">
                            <label class="ao-label">Địa chỉ giao hàng <span class="text-danger">*</span></label>
                            <textarea name="receiver_address" id="ao-receiver-address" class="form-control ao-input" rows="4" required maxlength="2000" placeholder="Địa chỉ nhận hàng chi tiết">{{ old('receiver_address') }}</textarea>
                        </div>
                        <div class="mt-3">
                            <label class="ao-label">Trạng thái <span class="text-danger">*</span></label>
                            <select name="status" class="form-select ao-input" required>
                                @foreach($statusOptions as $val => $label)
                                    <option value="{{ $val }}" @selected(old('status', 'pending') === $val)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="ao-card mt-4 ao-order-lines-card">
            <div class="ao-card-head d-flex flex-wrap align-items-center justify-content-between gap-2">
                <span><i class="bi bi-box-seam me-2"></i>Sản phẩm trong đơn</span>
                <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 fw-semibold" id="btn-add-line"><i class="bi bi-plus-lg me-1"></i>Thêm dòng</button>
            </div>
            <div class="ao-card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 ao-lines-table">
                        <thead>
                            <tr>
                                <th class="ps-4">Sản phẩm</th>
                                <th style="width:100px;">SL</th>
                                <th style="width:160px;">Đơn giá (đ)</th>
                                <th class="pe-4" style="width:52px;"></th>
                            </tr>
                        </thead>
                        <tbody id="order-lines"></tbody>
                    </table>
                </div>
            </div>
            <div class="ao-card-footer">
                <div class="d-flex flex-wrap gap-2 justify-content-end">
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-light border rounded-pill px-4">Hủy</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm" style="background: linear-gradient(135deg, #2563eb, #4f46e5); border: none;">
                        <i class="bi bi-check2-circle me-1"></i>{{ (isset($isQuote) && $isQuote) ? 'Lưu báo giá' : 'Lưu đơn hàng' }}
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Modal: Lưu đơn và hiển thị lịch sử mua hàng -->
    <div class="modal fade" id="aoCustomerHistoryModal" tabindex="-1" aria-labelledby="aoCustomerHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content" style="border: none; border-radius: 18px; box-shadow: 0 20px 60px rgba(0,0,0,0.18); overflow: hidden;">
                <div class="modal-header" style="background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%); color: #fff; border: 0; padding: 1rem 1.25rem;">
                    <div class="d-flex flex-column">
                        <h5 class="modal-title mb-0 fw-bold" id="aoCustomerHistoryModalLabel">
                            <i class="bi bi-people me-2"></i>{{ (isset($isQuote) && $isQuote) ? 'Lưu báo giá' : 'Lưu đơn hàng' }}
                        </h5>
                        <div class="small opacity-75">Hiển thị lịch sử mua hàng của khách theo MST/SĐT</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3" id="aoCustomerHistoryContent">
                        <div class="col-lg-4">
                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-body">
                                    <div class="fw-bold mb-3 text-dark"><i class="bi bi-clipboard-check me-2 text-primary"></i>Thông tin khách đặt hàng</div>

                                    <div class="mb-2">
                                        <label class="form-label small text-muted mb-1">Mã số thuế</label>
                                        <input type="text" id="aoModal-tax-code" class="form-control form-control-sm ao-input" autocomplete="off" placeholder="MST">
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label small text-muted mb-1">Người liên hệ</label>
                                        <input type="text" id="aoModal-contact-person" class="form-control form-control-sm ao-input" autocomplete="off" placeholder="Họ tên người liên hệ">
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label small text-muted mb-1">Tên công ty (HĐ)</label>
                                        <input type="text" id="aoModal-invoice-company" class="form-control form-control-sm ao-input" autocomplete="off" placeholder="Tên đơn vị">
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label small text-muted mb-1">Địa chỉ HĐ</label>
                                        <textarea id="aoModal-invoice-address" class="form-control form-control-sm ao-input" rows="2" maxlength="2000" placeholder="Địa chỉ trên hóa đơn VAT"></textarea>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label small text-muted mb-1">SĐT</label>
                                        <input type="text" id="aoModal-customer-phone" class="form-control form-control-sm ao-input" autocomplete="off" placeholder="SĐT liên hệ">
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label small text-muted mb-1">Email</label>
                                        <input type="email" id="aoModal-customer-email" class="form-control form-control-sm ao-input" autocomplete="off" placeholder="Email">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="card-body">
                                    <div class="fw-bold mb-3 text-dark"><i class="bi bi-truck me-2 text-primary"></i>Thông tin giao hàng</div>

                                    <div class="row g-2 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label small text-muted mb-1">Tên người nhận</label>
                                            <input type="text" id="aoModal-receiver-name" class="form-control form-control-sm ao-input" placeholder="Tên người nhận">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small text-muted mb-1">SĐT người nhận</label>
                                            <input type="text" id="aoModal-receiver-phone" class="form-control form-control-sm ao-input" placeholder="SĐT người nhận">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label small text-muted mb-1">Địa chỉ giao hàng</label>
                                            <textarea id="aoModal-receiver-address" class="form-control form-control-sm ao-input" rows="2" maxlength="2000" placeholder="Địa chỉ nhận hàng chi tiết"></textarea>
                                        </div>
                                    </div>

                                    <hr class="my-3">

                                    <div class="fw-bold mb-2 text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Lịch sử mua hàng (gợi ý)</div>

                                    <div class="d-none" id="aoCustomerHistoryLoading">
                                        <div class="text-center py-4">
                                            <div class="spinner-border text-primary" role="status"></div>
                                            <div class="mt-2 text-muted small">Đang lấy lịch sử mua hàng…</div>
                                        </div>
                                    </div>

                                    <div class="d-none" id="aoCustomerHistoryErrorWrap">
                                        <div class="alert alert-warning mb-0" role="alert" id="aoCustomerHistoryErrorText"></div>
                                    </div>

                                    <div class="row g-2" id="aoCustomerHistoryOrders"></div>
                                    <div class="text-muted small mt-2 d-none" id="aoCustomerHistoryOrdersEmpty">Chưa có lịch sử phù hợp.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border: 0; padding-top: 0;">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-semibold" id="aoCustomerHistoryConfirmBtn">
                        <i class="bi bi-check2-circle me-1"></i>{{ (isset($isQuote) && $isQuote) ? 'Xác nhận lưu báo giá' : 'Xác nhận lưu đơn' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <template id="tpl-order-line">
        <tr data-order-line>
            <td class="ps-4">
                <input type="hidden" name="items[__I__][product_id]" class="line-product-id" value="">
                <input type="text" class="form-control line-product-search ao-input" placeholder="Gõ tên hoặc mã sản phẩm..." autocomplete="off">
                <small class="text-muted line-catalog-hint d-none mt-1 d-block" style="font-size: 0.78rem;"></small>
            </td>
            <td>
                <input type="number" name="items[__I__][quantity]" class="form-control line-qty ao-input" min="1" value="1" required>
            </td>
            <td>
                <input type="number" name="items[__I__][unit_price]" class="form-control line-unit-price ao-input" min="0" step="1" value="" required placeholder="0" inputmode="numeric">
            </td>
            <td class="pe-4 text-end">
                <button type="button" class="btn btn-outline-danger btn-sm rounded-2 line-remove" title="Xóa dòng"><i class="bi bi-trash"></i></button>
            </td>
        </tr>
    </template>
</div>

<style>
    .ao-order-create { --ao-border: #e2e8f0; --ao-head: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%); }
    .ao-hero-icon {
        display: inline-flex; align-items: center; justify-content: center;
        width: 2.25rem; height: 2.25rem; border-radius: 10px;
        background: linear-gradient(135deg, #dbeafe, #e0e7ff); color: #2563eb; vertical-align: middle;
    }
    .ao-card {
        background: #fff;
        border: 1px solid var(--ao-border);
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }
    .ao-card-head {
        padding: 1rem 1.25rem;
        font-weight: 700;
        font-size: 0.95rem;
        color: #0f172a;
        background: var(--ao-head);
        border-bottom: 1px solid var(--ao-border);
    }
    .ao-card-head--ship { background: linear-gradient(180deg, #f0fdf4 0%, #ecfdf5 100%); }
    .ao-card-body { padding: 1.25rem 1.35rem 1.35rem; }
    .ao-label { font-size: 0.8rem; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 0.35rem; }
    .ao-input { border-radius: 10px; border-color: #cbd5e1; }
    .ao-input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2); }
    .ao-lines-table thead th {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        font-weight: 700;
        background: #f8fafc;
        border-bottom: 1px solid var(--ao-border);
        padding: 0.85rem 0.75rem;
    }
    .ao-lines-table tbody td { border-color: #f1f5f9; padding: 0.85rem 0.65rem; vertical-align: middle; }
    .ao-card-footer {
        padding: 0.95rem 1.35rem;
        border-top: 1px solid var(--ao-border);
        background: #fff;
    }
    .ao-card.mt-4.ao-order-lines-card { margin-top: 1.25rem !important; }
    .ao-product-suggest {
        display: none;
        position: fixed;
        z-index: 2050;
        background: #fff;
        border: 1px solid var(--ao-border);
        border-radius: 12px;
        box-shadow: 0 16px 48px rgba(15, 23, 42, 0.12);
        max-height: 280px;
        overflow-y: auto;
        padding: 6px;
    }
    .ao-product-suggest button {
        display: block;
        width: 100%;
        text-align: left;
        border: 0;
        background: transparent;
        padding: 10px 12px;
        border-radius: 8px;
        font-size: 0.875rem;
        cursor: pointer;
        color: #334155;
    }
    .ao-product-suggest button:hover { background: #f1f5f9; }
</style>
<script>
(function () {
    const tbody = document.getElementById('order-lines');
    const tpl = document.getElementById('tpl-order-line');
    const btnAdd = document.getElementById('btn-add-line');
    const taxInp = document.getElementById('ao-tax-code');
    const taxHint = document.getElementById('ao-tax-hint');

    const el = {
        contactPerson: document.getElementById('ao-contact-person'),
        invoiceCompany: document.getElementById('ao-invoice-company'),
        invoiceAddr: document.getElementById('ao-invoice-address'),
        customerPhone: document.getElementById('ao-customer-phone'),
        customerEmail: document.getElementById('ao-customer-email'),
        receiverName: document.getElementById('ao-receiver-name'),
        receiverPhone: document.getElementById('ao-receiver-phone'),
        receiverAddr: document.getElementById('ao-receiver-address'),
    };

    let suggestEl = null;
    let activeInput = null;
    let lineCounter = 0;
    let taxTimer = null;

    const LOOKUP_URL = @json(route('admin.products.lookup'));
    const LINE_OPT_BASE = @json(url('/cp-admin/orders/line-options'));
    const CUSTOMERS_LOOKUP = @json(route('admin.customers.lookup'));
    const TAX_URL_TPL = @json(route('admin.customers.taxLookup', ['taxCode' => '__TAX__']));

    function normalizeTaxCode(s) {
        return String(s || '').replace(/\s+/g, '').trim();
    }
    function isTaxLike(q) {
        const c = String(q || '').replace(/\s+/g, '');
        return /^[\d\-]{8,}$/.test(c);
    }

    async function fetchTaxLookup(code) {
        const url = TAX_URL_TPL.replace('__TAX__', encodeURIComponent(code));
        const res = await fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
        let json = null;
        try {
            json = await res.json();
        } catch (e) { json = null; }
        return { ok: res.ok, json };
    }

    async function lookupCustomersByTax(q) {
        const url = CUSTOMERS_LOOKUP + '?q=' + encodeURIComponent(q);
        const res = await fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
        if (!res.ok) return [];
        const data = await res.json();
        return Array.isArray(data) ? data : [];
    }

    function normalizeTaxDigits(s) {
        return String(s || '').replace(/\D/g, '');
    }

    /** Chỉ lấy khách trùng khớp MST (số) với ô nhập — tránh lấy nhầm bản ghi liên quan. */
    function pickCustomerExactTax(rows, typedDigits) {
        if (!rows || !rows.length || !typedDigits) return null;
        return rows.find(function (r) {
            return normalizeTaxDigits(r.tax_id || '') === typedDigits;
        }) || null;
    }

    function setHint(msg, cls) {
        if (!taxHint) return;
        taxHint.textContent = msg || '';
        taxHint.className = 'form-text small mt-1 ' + (cls || 'text-muted');
    }

    function applyTaxPayload(d) {
        if (!d) return;
        const v = function (x) { return (x != null && String(x).trim() !== '') ? String(x).trim() : ''; };
        const setIf = function (node, val) {
            if (!node || val === '') return;
            node.value = val;
        };
        setIf(el.invoiceCompany, v(d.name));
        const invAddr = v(d.tax_address) || v(d.address);
        setIf(el.invoiceAddr, invAddr);
        setIf(el.customerPhone, v(d.phone));
        setIf(el.customerEmail, v(d.email));
        const rep = v(d.representative) || v(d.invoice_recipient);
        setIf(el.contactPerson, rep);
        const shipName = rep || v(d.name);
        if (el.receiverName && (!el.receiverName.value.trim() || el.receiverName.dataset.autofill === '1')) {
            if (shipName) {
                el.receiverName.value = shipName;
                el.receiverName.dataset.autofill = '1';
            }
        }
        if (el.receiverPhone && (!el.receiverPhone.value.trim() || el.receiverPhone.dataset.autofill === '1')) {
            const ph = v(d.phone);
            if (ph) {
                el.receiverPhone.value = ph;
                el.receiverPhone.dataset.autofill = '1';
            }
        }
        const shipAddr = v(d.address) || v(d.tax_address);
        if (el.receiverAddr && (!el.receiverAddr.value.trim() || el.receiverAddr.dataset.autofill === '1')) {
            if (shipAddr) {
                el.receiverAddr.value = shipAddr;
                el.receiverAddr.dataset.autofill = '1';
            }
        }
    }

    ['receiverName', 'receiverPhone', 'receiverAddr'].forEach(function (key) {
        const node = el[key];
        if (!node) return;
        node.addEventListener('input', function () { node.dataset.autofill = '0'; });
    });

    if (taxInp) {
        taxInp.addEventListener('input', function () {
            const code = normalizeTaxCode(taxInp.value);
            if (taxTimer) clearTimeout(taxTimer);
            if (!isTaxLike(code)) {
                setHint('Nhập MST (ít nhất 8 ký tự)', 'text-muted');
                return;
            }
            setHint('Đang kiểm tra danh sách khách hàng…', 'text-muted');
            taxTimer = setTimeout(async function () {
                const typedDigits = normalizeTaxDigits(code);
                const rows = await lookupCustomersByTax(code);
                const local = pickCustomerExactTax(rows, typedDigits);
                if (local) {
                    setHint('Đã điền từ khách hàng nội bộ (CRM).', 'text-success');
                    applyTaxPayload({
                        name: local.name || '',
                        tax_address: local.tax_address || '',
                        address: local.address || '',
                        phone: local.phone || '',
                        email: local.email || '',
                        representative: local.representative || '',
                        invoice_recipient: local.invoice_recipient || '',
                    });
                    return;
                }
                setHint('Không có trong CRM — đang tra cứu nguồn thuế công khai…', 'text-muted');
                const { ok, json } = await fetchTaxLookup(code);
                if (!ok || !json || json.ok !== true || !json.data) {
                    const msg = (json && json.message) ? String(json.message) : 'Không tra cứu được MST ngoài.';
                    setHint(msg, 'text-danger');
                    return;
                }
                setHint('Đã điền từ tra cứu ngoài (không có trong danh sách khách hàng).', 'text-success');
                applyTaxPayload(json.data);
            }, 450);
        });
    }

    function ensureSuggest() {
        if (suggestEl) return suggestEl;
        suggestEl = document.createElement('div');
        suggestEl.className = 'ao-product-suggest';
        suggestEl.id = 'aoProductSuggest';
        document.body.appendChild(suggestEl);
        return suggestEl;
    }
    function hideSuggest() {
        if (suggestEl) suggestEl.style.display = 'none';
        activeInput = null;
    }
    function positionSuggest(anchor) {
        const e = ensureSuggest();
        const r = anchor.getBoundingClientRect();
        const w = Math.max(r.width, 280);
        let left = r.left;
        if (left + w > window.innerWidth - 8) left = Math.max(8, window.innerWidth - w - 8);
        e.style.left = left + 'px';
        e.style.top = (r.bottom + 6) + 'px';
        e.style.width = w + 'px';
    }
    function showSuggest(anchor, rows) {
        const e = ensureSuggest();
        if (!rows || !rows.length) { hideSuggest(); return; }
        positionSuggest(anchor);
        e.innerHTML = '';
        rows.forEach(function (p) {
            const b = document.createElement('button');
            b.type = 'button';
            b.textContent = p.name + (p.serial_number ? ' · ' + p.serial_number : '');
            b.addEventListener('mousedown', function (ev) { ev.preventDefault(); });
            b.addEventListener('click', function () {
                applyProduct(anchor.closest('tr'), p);
                hideSuggest();
            });
            e.appendChild(b);
        });
        e.style.display = 'block';
        activeInput = anchor;
    }
    async function lookupProducts(q) {
        const url = LOOKUP_URL + '?q=' + encodeURIComponent(q);
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) return [];
        const data = await res.json();
        return Array.isArray(data) ? data : [];
    }
    function formatMoney(n) {
        const x = Number(n);
        if (!isFinite(x)) return '';
        return new Intl.NumberFormat('vi-VN').format(Math.round(x)) + 'đ';
    }
    async function loadLinePrice(tr, productId) {
        const priceInp = tr.querySelector('.line-unit-price');
        const hint = tr.querySelector('.line-catalog-hint');
        if (hint) {
            hint.classList.add('d-none');
            hint.textContent = '';
        }
        if (!productId || !priceInp) return;
        try {
            const url = LINE_OPT_BASE + '/' + productId;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) throw new Error('HTTP');
            const data = await res.json();
            const fp = data.final_price != null ? Number(data.final_price) : null;
            if (fp != null && isFinite(fp)) {
                priceInp.value = String(Math.round(fp));
                if (hint) {
                    hint.textContent = 'Giá catalogue: ' + formatMoney(fp) + ' — có thể sửa';
                    hint.classList.remove('d-none');
                }
            }
        } catch (err) {
            if (hint) {
                hint.textContent = 'Không tải được giá tham chiếu';
                hint.classList.remove('d-none');
            }
        }
    }
    function applyProduct(tr, p) {
        if (!tr || !p) return;
        const hid = tr.querySelector('.line-product-id');
        const search = tr.querySelector('.line-product-search');
        if (hid) hid.value = p.id;
        if (search) search.value = p.name;
        loadLinePrice(tr, p.id);
    }
    function reindexLines() {
        tbody.querySelectorAll('tr[data-order-line]').forEach(function (tr, i) {
            tr.querySelectorAll('[name]').forEach(function (inp) {
                if (!inp.name) return;
                inp.name = inp.name.replace(/items\[\d+\]/, 'items[' + i + ']');
            });
        });
    }
    function addLine() {
        const i = lineCounter++;
        const html = tpl.innerHTML.replace(/__I__/g, String(i));
        const wrap = document.createElement('tbody');
        wrap.innerHTML = html.trim();
        const tr = wrap.firstElementChild;
        tbody.appendChild(tr);
        bindLine(tr);
        reindexLines();
    }
    function bindLine(tr) {
        const search = tr.querySelector('.line-product-search');
        const rm = tr.querySelector('.line-remove');
        if (rm) {
            rm.addEventListener('click', function () {
                tr.remove();
                reindexLines();
                if (!tbody.querySelector('tr[data-order-line]')) addLine();
            });
        }
        let t = null;
        if (search) {
            search.addEventListener('input', function () {
                clearTimeout(t);
                const q = String(search.value || '').trim();
                if (q.length < 2) { hideSuggest(); return; }
                t = setTimeout(async function () {
                    const rows = await lookupProducts(q);
                    showSuggest(search, rows);
                }, 200);
            });
            search.addEventListener('blur', function () { setTimeout(hideSuggest, 150); });
        }
    }
    document.addEventListener('click', function (ev) {
        const p = document.getElementById('aoProductSuggest');
        if (!p || p.style.display === 'none') return;
        if (ev.target.closest && ev.target.closest('#aoProductSuggest')) return;
        if (ev.target.closest && ev.target.closest('.line-product-search')) return;
        hideSuggest();
    });
    btnAdd.addEventListener('click', function () { addLine(); });
    addLine();

    // Modal hiển thị lịch sử mua hàng trước khi lưu đơn
    const aoSaveBtn = document.getElementById('ao-save-order-btn');
    const aoConfirmBtn = document.getElementById('aoCustomerHistoryConfirmBtn');
    const aoModalEl = document.getElementById('aoCustomerHistoryModal');
    const aoForm = document.getElementById('admin-order-create-form');
    if (aoSaveBtn && aoConfirmBtn && aoModalEl && aoForm) {
        const modal = new bootstrap.Modal(aoModalEl);
        const loadingEl = document.getElementById('aoCustomerHistoryLoading');
        const errorWrapEl = document.getElementById('aoCustomerHistoryErrorWrap');
        const errorTextEl = document.getElementById('aoCustomerHistoryErrorText');
        const ordersWrapEl = document.getElementById('aoCustomerHistoryOrders');
        const ordersEmptyEl = document.getElementById('aoCustomerHistoryOrdersEmpty');

        // Input trong modal (cho phép người dùng sửa trước khi lưu)
        const modalTaxCode = document.getElementById('aoModal-tax-code');
        const modalContactPerson = document.getElementById('aoModal-contact-person');
        const modalInvoiceCompany = document.getElementById('aoModal-invoice-company');
        const modalInvoiceAddress = document.getElementById('aoModal-invoice-address');
        const modalCustomerPhone = document.getElementById('aoModal-customer-phone');
        const modalCustomerEmail = document.getElementById('aoModal-customer-email');
        const modalReceiverName = document.getElementById('aoModal-receiver-name');
        const modalReceiverPhone = document.getElementById('aoModal-receiver-phone');
        const modalReceiverAddress = document.getElementById('aoModal-receiver-address');

        const customerHistoryUrl = '{{ route('admin.orders.customer-history') }}';
        let submitting = false;

        function showEl(el, show) {
            if (!el) return;
            el.classList.toggle('d-none', !show);
        }

        function renderOrders(orders) {
            if (!ordersWrapEl) return;
            ordersWrapEl.innerHTML = '';
            if (!orders || !orders.length) {
                showEl(ordersEmptyEl, true);
                return;
            }
            showEl(ordersEmptyEl, false);

            orders.forEach(function (o) {
                const statusBadge = o.status_badge ? 'bg-' + o.status_badge : 'bg-secondary';
                const statusText = o.status_label || o.status || '';
                const totalText = (o.total != null && isFinite(Number(o.total))) ? formatMoney(o.total) : '—';
                const itemsCountText = (o.items_count != null) ? String(o.items_count) : '0';

                const div = document.createElement('div');
                div.className = 'col-12';
                div.innerHTML = `
                    <div class="d-flex align-items-start justify-content-between gap-3 p-3 rounded-3" style="background: #f8fafc; border: 1px solid rgba(15, 23, 42, 0.06);">
                        <div>
                            <div class="fw-bold text-dark mb-1">${o.order_code || '—'}</div>
                            <div class="text-muted small">${o.created_at || ''}</div>
                            <div class="text-muted small mt-1">${itemsCountText} sản phẩm</div>
                        </div>
                        <div class="text-end">
                            <div class="badge ${statusBadge} mb-2" style="padding:.55rem .75rem; border-radius:999px;">${statusText}</div>
                            <div class="fw-bold" style="color:#e74c3c;">${totalText}</div>
                        </div>
                    </div>
                `;
                ordersWrapEl.appendChild(div);
            });
        }

        aoSaveBtn.addEventListener('click', async function () {
            if (submitting) return;

            const taxInp = document.getElementById('ao-tax-code');
            const phoneInp = document.getElementById('ao-customer-phone');
            const taxCode = (taxInp && taxInp.value) ? String(taxInp.value).trim() : '';
            const phone = (phoneInp && phoneInp.value) ? String(phoneInp.value).trim() : '';

            // Fill modal form with current values
            if (modalTaxCode && taxInp) modalTaxCode.value = taxInp.value || '';
            if (modalContactPerson && el && el.contactPerson) modalContactPerson.value = el.contactPerson.value || '';
            if (modalInvoiceCompany && el && el.invoiceCompany) modalInvoiceCompany.value = el.invoiceCompany.value || '';
            if (modalInvoiceAddress && el && el.invoiceAddr) modalInvoiceAddress.value = el.invoiceAddr.value || '';
            if (modalCustomerPhone && el && el.customerPhone) modalCustomerPhone.value = el.customerPhone.value || '';
            if (modalCustomerEmail && el && el.customerEmail) modalCustomerEmail.value = el.customerEmail.value || '';
            if (modalReceiverName && el && el.receiverName) modalReceiverName.value = el.receiverName.value || '';
            if (modalReceiverPhone && el && el.receiverPhone) modalReceiverPhone.value = el.receiverPhone.value || '';
            if (modalReceiverAddress && el && el.receiverAddr) modalReceiverAddress.value = el.receiverAddr.value || '';

            aoConfirmBtn.disabled = true;
            showEl(loadingEl, true);
            showEl(errorWrapEl, false);
            if (ordersWrapEl) ordersWrapEl.innerHTML = '';

            modal.show();

            try {
                if (!taxCode && !phone) {
                    renderOrders([]);
                    showEl(loadingEl, false);
                    aoConfirmBtn.disabled = false;
                    return;
                }

                const url = customerHistoryUrl
                    + '?tax_code=' + encodeURIComponent(taxCode)
                    + '&phone=' + encodeURIComponent(phone);

                const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                const data = await res.json().catch(() => null);

                showEl(loadingEl, false);
                if (!data || data.ok !== true) {
                    const msg = (data && data.message) ? String(data.message) : 'Không lấy được lịch sử mua hàng.';
                    if (errorTextEl) errorTextEl.textContent = msg;
                    showEl(errorWrapEl, true);
                    renderOrders([]);
                    aoConfirmBtn.disabled = false;
                    return;
                }

                // Orders
                renderOrders(data.orders || []);
                aoConfirmBtn.disabled = false;
            } catch (e) {
                showEl(loadingEl, false);
                if (errorTextEl) errorTextEl.textContent = 'Có lỗi khi tải lịch sử mua hàng.';
                showEl(errorWrapEl, true);
                renderOrders([]);
                aoConfirmBtn.disabled = false;
            }
        });

        aoConfirmBtn.addEventListener('click', function () {
            if (submitting) return;
            submitting = true;
            modal.hide();

            // Copy edited values back to the main form
            if (taxInp && modalTaxCode) taxInp.value = modalTaxCode.value || '';
            if (el && el.contactPerson && modalContactPerson) el.contactPerson.value = modalContactPerson.value || '';
            if (el && el.invoiceCompany && modalInvoiceCompany) el.invoiceCompany.value = modalInvoiceCompany.value || '';
            if (el && el.invoiceAddr && modalInvoiceAddress) el.invoiceAddr.value = modalInvoiceAddress.value || '';
            if (el && el.customerPhone && modalCustomerPhone) el.customerPhone.value = modalCustomerPhone.value || '';
            if (el && el.customerEmail && modalCustomerEmail) el.customerEmail.value = modalCustomerEmail.value || '';
            if (el && el.receiverName && modalReceiverName) el.receiverName.value = modalReceiverName.value || '';
            if (el && el.receiverPhone && modalReceiverPhone) el.receiverPhone.value = modalReceiverPhone.value || '';
            if (el && el.receiverAddr && modalReceiverAddress) el.receiverAddr.value = modalReceiverAddress.value || '';

            aoForm.submit();
        });
    }
})();
</script>
@endsection
