@extends('layouts.admin')

@section('title', 'Sửa đơn mua hàng')

@section('content')
<div class="content-card" style="padding:20px;">
    <style>
        .po-card { border:1px solid #e5e7eb; border-radius:12px; padding:16px; background:#f8fafc; }
        .po-card h6 { margin-bottom:12px; font-weight:700; color:#1f2937; }
        #itemsTable { table-layout: fixed; min-width: 1280px; }
        #itemsTable th { white-space: nowrap; font-size: 13px; }
        #itemsTable td { vertical-align: middle; }
        #itemsTable th:nth-child(1), #itemsTable td:nth-child(1) { width: 48px; }
        #itemsTable th:nth-child(2), #itemsTable td:nth-child(2) { width: 120px; }
        #itemsTable th:nth-child(3), #itemsTable td:nth-child(3) { width: 300px; }
        #itemsTable th:nth-child(4), #itemsTable td:nth-child(4) { width: 90px; }
        #itemsTable th:nth-child(5), #itemsTable td:nth-child(5) { width: 100px; }
        #itemsTable th:nth-child(6), #itemsTable td:nth-child(6) { width: 95px; }
        #itemsTable th:nth-child(7), #itemsTable td:nth-child(7) { width: 120px; }
        #itemsTable th:nth-child(8), #itemsTable td:nth-child(8) { width: 110px; }
        #itemsTable th:nth-child(9), #itemsTable td:nth-child(9) { width: 130px; }
        #itemsTable th:nth-child(10), #itemsTable td:nth-child(10) { width: 50px; }
        #itemsTable input[name$="[item_name]"] { min-width: 280px; }
        #itemsTable .price-input, #itemsTable .line-total { min-width: 110px; }
        #itemsTable .qty { min-width: 78px; }
        #itemsTable .tax-input { min-width: 90px; }
    </style>

    <form method="POST" action="{{ route('admin.purchase-orders.update', $order) }}" id="poForm">
        @csrf
        @method('PUT')

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="po-card">
                    <h6>Thông tin nhà cung cấp</h6>
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label fw-bold">Mã số thuế</label>
                            <input class="form-control" id="supplier_tax_code" name="supplier_tax_code" value="{{ old('supplier_tax_code', $order->supplier_tax_code) }}" placeholder="Nhập MST NCC">
                            <small id="taxLookupHint" class="text-muted">Nhập MST để tự điền từ Quản lý khách hàng.</small>
                        </div>
                        <input type="hidden" id="supplier_code" name="supplier_code" value="{{ old('supplier_code', $order->supplier_code) }}">
                        <div class="col-12">
                            <label class="form-label fw-bold">Tên nhà cung cấp *</label>
                            <input class="form-control" id="supplier_name" name="supplier_name" value="{{ old('supplier_name', $order->supplier_name) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Địa chỉ nhà cung cấp</label>
                            <input class="form-control" id="supplier_address" name="supplier_address" value="{{ old('supplier_address', $order->supplier_address) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Người liên hệ</label>
                            <input class="form-control" id="supplier_contact_name" name="supplier_contact_name" value="{{ old('supplier_contact_name', $order->supplier_contact_name) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Số điện thoại người liên hệ</label>
                            <input class="form-control" id="supplier_contact_phone" name="supplier_contact_phone" value="{{ old('supplier_contact_phone', $order->supplier_contact_phone) }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="po-card">
                    <h6>Thông tin người đề nghị</h6>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ngày giao hàng</label>
                            <input type="date" class="form-control" name="delivery_date" value="{{ old('delivery_date', optional($order->delivery_date)->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nhân viên mua hàng</label>
                            <select class="form-select" name="buyer_name">
                                <option value="">-- Chọn nhân viên --</option>
                                <option value="Nguyễn Hỷ Trúc Bình" {{ old('buyer_name', $order->buyer_name) === 'Nguyễn Hỷ Trúc Bình' ? 'selected' : '' }}>Nguyễn Hỷ Trúc Bình</option>
                                <option value="Nguyễn Thị Hồng Vi" {{ old('buyer_name', $order->buyer_name) === 'Nguyễn Thị Hồng Vi' ? 'selected' : '' }}>Nguyễn Thị Hồng Vi</option>
                                <option value="Bùi Nguyễn Tường Vy" {{ old('buyer_name', $order->buyer_name) === 'Bùi Nguyễn Tường Vy' ? 'selected' : '' }}>Bùi Nguyễn Tường Vy</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Chức vụ</label>
                            <select class="form-select" name="buyer_position">
                                <option value="">-- Chọn chức vụ --</option>
                                <option value="Thủ kho" {{ old('buyer_position', $order->buyer_position) === 'Thủ kho' ? 'selected' : '' }}>Thủ kho</option>
                                <option value="Kinh doanh" {{ old('buyer_position', $order->buyer_position) === 'Kinh doanh' ? 'selected' : '' }}>Kinh doanh</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Số ngày được nợ (nếu có)</label>
                            <input type="number" min="0" class="form-control" name="credit_days" value="{{ old('credit_days', (int) ($order->credit_days ?? 0)) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Loại tiền thanh toán</label>
                            <input class="form-control" name="payment_currency" value="{{ old('payment_currency', $order->payment_currency ?: 'VND') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Trạng thái</label>
                            <select class="form-select" name="order_type">
                                <option value="order" {{ old('order_type', $order->order_type ?: 'order') === 'order' ? 'selected' : '' }}>Đặt hàng</option>
                                <option value="return" {{ old('order_type', $order->order_type ?: 'order') === 'return' ? 'selected' : '' }}>Trả hàng</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Giao tại</label>
                            <input class="form-control" name="delivery_location" value="{{ old('delivery_location', $order->delivery_location) }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0 fw-bold">Chi tiết hàng hóa</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addLine">+ Thêm dòng</button>
        </div>
        <div style="overflow-x:auto;">
            <table class="table table-bordered align-middle" id="itemsTable">
                <thead>
                <tr>
                    <th>STT</th><th>Số seri</th><th>Tên hàng *</th><th>ĐVT</th><th>Bảo hành</th><th>Số lượng *</th><th>Đơn giá</th><th>Thuế GTGT (%)</th><th>Thành tiền</th><th>Xóa</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary" type="submit">Cập nhật đơn</button>
            <a href="{{ route('admin.purchase-orders.show', $order) }}" class="btn btn-secondary">Hủy</a>
        </div>
    </form>
</div>

@php
    $poInitialItems = old('items');
    if (!is_array($poInitialItems)) {
        $poInitialItems = $order->items->map(function ($it) {
            return [
                'serial_number' => $it->serial_number,
                'item_name' => $it->item_name,
                'unit' => $it->unit,
                'warranty_period' => $it->warranty_period,
                'quantity' => (float) ($it->quantity ?? 0),
                'unit_price' => (float) ($it->unit_price ?? 0),
                'tax_percent' => (float) ($it->tax_percent ?? 0),
            ];
        })->values()->toArray();
    }
@endphp

<script>
document.addEventListener('DOMContentLoaded', function () {
    const PRODUCTS_LOOKUP = @json(route('admin.products.lookup'));
    const productCache = {};
    const initialItems = @json($poInitialItems);

    function esc(s) {
        return String(s ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    async function lookupProducts(q) {
        const res = await fetch(PRODUCTS_LOOKUP + '?q=' + encodeURIComponent(q), {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        });
        if (!res.ok) return [];
        const data = await res.json();
        return Array.isArray(data) ? data : [];
    }

    function cacheProductRows(rows) {
        rows.forEach(function (p) {
            if (!p || !p.name) return;
            productCache[String(p.name).trim().toLowerCase()] = p;
        });
    }

    function formatVnNumberInput(raw) {
        const digits = String(raw || '').replace(/[^\d]/g, '');
        if (!digits) return '';
        return Number(digits).toLocaleString('vi-VN');
    }

    function parseNumberInput(raw) {
        const s = String(raw || '').trim();
        if (!s) return 0;
        const hasDot = s.includes('.');
        const hasComma = s.includes(',');
        let normalized = s.replace(/\s+/g, '');
        if (hasDot && hasComma) normalized = normalized.replace(/\./g, '').replace(',', '.');
        else if (hasDot && !hasComma) normalized = normalized.replace(/\./g, '');
        else if (!hasDot && hasComma) normalized = normalized.replace(',', '.');
        normalized = normalized.replace(/[^\d.-]/g, '');
        const n = Number(normalized);
        return Number.isFinite(n) ? n : 0;
    }

    function calcLineTotal(tr) {
        if (!tr) return;
        const qty = parseNumberInput(tr.querySelector('.qty')?.value);
        const price = parseNumberInput(tr.querySelector('.price-input')?.value);
        const tax = parseNumberInput(tr.querySelector('.tax-input')?.value);
        const lineTotalEl = tr.querySelector('.line-total');
        const base = qty * price;
        const total = base + (base * tax / 100);
        if (lineTotalEl) lineTotalEl.value = Number(total || 0).toLocaleString('vi-VN');
    }

    const tbody = document.querySelector('#itemsTable tbody');
    const addBtn = document.getElementById('addLine');

    function row(i, data = {}) {
        const serial = esc(data.serial_number || '');
        const itemName = esc(data.item_name || '');
        const unit = esc(data.unit || 'Cái');
        const warranty = esc(data.warranty_period || '12 tháng');
        const qty = Number(data.quantity || 1);
        const unitPrice = formatVnNumberInput(String(Math.round(Number(data.unit_price || 0))));
        const tax = Number(data.tax_percent ?? 8);

        return `<tr>
            <td class="text-center">${i+1}</td>
            <td><input class="form-control" name="items[${i}][serial_number]" placeholder="SN..." value="${serial}"></td>
            <td><input class="form-control item-name" list="poProductOptions" name="items[${i}][item_name]" required placeholder="Gõ để lấy từ sản phẩm" value="${itemName}"></td>
            <td><input class="form-control" list="poUnitOptions" name="items[${i}][unit]" value="${unit}" placeholder="Cái / Máy / khác"></td>
            <td>
                <select class="form-select" name="items[${i}][warranty_period]">
                    <option value="12 tháng" ${warranty === '12 tháng' ? 'selected' : ''}>12 tháng</option>
                    <option value="6 tháng" ${warranty === '6 tháng' ? 'selected' : ''}>6 tháng</option>
                </select>
            </td>
            <td><input type="number" step="0.01" min="0.01" class="form-control qty" name="items[${i}][quantity]" value="${qty}" required></td>
            <td><input class="form-control price-input" name="items[${i}][unit_price]" value="${unitPrice}"></td>
            <td><input type="number" step="0.01" min="0" class="form-control tax-input" name="items[${i}][tax_percent]" value="${tax}"></td>
            <td><input class="form-control line-total" value="0" readonly tabindex="-1"></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger del">X</button></td>
        </tr>`;
    }

    function reindex(){
        [...tbody.querySelectorAll('tr')].forEach((tr, i) => {
            tr.cells[0].textContent = i + 1;
            tr.querySelectorAll('input[name^="items["], select[name^="items["]').forEach(inp => {
                inp.name = inp.name.replace(/items\[\d+\]/, `items[${i}]`);
            });
            calcLineTotal(tr);
        });
    }

    function add(data = {}) {
        tbody.insertAdjacentHTML('beforeend', row(tbody.children.length, data));
        calcLineTotal(tbody.lastElementChild);
    }

    if (Array.isArray(initialItems) && initialItems.length) initialItems.forEach(it => add(it));
    else add();

    addBtn.addEventListener('click', () => add());

    const datalist = document.createElement('datalist');
    datalist.id = 'poProductOptions';
    document.body.appendChild(datalist);
    const unitList = document.createElement('datalist');
    unitList.id = 'poUnitOptions';
    unitList.innerHTML = '<option value="Cái"></option><option value="Máy"></option>';
    document.body.appendChild(unitList);

    tbody.addEventListener('input', e => {
        const tr = e.target.closest('tr');
        if (!tr) return;
        if (e.target.matches('.price-input')) e.target.value = formatVnNumberInput(e.target.value);
        if (e.target.matches('.qty, .price-input, .tax-input')) calcLineTotal(tr);
    });

    tbody.addEventListener('focusout', async e => {
        const input = e.target;
        if (!input.matches('.item-name')) return;
        const q = String(input.value || '').trim();
        if (q.length < 2) return;
        const rows = await lookupProducts(q);
        cacheProductRows(rows);
        datalist.innerHTML = rows.map(p => `<option value="${p.name.replace(/"/g, '&quot;')}"></option>`).join('');
        const p = productCache[q.toLowerCase()];
        if (!p) return;
        const tr = input.closest('tr');
        const priceInput = tr.querySelector('.price-input');
        const serialInput = tr.querySelector('input[name$="[serial_number]"]');
        const warrantySel = tr.querySelector('select[name$="[warranty_period]"]');
        if (priceInput) priceInput.value = formatVnNumberInput(String(Math.round(Number(p.final_price || p.price || 0))));
        if (serialInput && !serialInput.value && p.serial_number) serialInput.value = p.serial_number;
        if (warrantySel) warrantySel.value = '12 tháng';
        calcLineTotal(tr);
    });

    tbody.addEventListener('click', e => {
        if (!e.target.classList.contains('del')) return;
        e.target.closest('tr').remove();
        reindex();
    });

    const form = document.getElementById('poForm');
    if (form) {
        form.addEventListener('submit', function () {
            form.querySelectorAll('.qty, .price-input, .tax-input').forEach(function (inp) {
                inp.value = String(parseNumberInput(inp.value));
                if (inp.value === '' || inp.value === 'NaN') inp.value = '0';
            });
        });
    }
});
</script>
@endsection