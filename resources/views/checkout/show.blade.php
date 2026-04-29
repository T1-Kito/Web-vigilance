@extends('layouts.user')

@section('title', 'Xác nhận đơn hàng')

@section('content')
<div class="container py-4 ck-page ck-page--enterprise ck-layout-sketch">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <a href="{{ route('cart.view') }}" class="btn btn-outline-secondary">&larr; Quay lại giỏ hàng</a>
        <div class="ck-steps">
            <div class="ck-steps__item is-active">1. Thông tin</div>
            <div class="ck-steps__sep"></div>
            <div class="ck-steps__item">2. Thanh toán</div>
            <div class="ck-steps__sep"></div>
            <div class="ck-steps__item">3. Hoàn tất</div>
        </div>
    </div>

   

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php $total = 0; @endphp

    <div class="checkout-grid">
        <div class="checkout-left">
            <form action="{{ route('checkout.info') }}" method="post" id="checkout-info-form">
                @csrf

                <div class="card ck-card mb-3">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="fw-bold mb-0">1) Thông tin doanh nghiệp / xuất hoá đơn</h5>
                            <span class="badge ck-badge-soft">B2B</span>
                        </div>

                        <div class="row g-3 align-items-start">
                            <div class="col-lg-3 col-md-4">
                                <label class="form-label fw-semibold">Mã số thuế</label>
                                <input type="text" name="customer_tax_code" id="customer_tax_code" class="form-control ck-control" value="{{ old('customer_tax_code') }}" placeholder="VD: 0312345678">
                                <div id="tax_lookup_hint" class="form-text" style="display:none"></div>
                            </div>
                            <div class="col-12 col-lg-9">
                                <label class="form-label fw-semibold">Tên doanh nghiệp</label>
                                <input type="text" name="invoice_company_name" id="invoice_company_name" class="form-control ck-control" value="{{ old('invoice_company_name') }}" placeholder="Tên công ty / pháp nhân thanh toán">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Địa chỉ đăng ký kinh doanh</label>
                                <input type="text" name="invoice_address" id="invoice_address" class="form-control ck-control" value="{{ old('invoice_address') }}" placeholder="Địa chỉ công ty">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card ck-card mb-3">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="fw-bold mb-0">2) Thông tin người liên hệ / nhận hàng</h5>
                            <span class="badge text-bg-light">Bắt buộc</span>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="same_as_company">
                            <label class="form-check-label fw-semibold" for="same_as_company">Thông tin giao hàng giống với thông tin doanh nghiệp</label>
                        </div>

                        <div class="row g-3 align-items-start">
                            <div class="col-lg-4 col-md-4">
                                <label class="form-label fw-semibold">Họ và tên người nhận <span class="text-danger">*</span></label>
                                <input type="text" id="receiver_name" name="receiver_name" class="form-control ck-control" required value="{{ old('receiver_name') }}" placeholder="Người nhận hàng / liên hệ">
                            </div>
                            <div class="col-lg-4 col-md-4">
                                <label class="form-label fw-semibold">Số điện thoại liên hệ <span class="text-danger">*</span></label>
                                <input type="text" name="receiver_phone" class="form-control ck-control" required pattern="[0-9]{9,12}" value="{{ old('receiver_phone') }}" placeholder="Số điện thoại nhận hàng">
                            </div>
                            <div class="col-lg-4 col-md-4">
                                <label class="form-label fw-semibold">Email</label>
                                <input type="email" name="customer_email" class="form-control ck-control" value="{{ old('customer_email') }}" placeholder="Email nhận thông báo và chứng từ">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Địa chỉ giao hàng thực tế <span class="text-danger">*</span></label>
                                <div class="row g-2">
                                    <div class="col-12 col-md-4">
                                        <select name="receiver_city" class="form-select ck-control" required>
                                            <option value="">Chọn Tỉnh/Thành</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <select name="receiver_district" class="form-select ck-control" required disabled>
                                            <option value="">Chọn Quận/Huyện</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <select name="receiver_ward" class="form-select ck-control" required disabled>
                                            <option value="">Chọn Phường/Xã</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <input type="text" id="receiver_address_detail" name="receiver_address_detail" class="form-control ck-control" required placeholder="Số nhà, đường..." value="{{ old('receiver_address_detail') }}">
                                        <input type="hidden" name="receiver_address" value="{{ old('receiver_address') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Người liên hệ (tuỳ chọn)</label>
                                <input type="text" name="customer_contact_person" class="form-control ck-control" value="{{ old('customer_contact_person') }}" placeholder="Người phụ trách chứng từ / đơn hàng">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card ck-card mb-3">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="fw-bold mb-0">3) Điều khoản thanh toán</h5>
                            <span class="badge text-bg-light">Nghiệp vụ</span>
                        </div>
                        
                        <div class="row g-3 align-items-start">
                            <div class="col-12 col-lg-4">
                                <label class="misa-label" for="payment_term">Chọn điều khoản <span class="text-danger">*</span></label>
                                <select id="payment_term" name="payment_term" class="form-select ck-control" required>
                                    <option value="full_advance" {{ old('payment_term', 'full_advance') === 'full_advance' ? 'selected' : '' }}>Thanh toán 100% trước giao hàng</option>
                                    <option value="deposit" {{ old('payment_term') === 'deposit' ? 'selected' : '' }}>Đặt cọc + phần còn lại</option>
                                    <option value="debt" {{ old('payment_term') === 'debt' ? 'selected' : '' }}>Công nợ theo hạn</option>
                                </select>
                                <div class="form-text">Chọn một kiểu thanh toán có sẵn để hệ thống gợi ý nội dung.</div>
                            </div>
                            <div class="col-12 col-lg-4" id="depositPercentWrap" style="display:none;">
                                <label class="misa-label" for="depositPercent">Tỷ lệ đặt cọc (%)</label>
                                <select id="depositPercent" name="deposit_percent" class="form-select ck-control">
                                    <option value="">Chọn tỷ lệ</option>
                                    <option value="10" {{ old('deposit_percent') == '10' ? 'selected' : '' }}>10%</option>
                                    <option value="20" {{ old('deposit_percent') == '20' ? 'selected' : '' }}>20%</option>
                                    <option value="30" {{ old('deposit_percent') == '30' ? 'selected' : '' }}>30%</option>
                                    <option value="50" {{ old('deposit_percent') == '50' ? 'selected' : '' }}>50%</option>
                                    <option value="70" {{ old('deposit_percent') == '70' ? 'selected' : '' }}>70%</option>
                                </select>
                            </div>
                            <div class="col-12 col-lg-4" id="paymentDueDaysWrap" style="display:none;">
                                <label class="misa-label" for="paymentDueDays">Hạn công nợ (ngày)</label>
                                <select id="paymentDueDays" name="payment_due_days" class="form-select ck-control">
                                    <option value="">Chọn số ngày</option>
                                    <option value="7" {{ old('payment_due_days') == '7' ? 'selected' : '' }}>7 ngày</option>
                                    <option value="15" {{ old('payment_due_days') == '15' ? 'selected' : '' }}>15 ngày</option>
                                    <option value="30" {{ old('payment_due_days') == '30' ? 'selected' : '' }}>30 ngày</option>
                                    <option value="45" {{ old('payment_due_days') == '45' ? 'selected' : '' }}>45 ngày</option>
                                    <option value="60" {{ old('payment_due_days') == '60' ? 'selected' : '' }}>60 ngày</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="misa-label" for="paymentNotePreset">Ghi chú thanh toán</label>
                                <select id="paymentNotePreset" class="form-select ck-control mb-2">
                                    <option value="">Chọn gợi ý nội dung</option>
                                    <option value="deposit">Gợi ý cho đặt cọc</option>
                                    <option value="debt">Gợi ý cho công nợ</option>
                                    <option value="custom">Tự nhập / chỉnh lại</option>
                                </select>
                                <textarea id="paymentNote" name="payment_note" class="form-control ck-control" rows="4" placeholder="Chọn một gợi ý bên trên hoặc nhập nội dung cụ thể cho hợp đồng/đơn hàng">{{ old('payment_note') }}</textarea>
                                <div class="form-text">Có thể chọn gợi ý sẵn rồi chỉnh lại để phù hợp đơn hàng thực tế.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="submit" class="btn btn-primary fw-bold px-4 ck-btn-next">Tiếp tục</button>
                </div>
            </form>
        </div>

        <div class="checkout-right">
            <div class="ck-sticky">
                <div class="card ck-card ck-summary" style="background:#f8f9fa;">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold mb-0">Tóm tắt đơn hàng</h5>
                            <span class="text-muted" style="font-weight:600;">{{ $cartItems->where('parent_cart_item_id', null)->count() }} sản phẩm</span>
                        </div>

                        <div class="ck-items">
                            @foreach($cartItems->where('parent_cart_item_id', null) as $item)
                                @php
                                    $product = $item->product;
                                    $finalPrice = $item->price;
                                    $subtotal = $finalPrice * $item->quantity;
                                    $total += $subtotal;
                                    $addons = $cartItems->where('parent_cart_item_id', $item->id);
                                @endphp
                                <div class="ck-item">
                                    <div class="ck-item__left">
                                        <img class="ck-item__img" src="{{ asset('images/products/' . $product->image) }}" alt="{{ $product->name }}">
                                        <div>
                                            <div class="ck-item__name">{{ $product->name }}</div>
                                            <div class="ck-item__meta">SL: {{ $item->quantity }}</div>
                                        </div>
                                    </div>
                                    <div class="ck-item__price">{{ number_format($subtotal, 0, ',', '.') }}đ</div>
                                </div>

                                @if($addons->count())
                                    @foreach($addons as $addon)
                                        @php
                                            $addonProduct = $addon->addonProduct ?? $addon->product;
                                            $addonPrice = $addon->price;
                                            $addonSubtotal = $addonPrice * $addon->quantity;
                                            $total += $addonSubtotal;
                                        @endphp
                                        <div class="ck-item ck-item--addon">
                                            <div class="ck-item__left">
                                                <img class="ck-item__img" src="{{ asset('images/products/' . ($addonProduct->image ?? '')) }}" alt="{{ $addonProduct->name ?? '' }}">
                                                <div>
                                                    <div class="ck-item__name">{{ $addonProduct->name ?? '' }} <span class="badge bg-warning text-dark">Mua kèm</span></div>
                                                    <div class="ck-item__meta">SL: {{ $addon->quantity }}</div>
                                                </div>
                                            </div>
                                            <div class="ck-item__price">{{ number_format($addonSubtotal, 0, ',', '.') }}đ</div>
                                        </div>
                                    @endforeach
                                @endif
                            @endforeach
                        </div>

                        <hr class="my-3">

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">Tạm tính</div>
                            <div class="fw-bold">{{ number_format($total, 0, ',', '.') }}đ</div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <div class="text-muted">Vận chuyển</div>
                            <div class="fw-bold text-success">Miễn phí</div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <div class="fw-bold">Tổng cộng</div>
                            <div class="fw-bold" style="font-size:1.15rem; color:#16a34a;">{{ number_format($total, 0, ',', '.') }}đ</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const taxEl = document.getElementById('customer_tax_code');
    const companyEl = document.getElementById('invoice_company_name');
    const addrEl = document.getElementById('invoice_address');
    const hintEl = document.getElementById('tax_lookup_hint');
    if (!taxEl || !companyEl || !addrEl || !hintEl) return;

    let timer = null;
    let lastTax = '';
    let inFlight = null;

    function setHint(text, kind) {
        hintEl.textContent = text || '';
        hintEl.classList.remove('text-success', 'text-danger', 'text-muted');
        if (kind) hintEl.classList.add(kind);
        hintEl.style.display = text ? '' : 'none';
    }

    function setTaxLocked(locked) {
        taxEl.readOnly = !!locked;
        taxEl.classList.toggle('bg-light', !!locked);
    }

    function toggleReadonlyInvoiceFields(locked) {
        companyEl.readOnly = !!locked;
        addrEl.readOnly = !!locked;
        companyEl.classList.toggle('bg-light', !!locked);
        addrEl.classList.toggle('bg-light', !!locked);
    }

    function resetInvoiceInfo() {
        lastTax = '';
        if (inFlight && typeof inFlight.abort === 'function') inFlight.abort();
        companyEl.value = '';
        addrEl.value = '';
        setHint('', null);
        setTaxLocked(false);
        toggleReadonlyInvoiceFields(false);
    }

    function normalizeTax(v) {
        return (v || '').toString().trim().replace(/\s+/g, '');
    }

    async function lookupTaxCode(tax) {
        if (!tax || tax === lastTax) return;
        if (inFlight && typeof inFlight.abort === 'function') inFlight.abort();
        inFlight = new AbortController();
        setHint('Đang tra cứu mã số thuế...', 'text-muted');
        try {
            const res = await fetch(`/api/tax-code/${encodeURIComponent(tax)}`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                signal: inFlight.signal,
            });
            const payload = await res.json().catch(() => null);
            if (!res.ok) {
                setHint('Hệ thống không tìm thấy mã số thuế hoặc mã số thuế không đúng.', 'text-danger');
                lastTax = '';
                return;
            }
            const name = payload && payload.data ? (payload.data.name || '') : '';
            const address = payload && payload.data ? (payload.data.address || '') : '';
            if (name || address) {
                lastTax = tax;
                if (name) companyEl.value = name;
                if (address) addrEl.value = address;
                setHint('Đã lấy thông tin công ty từ mã số thuế.', 'text-success');
                setTaxLocked(true);
                toggleReadonlyInvoiceFields(true);
            } else {
                setHint('Hệ thống không tìm thấy mã số thuế.', 'text-danger');
                lastTax = '';
                setTaxLocked(false);
                toggleReadonlyInvoiceFields(false);
            }
        } catch (e) {
            if (e && e.name === 'AbortError') return;
            lastTax = '';
            setHint('Có lỗi khi tra cứu mã số thuế.', 'text-danger');
        }
    }

    function scheduleLookup() {
        if (timer) clearTimeout(timer);
        timer = setTimeout(() => {
            const tax = normalizeTax(taxEl.value);
            if (!tax) { resetInvoiceInfo(); return; }
            if (tax.length < 8) { setHint('', null); setTaxLocked(false); toggleReadonlyInvoiceFields(false); return; }
            if (lastTax && tax !== lastTax) { setHint('Vui lòng xóa mã số cũ và nhập lại từ đầu.', 'text-danger'); return; }
            lookupTaxCode(tax);
        }, 350);
    }

    taxEl.addEventListener('input', scheduleLookup);
    taxEl.addEventListener('blur', scheduleLookup);
    const sameAsCompanyEl = document.getElementById('same_as_company');
    const receiverNameEl = document.getElementById('receiver_name');
    const receiverAddressDetailEl = document.getElementById('receiver_address_detail');
    function applySameAsCompany() {
        if (!sameAsCompanyEl || !sameAsCompanyEl.checked) return;
        if (receiverNameEl && companyEl) receiverNameEl.value = companyEl.value || receiverNameEl.value || '';
        if (receiverAddressDetailEl && addrEl) receiverAddressDetailEl.value = addrEl.value || receiverAddressDetailEl.value || '';
    }

    if (sameAsCompanyEl) sameAsCompanyEl.addEventListener('change', applySameAsCompany);
    if (companyEl) companyEl.addEventListener('input', applySameAsCompany);
    if (addrEl) addrEl.addEventListener('input', applySameAsCompany);

    const citySelect = document.querySelector('select[name="receiver_city"]');
    const districtSelect = document.querySelector('select[name="receiver_district"]');
    const wardSelect = document.querySelector('select[name="receiver_ward"]');
    const detailInput = document.querySelector('input[name="receiver_address_detail"]');
    const fullAddressInput = document.querySelector('input[name="receiver_address"]');

    const setOptions = (select, items, placeholder) => {
        if (!select) return;
        select.innerHTML = '';
        const opt = document.createElement('option');
        opt.value = '';
        opt.textContent = placeholder;
        select.appendChild(opt);
        items.forEach((it) => {
            const o = document.createElement('option');
            o.value = String(it.code);
            o.textContent = String(it.name);
            select.appendChild(o);
        });
    };

    const fetchJson = async (url) => {
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error('fetch_failed');
        return await res.json();
    };

    const loadCities = async () => {
        try {
            const cities = await fetchJson('https://provinces.open-api.vn/api/p/');
            setOptions(citySelect, Array.isArray(cities) ? cities : [], 'Chọn Tỉnh/Thành');
        } catch (e) {}
    };

    const loadDistricts = async (cityCode) => {
        if (!districtSelect || !wardSelect) return;
        districtSelect.disabled = true;
        wardSelect.disabled = true;
        setOptions(districtSelect, [], 'Chọn Quận/Huyện');
        setOptions(wardSelect, [], 'Chọn Phường/Xã');
        if (!cityCode) return;
        try {
            const city = await fetchJson('https://provinces.open-api.vn/api/p/' + encodeURIComponent(cityCode) + '?depth=2');
            const districts = city && Array.isArray(city.districts) ? city.districts : [];
            setOptions(districtSelect, districts, 'Chọn Quận/Huyện');
            districtSelect.disabled = false;
        } catch (e) {}
    };

    const loadWards = async (districtCode) => {
        if (!wardSelect) return;
        wardSelect.disabled = true;
        setOptions(wardSelect, [], 'Chọn Phường/Xã');
        if (!districtCode) return;
        try {
            const district = await fetchJson('https://provinces.open-api.vn/api/d/' + encodeURIComponent(districtCode) + '?depth=2');
            const wards = district && Array.isArray(district.wards) ? district.wards : [];
            setOptions(wardSelect, wards, 'Chọn Phường/Xã');
            wardSelect.disabled = false;
        } catch (e) {}
    };

    if (citySelect) citySelect.addEventListener('change', async () => await loadDistricts(citySelect.value));
    if (districtSelect) districtSelect.addEventListener('change', async () => await loadWards(districtSelect.value));

    const paymentNoteEl = document.getElementById('paymentNote');
    const paymentTermEl = document.getElementById('payment_term');
    const paymentNotePresetEl = document.getElementById('paymentNotePreset');
    const depositPercentEl = document.getElementById('depositPercent');
    const paymentDueDaysEl = document.getElementById('paymentDueDays');
    const paymentTemplates = {
        full_advance: 'Thanh toán 100% giá trị đơn hàng trước khi giao hàng.\n\nThanh toán toàn bộ trước khi giao, hình thức chuyển khoản/tiền mặt theo hướng dẫn của bên bán.',
        deposit: 'Thanh toán __DEPOSIT_PERCENT__% giá trị đơn hàng trước khi giao hàng; __REMAINING_PERCENT__% còn lại thanh toán khi nhận đủ hàng, xuất hóa đơn hoặc nghiệm thu theo thỏa thuận.\n\nCó thể thay đổi mốc thanh toán nếu hai bên thống nhất bằng văn bản.',
        debt: 'Thanh toán trong vòng __DUE_DAYS__ ngày kể từ ngày nhận hàng/xuất hóa đơn/nghiệm thu.\n\nCông nợ được đối chiếu và thanh toán theo kỳ hạn đã thống nhất giữa hai bên.'
    };

    function buildPaymentTemplate() {
        const term = paymentTermEl?.value || 'full_advance';
        let text = paymentTemplates[term] || '';
        if (term === 'deposit') {
            const deposit = (depositPercentEl?.value || '').trim() || '...';
            const remaining = deposit !== '...' ? Math.max(0, 100 - parseFloat(deposit)) : '...';
            text = text.replace('__DEPOSIT_PERCENT__', deposit).replace('__REMAINING_PERCENT__', String(remaining));
        }
        if (term === 'debt') {
            const days = (paymentDueDaysEl?.value || '').trim() || '...';
            text = text.replace('__DUE_DAYS__', days);
        }
        return text;
    }

    function syncPaymentNote(force = false) {
        if (!paymentNoteEl) return;
        if (force || !paymentNoteEl.value.trim() || paymentNoteEl.dataset.autoTemplate === '1') {
            paymentNoteEl.value = buildPaymentTemplate();
            paymentNoteEl.dataset.autoTemplate = '1';
        }
    }

    function syncPaymentInputsVisibility() {
        const term = paymentTermEl?.value || 'full_advance';
        const depositWrap = document.getElementById('depositPercentWrap');
        const debtWrap = document.getElementById('paymentDueDaysWrap');
        if (depositWrap) depositWrap.style.display = term === 'deposit' ? '' : 'none';
        if (debtWrap) debtWrap.style.display = term === 'debt' ? '' : 'none';
        if (term !== 'deposit' && depositPercentEl) depositPercentEl.value = depositPercentEl.value || '';
        if (term !== 'debt' && paymentDueDaysEl) paymentDueDaysEl.value = paymentDueDaysEl.value || '';
    }

    if (paymentTermEl) {
        paymentTermEl.addEventListener('change', function () {
            syncPaymentInputsVisibility();
            syncPaymentNote(true);
        });
    }

    if (depositPercentEl) depositPercentEl.addEventListener('change', () => syncPaymentNote(true));
    if (paymentDueDaysEl) paymentDueDaysEl.addEventListener('change', () => syncPaymentNote(true));
    if (paymentNotePresetEl) {
        paymentNotePresetEl.addEventListener('change', function () {
            if (!paymentNoteEl) return;
            if (this.value === 'deposit') {
                paymentNoteEl.value = buildPaymentTemplate();
                paymentNoteEl.dataset.autoTemplate = '1';
            } else if (this.value === 'debt') {
                paymentNoteEl.value = buildPaymentTemplate();
                paymentNoteEl.dataset.autoTemplate = '1';
            }
        });
    }

    if (paymentNoteEl) {
        paymentNoteEl.addEventListener('input', function () {
            paymentNoteEl.dataset.autoTemplate = '0';
        });
    }

    syncPaymentInputsVisibility();
    syncPaymentNote(true);

    const form = document.getElementById('checkout-info-form');
    if (form) {
        form.addEventListener('submit', () => {
            const cityText = citySelect?.options[citySelect.selectedIndex]?.textContent || '';
            const districtText = districtSelect?.options[districtSelect.selectedIndex]?.textContent || '';
            const wardText = wardSelect?.options[wardSelect.selectedIndex]?.textContent || '';
            const detail = (detailInput?.value || '').trim();
            const parts = [detail, wardText, districtText, cityText].map(s => String(s || '').trim()).filter(Boolean);
            if (fullAddressInput) fullAddressInput.value = parts.join(', ');
        });
    }

    if (citySelect) loadCities();
    const initial = normalizeTax(taxEl.value);
    if (initial) { setTaxLocked(false); toggleReadonlyInvoiceFields(false); scheduleLookup(); }
    else { setTaxLocked(false); toggleReadonlyInvoiceFields(false); }
});
</script>

