@extends('layouts.admin')

@section('title', 'Sửa phiếu mượn hàng')

@section('content')
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
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <div class="fw-bold mb-3">Thông tin khách hàng</div>
                                <div class="row g-3">
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
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Mã số thuế</label>
                                        <input type="text" id="taxCode" name="tax_code" class="form-control" value="{{ old('tax_code', $borrowRequest->tax_code) }}" maxlength="255">
                                    </div>
                                    <div class="col-md-6">
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
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <div class="fw-bold mb-3">Thông tin mượn hàng</div>
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

                <div class="card shadow-sm mt-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between" style="gap: 12px; flex-wrap: wrap;">
                            <div class="fw-bold" style="font-size:1.05rem;">Danh sách hàng mượn</div>
                            <button type="button" class="btn btn-outline-primary" id="addItemRow"><i class="bi bi-plus-circle"></i> Thêm dòng</button>
                        </div>

                        <div class="table-responsive mt-3">
                            <table class="table table-bordered align-middle" id="itemsTable">
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
                                                <td class="text-center stt"></td>
                                                <td><input type="text" name="items[{{ $i }}][item_name]" class="form-control" value="{{ $it['item_name'] ?? '' }}" maxlength="255" list="productDatalist"></td>
                                                <td><input type="text" name="items[{{ $i }}][unit]" class="form-control" value="{{ $it['unit'] ?? '' }}" maxlength="50"></td>
                                                <td><input type="number" step="0.01" name="items[{{ $i }}][quantity]" class="form-control" value="{{ $it['quantity'] ?? '' }}"></td>
                                                <td><input type="text" inputmode="numeric" name="items[{{ $i }}][value]" class="form-control" value="{{ $it['value'] ?? '' }}" autocomplete="off"></td>
                                                <td><input type="text" name="items[{{ $i }}][note]" class="form-control" value="{{ $it['note'] ?? '' }}" maxlength="255"></td>
                                                <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-x"></i></button></td>
                                            </tr>
                                        @endforeach
                                    @else
                                        @foreach($borrowRequest->items as $i => $it)
                                            <tr>
                                                <td class="text-center stt"></td>
                                                <td><input type="text" name="items[{{ $i }}][item_name]" class="form-control" value="{{ $it->item_name }}" maxlength="255" list="productDatalist"></td>
                                                <td><input type="text" name="items[{{ $i }}][unit]" class="form-control" value="{{ $it->unit }}" maxlength="50"></td>
                                                <td><input type="number" step="0.01" name="items[{{ $i }}][quantity]" class="form-control" value="{{ $it->quantity }}"></td>
                                                <td><input type="text" inputmode="numeric" name="items[{{ $i }}][value]" class="form-control" value="{{ $it->value }}" autocomplete="off"></td>
                                                <td><input type="text" name="items[{{ $i }}][note]" class="form-control" value="{{ $it->note }}" maxlength="255"></td>
                                                <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-x"></i></button></td>
                                            </tr>
                                        @endforeach
                                        @if($borrowRequest->items->count() === 0)
                                            <tr>
                                                <td class="text-center stt"></td>
                                                <td><input type="text" name="items[0][item_name]" class="form-control" maxlength="255" list="productDatalist"></td>
                                                <td><input type="text" name="items[0][unit]" class="form-control" maxlength="50"></td>
                                                <td><input type="number" step="0.01" name="items[0][quantity]" class="form-control"></td>
                                                <td><input type="text" inputmode="numeric" name="items[0][value]" class="form-control" autocomplete="off"></td>
                                                <td><input type="text" name="items[0][note]" class="form-control" maxlength="255"></td>
                                                <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-x"></i></button></td>
                                            </tr>
                                        @endif
                                    @endif
                                </tbody>
                            </table>
                            <datalist id="productDatalist"></datalist>
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

    const productDatalist = document.getElementById('productDatalist');
    let productCache = {};
    let productLookupTimer = null;

    async function lookupProducts(q) {
        const url = `{{ route('admin.products.lookup') }}?q=${encodeURIComponent(q)}`;
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        if (!res.ok) return [];
        const data = await res.json();
        return Array.isArray(data) ? data : [];
    }

    function renderProductDatalist(rows) {
        if (!productDatalist) return;
        productDatalist.innerHTML = '';
        productCache = {};
        rows.forEach((p) => {
            if (!p || !p.name) return;
            const key = String(p.name).trim().toLowerCase();
            productCache[key] = p;
            const opt = document.createElement('option');
            opt.value = p.name;
            productDatalist.appendChild(opt);
        });
    }

    function applyProductIfMatched(inputEl) {
        if (!inputEl) return;
        const key = String(inputEl.value || '').trim().toLowerCase();
        const p = productCache[key];
        if (!p) return;

        const tr = inputEl.closest('tr');
        if (!tr) return;

        const qtyInp = tr.querySelector('input[name^="items["][name$="][quantity]"]');
        const valInp = tr.querySelector('input[name^="items["][name$="][value]"]');

        if (qtyInp && (qtyInp.value === '' || qtyInp.value === null)) {
            qtyInp.value = '1';
        }

        if (valInp && (valInp.value === '' || valInp.value === null)) {
            valInp.value = formatVnNumber(String(p.final_price ?? p.price ?? ''));
        }
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
        if (contactName && (!contactName.value || contactName.value.trim() === '')) contactName.value = c.invoice_recipient || '';
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
            if (q.length < 2) return;
            productLookupTimer = setTimeout(async function() {
                const rows = await lookupProducts(q);
                renderProductDatalist(rows);
            }, 250);
        });

        tableBody.addEventListener('change', function(e) {
            const t = e.target;
            if (!t || !t.matches('input[name^="items["][name$="][item_name]"]')) return;
            applyProductIfMatched(t);
        });

        tableBody.addEventListener('blur', function(e) {
            const t = e.target;
            if (!t || !t.matches('input[name^="items["][name$="][item_name]"]')) return;
            applyProductIfMatched(t);
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
                        <td class="text-center stt"></td>
                        <td><input type="text" name="items[0][item_name]" class="form-control" maxlength="255" list="productDatalist"></td>
                        <td><input type="text" name="items[0][unit]" class="form-control" maxlength="50"></td>
                        <td><input type="number" step="0.01" name="items[0][quantity]" class="form-control"></td>
                        <td><input type="text" inputmode="numeric" name="items[0][value]" class="form-control" autocomplete="off"></td>
                        <td><input type="text" name="items[0][note]" class="form-control" maxlength="255"></td>
                        <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-x"></i></button></td>
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
            <td class="text-center stt"></td>
            <td><input type="text" name="items[${idx}][item_name]" class="form-control" maxlength="255" list="productDatalist"></td>
            <td><input type="text" name="items[${idx}][unit]" class="form-control" maxlength="50"></td>
            <td><input type="number" step="0.01" name="items[${idx}][quantity]" class="form-control"></td>
            <td><input type="text" inputmode="numeric" name="items[${idx}][value]" class="form-control" autocomplete="off"></td>
            <td><input type="text" name="items[${idx}][note]" class="form-control" maxlength="255"></td>
            <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-row"><i class="bi bi-x"></i></button></td>
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
