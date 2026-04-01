@extends('layouts.admin')

@section('title', 'Thêm khách hàng')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4" style="display:flex; align-items:center; justify-content:space-between; gap: 12px; flex-wrap: wrap;">
        <h2 class="mb-0">Thêm khách hàng</h2>
        <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">Quay lại</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.customers.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-12 mt-2">
                        <div class="fw-bold">Nhóm 1: Định danh</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Mã số thuế</label>
                        <div class="position-relative">
                            <input type="text" name="tax_id" class="form-control" value="{{ old('tax_id') }}" maxlength="255">
                            <div id="tax_lookup_spinner" class="spinner-border spinner-border-sm text-primary position-absolute end-0 top-50 translate-middle-y" style="display:none" role="status" aria-hidden="true"></div>
                        </div>
                        <div id="tax_lookup_hint" class="form-text" style="display:none" aria-live="polite"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tên công ty</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" maxlength="255" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Tình trạng</label>
                        <input type="text" name="company_status" class="form-control" value="{{ old('company_status') }}" maxlength="255">
                    </div>

                    <div class="col-12 mt-3">
                        <div class="fw-bold">Nhóm 2: Pháp lý</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Loại hình DN</label>
                        <input type="text" name="business_type" class="form-control" value="{{ old('business_type') }}" maxlength="255">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Người đại diện</label>
                        <input type="text" name="representative" class="form-control" value="{{ old('representative') }}" maxlength="255" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    </div>

                    <div class="col-12 mt-3">
                        <div class="fw-bold">Nhóm 3: Địa chỉ</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Địa chỉ thuế</label>
                        <textarea name="tax_address" class="form-control" rows="2">{{ old('tax_address') }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Địa chỉ liên hệ</label>
                        <textarea name="address" class="form-control" rows="3">{{ old('address') }}</textarea>
                    </div>

                    <div class="col-12 mt-3">
                        <div class="fw-bold">Nhóm 4: Liên hệ</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" maxlength="30">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" maxlength="255">
                    </div>

                    <div class="col-12 mt-3">
                        <div class="fw-bold">Nhóm 5: Hóa đơn</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Người nhận HĐ</label>
                        <input type="text" name="invoice_recipient" class="form-control" value="{{ old('invoice_recipient') }}" maxlength="255">
                    </div>

                    <div class="col-12 mt-3">
                        <div class="fw-bold">Nhóm 6: Khác</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Cơ quan thuế quản lý</label>
                        <input type="text" name="managed_by" class="form-control" value="{{ old('managed_by') }}" maxlength="255">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Ngành nghề chính</label>
                        <textarea name="main_business" class="form-control" rows="2">{{ old('main_business') }}</textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const taxInp = document.querySelector('input[name="tax_id"]');
    const nameInp = document.querySelector('input[name="name"]');
    const phoneInp = document.querySelector('input[name="phone"]');
    const addrInp = document.querySelector('textarea[name="address"]');
    const taxAddrInp = document.querySelector('textarea[name="tax_address"]');
    const statusInp = document.querySelector('input[name="company_status"]');
    const repInp = document.querySelector('input[name="representative"]');
    const managedByInp = document.querySelector('input[name="managed_by"]');
    const businessTypeInp = document.querySelector('input[name="business_type"]');
    const mainBusinessInp = document.querySelector('textarea[name="main_business"]');
    const emailInp = document.querySelector('input[name="email"]');
    const invoiceRecipientInp = document.querySelector('input[name="invoice_recipient"]');
    const hintEl = document.getElementById('tax_lookup_hint');
    const spinnerEl = document.getElementById('tax_lookup_spinner');

    const CUSTOMERS_LOOKUP = @json(route('admin.customers.lookup'));

    if (!taxInp) return;

    let lastFilled = '';
    let lookupTimer = null;

    function normalizeTaxCode(v) {
        return String(v || '').replace(/\s+/g, '').trim();
    }

    function normalizeTaxDigits(s) {
        return String(s || '').replace(/\D/g, '');
    }

    function isTaxLike(q) {
        const c = String(q || '').replace(/\s+/g, '');
        return /^[\d\-]{8,}$/.test(c);
    }

    async function lookupCustomersByTax(q) {
        const url = CUSTOMERS_LOOKUP + '?q=' + encodeURIComponent(q);
        const res = await fetch(url, { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
        if (!res.ok) return [];
        const data = await res.json();
        return Array.isArray(data) ? data : [];
    }

    /** Khớp đúng MST đã nhập (so sánh chỉ phần số, hỗ trợ chi nhánh dạng xxx-019). */
    function pickCustomerExactTax(rows, typedDigits) {
        if (!rows || !rows.length || !typedDigits) return null;
        return rows.find(function (r) {
            return normalizeTaxDigits(r.tax_id || '') === typedDigits;
        }) || null;
    }

    function isAutofilled(el) {
        return el && String(el.dataset.autofilled || '') === '1';
    }

    function markUserEdited(el) {
        if (!el) return;
        el.dataset.autofilled = '0';
    }

    function setAutofill(el, v) {
        if (!el) return;
        const next = String(v || '').trim();
        const cur = String(el.value || '').trim();
        if (cur !== '' && !isAutofilled(el)) return;
        el.value = next;
        el.dataset.autofilled = '1';
    }

    function setHint(text, kind) {
        if (!hintEl) return;
        hintEl.textContent = text || '';
        hintEl.style.display = text ? '' : 'none';
        hintEl.classList.remove('text-success', 'text-danger', 'text-muted', 'text-warning');
        if (kind) hintEl.classList.add(kind);
    }

    function setLoading(loading) {
        if (spinnerEl) spinnerEl.style.display = loading ? '' : 'none';
    }

    function clearAutofilledFields() {
        const fields = [
            nameInp,
            phoneInp,
            taxAddrInp,
            addrInp,
            statusInp,
            repInp,
            managedByInp,
            businessTypeInp,
            mainBusinessInp,
            emailInp,
            invoiceRecipientInp,
        ].filter(Boolean);

        for (const el of fields) {
            if (isAutofilled(el)) {
                el.value = '';
            }
        }
    }

    async function lookupTax(code) {
        const tpl = `{{ route('admin.customers.taxLookup', ['taxCode' => '__TAX__']) }}`;
        const url = tpl.replace('__TAX__', encodeURIComponent(code));
        const res = await fetch(url, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
        });
        const raw = await res.text().catch(() => '');
        let json = null;
        try {
            json = raw ? JSON.parse(raw) : null;
        } catch (e) {
            json = null;
            if (!res.ok) {
                console.log('taxLookup raw (non-JSON):', raw);
            }
        }

        if (!res.ok) {
            try {
                const durl = url + (url.includes('?') ? '&debug=1' : '?debug=1');
                const dres = await fetch(durl, {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' },
                });
                const draw = await dres.text().catch(() => '');
                let djson = null;
                try {
                    djson = draw ? JSON.parse(draw) : null;
                } catch (e) {
                    djson = null;
                }

                if (djson) {
                    console.log('taxLookup debug:', djson);
                } else {
                    console.log('taxLookup debug raw (non-JSON):', draw);
                }
            } catch (e) {
                // ignore
            }
        }

        return { ok: res.ok, json };
    }

    async function runLookup() {
        const code = normalizeTaxCode(taxInp.value);
        if (code === '' || code.length < 8) {
            lastFilled = '';
            return;
        }
        if (code === lastFilled) return;

        if (lastFilled !== '' && code !== lastFilled) {
            clearAutofilledFields();
            lastFilled = '';
        }

        setLoading(true);

        if (isTaxLike(code)) {
            setHint('Đang kiểm tra danh sách khách hàng…', 'text-muted');
            try {
                const typedDigits = normalizeTaxDigits(code);
                const rows = await lookupCustomersByTax(code);
                const local = pickCustomerExactTax(rows, typedDigits);
                if (local) {
                    taxInp.setCustomValidity('');
                    setAutofill(nameInp, local.name || '');
                    setAutofill(phoneInp, local.phone || '');
                    setAutofill(taxAddrInp, local.tax_address || '');
                    setAutofill(addrInp, local.address || '');
                    setAutofill(statusInp, local.company_status || '');
                    setAutofill(businessTypeInp, local.business_type || '');
                    setAutofill(repInp, local.representative || '');
                    setAutofill(managedByInp, local.managed_by || '');
                    setAutofill(mainBusinessInp, local.main_business || '');
                    setAutofill(emailInp, local.email || '');
                    setAutofill(invoiceRecipientInp, local.invoice_recipient || '');
                    setLoading(false);
                    setHint('Đã điền từ Quản lý khách hàng (CRM).', 'text-success');
                    lastFilled = code;
                    return;
                }
            } catch (e) {
                // ignore, fall through to external lookup
            }
        }

        setHint('Đang tra cứu nguồn bên ngoài…', 'text-muted');
        const { ok, json } = await lookupTax(code);
        if (!ok || !json || json.ok !== true || !json.data) {
            const msg = (json && json.message) ? String(json.message) : 'Không tra cứu được mã số thuế.';
            taxInp.setCustomValidity(msg);
            setLoading(false);
            setHint(msg, 'text-danger');
            lastFilled = '';
            return;
        }

        taxInp.setCustomValidity('');

        setAutofill(nameInp, json.data.name || '');
        setAutofill(phoneInp, json.data.phone || '');
        setAutofill(taxAddrInp, json.data.tax_address || '');
        setAutofill(addrInp, json.data.address || '');
        setAutofill(statusInp, json.data.company_status || '');
        setAutofill(repInp, json.data.representative || '');
        setAutofill(managedByInp, json.data.managed_by || '');
        setAutofill(businessTypeInp, json.data.business_type || '');
        setAutofill(mainBusinessInp, json.data.main_business || '');

        setLoading(false);
        const successMsg = 'Đã lấy thông tin từ tra cứu ngoài (không có trong danh sách khách hàng).';
        setHint(successMsg, 'text-success');
        lastFilled = code;
    }

    [
        nameInp,
        phoneInp,
        taxAddrInp,
        addrInp,
        statusInp,
        repInp,
        managedByInp,
        businessTypeInp,
        mainBusinessInp,
        emailInp,
        invoiceRecipientInp,
    ].filter(Boolean).forEach(function (el) {
        el.addEventListener('input', function () {
            markUserEdited(el);
        });
    });

    function scheduleLookup() {
        if (lookupTimer) clearTimeout(lookupTimer);
        lookupTimer = setTimeout(function () {
            lookupTimer = null;
            runLookup();
        }, 350);
    }

    taxInp.addEventListener('input', function () {
        taxInp.setCustomValidity('');
        const code = normalizeTaxCode(taxInp.value);
        if (code === '' || code.length < 8) {
            if (lookupTimer) {
                clearTimeout(lookupTimer);
                lookupTimer = null;
            }
            lastFilled = '';
            setLoading(false);
            setHint('', null);
            return;
        }
        scheduleLookup();
    });

    taxInp.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (lookupTimer) {
                clearTimeout(lookupTimer);
                lookupTimer = null;
            }
            runLookup();
        }
    });

    taxInp.addEventListener('blur', function () {
        if (lookupTimer) {
            clearTimeout(lookupTimer);
            lookupTimer = null;
        }
        runLookup();
    });

    try {
        const code0 = normalizeTaxCode(taxInp.value);
        const name0 = nameInp ? String(nameInp.value || '').trim() : '';
        if (code0 !== '' && code0.length >= 8 && name0 === '') {
            runLookup();
        }
    } catch (e) {
        // ignore
    }
});
</script>
@endsection