<style>
    .ck-page { --ck-primary:#1f4db8; --ck-primary-soft:#edf3ff; --ck-border:#dbe3f0; --ck-text:#0f172a; --ck-muted:#64748b; }
    .ck-page--enterprise .card.ck-card { margin-bottom: 12px; }
    .ck-layout-sketch .checkout-grid { display:grid; grid-template-columns:minmax(0,1.72fr) minmax(320px,.9fr); gap:14px; align-items:start; }
    .ck-layout-sketch .checkout-left, .ck-layout-sketch .checkout-right { min-width:0; }
    .ck-card { border:1px solid var(--ck-border); border-radius:16px; background:#fff; box-shadow:0 10px 26px rgba(15,23,42,.05); }
    .ck-summary { background:#f8f9fa; border:1px solid #e9ecef; }
    .ck-control { border-radius:10px; border-color:#d7deeb; min-height:42px; }
    #invoice_company_name { width: 100%; }
    .ck-control:focus { border-color:#94b2f3; box-shadow:0 0 0 .2rem rgba(31,77,184,.12); }
    .ck-btn-next { border-radius:10px; padding-top:10px; padding-bottom:10px; min-width:132px; background:linear-gradient(180deg,#2563eb,#1d4ed8); border-color:#1d4ed8; }
    .misa-label { font-size:.86rem; font-weight:700; color:#334155; margin-bottom:6px; }
    .misa-card-group { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; }
    .misa-card-group--inline { margin-bottom: 8px; }
    .misa-radio-card { display:flex; align-items:center; min-height:54px; border:1px solid #dbe3f0; border-radius:10px; background:#fff; padding:10px 12px; cursor:pointer; font-weight:700; color:#0f172a; }
    .misa-radio-card input { margin-right:8px; }
    .ck-steps { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
    .ck-steps__item { font-weight:800; font-size:.86rem; color:rgba(15,23,42,.6); background:#f8fafc; border:1px solid #e2e8f0; padding:6px 10px; border-radius:999px; }
    .ck-steps__item.is-active { color:#1f4db8; background:var(--ck-primary-soft); border-color:#cfe0ff; }
    .ck-steps__sep { width:24px; height:2px; background:#dbe3f0; border-radius:2px; }
    .ck-sticky { position:sticky; top:86px; }
    .ck-items { display:grid; gap:10px; max-height:340px; overflow:auto; padding-right:4px; }
    .ck-item { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:10px 12px; border:1px solid #e2e8f0; border-radius:12px; background:#fff; }
    .ck-item--addon { background:#fffbe9; border-color:#f2e7bd; }
    .ck-item__left { display:flex; align-items:center; gap:10px; min-width:0; }
    .ck-item__img { width:42px; height:42px; object-fit:cover; border-radius:10px; border:1px solid #e2e8f0; background:#fff; }
    .ck-item__name { font-weight:700; color:#0f172a; line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:240px; }
    .ck-item__meta { font-size:.82rem; color:#64748b; font-weight:700; }
    .ck-item__price { font-weight:900; color:#159249; }
    @media (max-width:991.98px) { .ck-layout-sketch .checkout-grid { grid-template-columns:1fr; } .ck-sticky { position:static; } }
    @media (max-width:575.98px) { .ck-steps__sep { display:none; } .ck-btn-next { width:100%; } .misa-card-group { grid-template-columns:1fr; } }
</style>
@endsection