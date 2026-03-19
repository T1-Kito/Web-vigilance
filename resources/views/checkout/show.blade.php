@extends('layouts.user')

@section('title', 'Xác nhận đơn hàng')

@section('content')
<div class="container py-4">
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

    <div class="mb-4">
        <h2 class="fw-bold mb-1" style="color:#0f172a;">Xác nhận đơn hàng</h2>
        <div class="text-muted">Nhập thông tin giao hàng để tiếp tục</div>
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

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <form action="{{ route('checkout.info') }}" method="post" id="checkout-info-form">
                @csrf

                <div class="card ck-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="fw-bold mb-0">Thông tin người nhận</h5>
                            <span class="badge text-bg-light">Bước 1/2</span>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Mã số thuế</label>
                                <div class="input-group">
                                    <input type="text" name="customer_tax_code" id="customer_tax_code" class="form-control ck-control" value="{{ old('customer_tax_code') }}" placeholder="VD: 0312345678">
                                    <button class="btn btn-outline-secondary" type="button" id="btn_change_tax" style="display:none;">Đổi MST</button>
                                </div>
                                <div id="tax_lookup_hint" class="form-text" style="display:none"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Họ tên người nhận <span class="text-danger">*</span></label>
                                <input type="text" name="receiver_name" class="form-control ck-control" required value="{{ old('receiver_name') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Số điện thoại liên hệ <span class="text-danger">*</span></label>
                                <input type="text" name="receiver_phone" class="form-control ck-control" required pattern="[0-9]{9,12}" value="{{ old('receiver_phone') }}">
                            </div>

                            <div id="invoice_fields" class="col-12" style="display:none;">
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label fw-semibold">Tên công ty</label>
                                        <input type="text" name="invoice_company_name" id="invoice_company_name" class="form-control ck-control bg-light" value="{{ old('invoice_company_name') }}" placeholder="Tự động điền theo MST" readonly>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Địa chỉ công ty</label>
                                        <input type="text" name="invoice_address" id="invoice_address" class="form-control ck-control bg-light" value="{{ old('invoice_address') }}" placeholder="Tự động điền theo MST" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Địa chỉ giao hàng <span class="text-danger">*</span></label>
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
                                        <input type="text" name="receiver_address_detail" class="form-control ck-control" required placeholder="Số nhà, đường..." value="{{ old('receiver_address_detail') }}">
                                        <input type="hidden" name="receiver_address" value="{{ old('receiver_address') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Email</label>
                                <div class="text-muted" style="font-size:.9rem;">Email này sẽ nhận tin nhắn khi đơn hàng được duyệt</div>
                                <input type="email" name="customer_email" class="form-control ck-control" value="{{ old('customer_email') }}" placeholder="Nhập email của bạn">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-3">
                    <button type="submit" class="btn btn-primary fw-bold px-4 ck-btn-next">Tiếp tục</button>
                </div>
            </form>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const taxEl = document.getElementById('customer_tax_code');
            const invoiceFieldsEl = document.getElementById('invoice_fields');
            const companyEl = document.getElementById('invoice_company_name');
            const addrEl = document.getElementById('invoice_address');
            const hintEl = document.getElementById('tax_lookup_hint');
            const btnChangeTaxEl = document.getElementById('btn_change_tax');

            if (!taxEl || !invoiceFieldsEl || !companyEl || !addrEl || !hintEl || !btnChangeTaxEl) return;

            let timer = null;
            let lastTax = '';
            let inFlight = null;

            function setHint(text, kind) {
                hintEl.textContent = text || '';
                hintEl.classList.remove('text-success', 'text-danger', 'text-muted');
                if (kind) {
                    hintEl.classList.add(kind);
                }
                hintEl.style.display = text ? '' : 'none';
            }

            function showInvoiceFields(show) {
                invoiceFieldsEl.style.display = show ? '' : 'none';
            }

            function setTaxLocked(locked) {
                taxEl.readOnly = !!locked;
                if (locked) {
                    taxEl.classList.add('bg-light');
                } else {
                    taxEl.classList.remove('bg-light');
                }
                btnChangeTaxEl.style.display = locked ? '' : 'none';
            }

            function resetInvoiceInfo() {
                lastTax = '';
                if (inFlight && typeof inFlight.abort === 'function') {
                    inFlight.abort();
                }
                showInvoiceFields(false);
                companyEl.value = '';
                addrEl.value = '';
                setHint('', null);
                setTaxLocked(false);
            }

            function normalizeTax(v) {
                return (v || '').toString().trim().replace(/\s+/g, '');
            }

            async function lookupTaxCode(tax) {
                if (!tax) return;
                if (tax === lastTax) return;

                if (inFlight && typeof inFlight.abort === 'function') {
                    inFlight.abort();
                }
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
                        showInvoiceFields(false);
                        return;
                    }

                    const name = payload && payload.data ? (payload.data.name || '') : '';
                    const address = payload && payload.data ? (payload.data.address || '') : '';

                    if (name || address) {
                        lastTax = tax;
                        showInvoiceFields(true);
                        if (name) companyEl.value = name;
                        if (address) addrEl.value = address;
                        setHint('Đã lấy thông tin công ty từ mã số thuế.', 'text-success');
                        setTaxLocked(true);
                    } else {
                        showInvoiceFields(false);
                        setHint('Hệ thống không tìm thấy mã số thuế.', 'text-danger');
                        lastTax = '';
                        setTaxLocked(false);
                    }
                } catch (e) {
                    if (e && e.name === 'AbortError') {
                        lastTax = '';
                        return;
                    }
                    lastTax = '';
                    showInvoiceFields(false);
                    setTaxLocked(false);
                    setHint('Có lỗi khi tra cứu mã số thuế.', 'text-danger');
                }
            }

            function scheduleLookup() {
                if (timer) clearTimeout(timer);
                timer = setTimeout(() => {
                    const tax = normalizeTax(taxEl.value);
                    if (!tax) {
                        resetInvoiceInfo();
                        return;
                    }

                    if (tax.length < 8) {
                        showInvoiceFields(false);
                        setHint('', null);
                        setTaxLocked(false);
                        return;
                    }

                    if (lastTax && tax !== lastTax) {
                        showInvoiceFields(true);
                        setHint('Vui lòng xóa mã số cũ và nhập lại từ đầu.', 'text-danger');
                        return;
                    }

                    showInvoiceFields(true);
                    lookupTaxCode(tax);
                }, 350);
            }

            taxEl.addEventListener('input', scheduleLookup);
            taxEl.addEventListener('blur', scheduleLookup);

            btnChangeTaxEl.addEventListener('click', function () {
                taxEl.value = '';
                resetInvoiceInfo();
                taxEl.focus();
            });

            const initial = normalizeTax(taxEl.value);
            if (initial) {
                showInvoiceFields(false);
                setTaxLocked(false);
                scheduleLookup();
            } else {
                showInvoiceFields(false);
                setTaxLocked(false);
            }
        });
        </script>

        <div class="col-12 col-lg-5">
            <div class="ck-sticky">
                <div class="card ck-card">
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
    (function () {
        const form = document.getElementById('checkout-info-form');
        if (!form) return;

        const citySelect = form.querySelector('select[name="receiver_city"]');
        const districtSelect = form.querySelector('select[name="receiver_district"]');
        const wardSelect = form.querySelector('select[name="receiver_ward"]');
        const detailInput = form.querySelector('input[name="receiver_address_detail"]');
        const fullAddressInput = form.querySelector('input[name="receiver_address"]');

        const setOptions = (select, items, placeholder) => {
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
            } catch (e) {
            }
        };

        const loadDistricts = async (cityCode) => {
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
            } catch (e) {
            }
        };

        const loadWards = async (districtCode) => {
            wardSelect.disabled = true;
            setOptions(wardSelect, [], 'Chọn Phường/Xã');
            if (!districtCode) return;
            try {
                const district = await fetchJson('https://provinces.open-api.vn/api/d/' + encodeURIComponent(districtCode) + '?depth=2');
                const wards = district && Array.isArray(district.wards) ? district.wards : [];
                setOptions(wardSelect, wards, 'Chọn Phường/Xã');
                wardSelect.disabled = false;
            } catch (e) {
            }
        };

        citySelect.addEventListener('change', async () => {
            await loadDistricts(citySelect.value);
        });

        districtSelect.addEventListener('change', async () => {
            await loadWards(districtSelect.value);
        });

        form.addEventListener('submit', () => {
            const cityText = citySelect.options[citySelect.selectedIndex]?.textContent || '';
            const districtText = districtSelect.options[districtSelect.selectedIndex]?.textContent || '';
            const wardText = wardSelect.options[wardSelect.selectedIndex]?.textContent || '';
            const detail = (detailInput?.value || '').trim();
            const parts = [detail, wardText, districtText, cityText].map(s => String(s || '').trim()).filter(Boolean);
            if (fullAddressInput) fullAddressInput.value = parts.join(', ');
        });

        loadCities();
    })();
</script>

<style>
    .ck-card { border: 1px solid rgba(15, 23, 42, 0.10); border-radius: 16px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05); }
    .ck-control { border-radius: 12px; }
    .ck-btn-next { border-radius: 12px; padding-top: 10px; padding-bottom: 10px; }

    .ck-steps { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .ck-steps__item { font-weight: 800; font-size: 0.9rem; color: rgba(15,23,42,0.55); background: rgba(15,23,42,0.04); border: 1px solid rgba(15,23,42,0.08); padding: 6px 10px; border-radius: 999px; }
    .ck-steps__item.is-active { color: #0f172a; background: rgba(37,99,235,0.10); border-color: rgba(37,99,235,0.25); }
    .ck-steps__sep { width: 26px; height: 2px; background: rgba(15,23,42,0.12); border-radius: 2px; }

    .ck-sticky { position: sticky; top: 86px; }
    .ck-items { display: grid; gap: 10px; max-height: 340px; overflow: auto; padding-right: 4px; }
    .ck-item { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 12px; border: 1px solid rgba(15,23,42,0.08); border-radius: 14px; background: #fff; }
    .ck-item--addon { background: #fffbe9; }
    .ck-item__left { display: flex; align-items: center; gap: 10px; min-width: 0; }
    .ck-item__img { width: 42px; height: 42px; object-fit: cover; border-radius: 10px; border: 1px solid rgba(15,23,42,0.10); background: #fff; }
    .ck-item__name { font-weight: 800; color: #0f172a; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 240px; }
    .ck-item__meta { font-size: 0.85rem; color: rgba(15,23,42,0.60); font-weight: 700; }
    .ck-item__price { font-weight: 900; color: #16a34a; }

    @media (max-width: 991.98px) {
        .ck-sticky { position: static; top: auto; }
        .ck-items { max-height: none; overflow: visible; }
    }

    @media (max-width: 575.98px) {
        .ck-steps__sep { display: none; }
        .ck-item__name { max-width: 190px; }
        .ck-btn-next { width: 100%; }
    }
</style>
@endsection