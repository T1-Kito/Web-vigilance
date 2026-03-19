@extends('layouts.admin')

@section('title', 'Tạo phiếu bảo hành')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-file-earmark-plus"></i> Tạo phiếu bảo hành
            </h1>
            <p class="text-muted">Tạo phiếu bảo hành - sửa chữa mới</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.repair-forms.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin phiếu bảo hành</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.repair-forms.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="serial_number" class="form-label fw-bold">Số seri (SN) <span class="text-danger">*</span></label>
                            <input type="text" name="serial_number" id="serial_number" class="form-control" list="serial_number_list" required>
                            <datalist id="serial_number_list">
                                @foreach($warranties as $warranty)
                                    <option value="{{ $warranty->serial_number }}">
                                @endforeach
                            </datalist>
                            <div id="serial_autofill_hint" class="form-text" style="display:none"></div>
                            @error('serial_number')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="customer_company" class="form-label fw-bold">Khách hàng <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customer_company" name="customer_company" required>
                        </div>

                        <div class="mb-3">
                            <label for="contact_phone" class="form-label fw-bold">Số điện thoại</label>
                            <input type="text" class="form-control" id="contact_phone" name="contact_phone">
                        </div>

                        <div class="mb-3">
                            <label for="purchase_date" class="form-label fw-bold">Ngày mua hàng</label>
                            <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="{{ old('purchase_date') }}">
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="purchase_date_unknown" name="purchase_date_unknown" {{ old('purchase_date_unknown') ? 'checked' : '' }}>
                            <label class="form-check-label" for="purchase_date_unknown">Không</label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="equipment_name" class="form-label fw-bold">Tên thiết bị <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="equipment_name" name="equipment_name" required>
                        </div>

                        <div class="mb-3">
                            <label for="received_date" class="form-label fw-bold">Ngày tiếp nhận <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="received_date" name="received_date" required>
                        </div>

                        <div class="mb-3">
                            <label for="warranty_status" class="form-label fw-bold">Trạng thái bảo hành</label>
                            <select name="warranty_status" id="warranty_status" class="form-select">
                                <option value="">Tự động</option>
                                <option value="under_warranty">Còn bảo hành</option>
                                <option value="out_of_warranty">Hết bảo hành</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="error_status" class="form-label fw-bold">Tình trạng lỗi <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="error_status" name="error_status" rows="3" required></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="accessories" class="form-label fw-bold">Phụ kiện kèm theo</label>
                            <input type="text" class="form-control" id="accessories" name="accessories" value="{{ old('accessories') }}" placeholder="Nhập phụ kiện kèm theo (nếu có)">
                        </div>

                        <div class="mb-3">
                            <label for="repair_time_required" class="form-label fw-bold">Thời gian sửa chữa cần thiết</label>
                            <input type="text" class="form-control" id="repair_time_required" name="repair_time_required" value="{{ old('repair_time_required', '3-7 ngày') }}" placeholder="VD: 3-5 ngày">
                        </div>

                        <div class="mb-3">
                            <label for="estimated_warranty_time" class="form-label fw-bold">Thời gian bảo hành dự kiến</label>
                            <input type="text" class="form-control" id="estimated_warranty_time" name="estimated_warranty_time" value="{{ old('estimated_warranty_time') }}" placeholder="VD: 30 ngày">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="estimated_return_date" class="form-label fw-bold">Ngày trả máy (dự kiến)</label>
                            <input type="date" class="form-control" id="estimated_return_date" name="estimated_return_date" value="{{ old('estimated_return_date') }}">
                        </div>

                        <div class="mb-3">
                            <label for="actual_return_date" class="form-label fw-bold">Ngày trả máy cho khách</label>
                            <input type="date" class="form-control" id="actual_return_date" name="actual_return_date" value="{{ old('actual_return_date') }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="received_by" class="form-label fw-bold">Người tiếp nhận</label>
                            <input type="text" class="form-control" id="received_by" name="received_by" value="{{ old('received_by') }}" placeholder="Nhập người tiếp nhận">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="received_by_phone" class="form-label fw-bold">Số điện thoại người tiếp nhận</label>
                            <input type="text" class="form-control" id="received_by_phone" name="received_by_phone" value="{{ old('received_by_phone') }}" placeholder="Nhập số điện thoại người tiếp nhận">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="service_representative" class="form-label fw-bold">Phụ trách dịch vụ khách hàng</label>
                            <input type="text" class="form-control" id="service_representative" name="service_representative" value="{{ old('service_representative') }}" placeholder="Nhập tên phụ trách dịch vụ khách hàng">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label fw-bold">Ghi chú</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.repair-forms.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Hủy
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Tạo phiếu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const warrantyMap = @json($warrantyMap);
    const normalizeSn = (value) => (value || '').toString().trim().toUpperCase();
    const normalizedWarrantyMap = {};
    Object.keys(warrantyMap || {}).forEach((key) => {
        normalizedWarrantyMap[normalizeSn(key)] = warrantyMap[key];
    });

    const serialEl = document.getElementById('serial_number');
    const customerEl = document.getElementById('customer_company');
    const phoneEl = document.getElementById('contact_phone');
    const equipmentEl = document.getElementById('equipment_name');
    const purchaseDateEl = document.getElementById('purchase_date');
    const purchaseUnknownEl = document.getElementById('purchase_date_unknown');
    const warrantyStatusEl = document.getElementById('warranty_status');
    const receivedDateEl = document.getElementById('received_date');
    const estimatedWarrantyTimeEl = document.getElementById('estimated_warranty_time');
    const estimatedReturnDateEl = document.getElementById('estimated_return_date');
    const hintEl = document.getElementById('serial_autofill_hint');

    const AUTO_KEY = 'data-autofilled';
    let lastSn = null;

    function markAutofilled(el) {
        if (!el) return;
        el.setAttribute(AUTO_KEY, '1');
    }

    function unmarkAutofilled(el) {
        if (!el) return;
        el.removeAttribute(AUTO_KEY);
    }

    function isAutofilled(el) {
        if (!el) return false;
        return el.getAttribute(AUTO_KEY) === '1';
    }

    function attachUserEditTracker(el) {
        if (!el) return;
        el.addEventListener('input', function () {
            unmarkAutofilled(el);
        });
    }

    attachUserEditTracker(customerEl);
    attachUserEditTracker(phoneEl);
    attachUserEditTracker(equipmentEl);
    attachUserEditTracker(purchaseDateEl);
    attachUserEditTracker(warrantyStatusEl);

    function syncPurchaseDateUnknown() {
        const unknown = !!(purchaseUnknownEl && purchaseUnknownEl.checked);
        if (purchaseDateEl) {
            purchaseDateEl.disabled = unknown;
            if (unknown) {
                purchaseDateEl.value = '';
            }
        }
    }

    if (purchaseUnknownEl) {
        purchaseUnknownEl.addEventListener('change', syncPurchaseDateUnknown);
    }
    syncPurchaseDateUnknown();

    function tryFillFromSerial() {
        const sn = normalizeSn(serialEl ? serialEl.value : '');
        const info = normalizedWarrantyMap[sn];

        const snChanged = lastSn !== sn;
        lastSn = sn;

        if (!info) {
            if (hintEl) {
                hintEl.textContent = sn ? 'Không tìm thấy SN trong hệ thống bảo hành.' : '';
                hintEl.classList.remove('text-success', 'text-muted');
                hintEl.classList.add('text-danger');
                hintEl.style.display = sn ? '' : 'none';
            }
            if (snChanged) {
                if (customerEl && isAutofilled(customerEl)) customerEl.value = '';
                if (phoneEl && isAutofilled(phoneEl)) phoneEl.value = '';
                if (equipmentEl && isAutofilled(equipmentEl)) equipmentEl.value = '';
                if (purchaseDateEl && isAutofilled(purchaseDateEl)) purchaseDateEl.value = '';
                if (warrantyStatusEl && isAutofilled(warrantyStatusEl)) warrantyStatusEl.value = '';

                unmarkAutofilled(customerEl);
                unmarkAutofilled(phoneEl);
                unmarkAutofilled(equipmentEl);
                unmarkAutofilled(purchaseDateEl);
                unmarkAutofilled(warrantyStatusEl);
            }
            return;
        }

        if (hintEl) {
            hintEl.textContent = '';
            hintEl.classList.remove('text-success', 'text-danger', 'text-muted');
            hintEl.style.display = 'none';
        }

        const canOverwrite = (el) => !el || !el.value || isAutofilled(el);

        if (customerEl && canOverwrite(customerEl)) {
            customerEl.value = info.customer || '';
            markAutofilled(customerEl);
        }
        if (phoneEl && canOverwrite(phoneEl)) {
            phoneEl.value = info.phone || '';
            markAutofilled(phoneEl);
        }
        if (equipmentEl && canOverwrite(equipmentEl)) {
            equipmentEl.value = info.product || '';
            markAutofilled(equipmentEl);
        }

        const purchaseUnknown = !!(purchaseUnknownEl && purchaseUnknownEl.checked);
        if (!purchaseUnknown && purchaseDateEl && info.purchase_date && canOverwrite(purchaseDateEl)) {
            purchaseDateEl.value = info.purchase_date;
            markAutofilled(purchaseDateEl);
        }

        if (warrantyStatusEl && info.warranty_status && canOverwrite(warrantyStatusEl)) {
            warrantyStatusEl.value = info.warranty_status;
            markAutofilled(warrantyStatusEl);
        }
    }

    if (serialEl) {
        serialEl.addEventListener('change', tryFillFromSerial);
        serialEl.addEventListener('keyup', tryFillFromSerial);
        serialEl.addEventListener('blur', tryFillFromSerial);
    }

    if (receivedDateEl && !receivedDateEl.value) {
        receivedDateEl.value = new Date().toISOString().split('T')[0];
    }

    function pad2(n) {
        return String(n).padStart(2, '0');
    }

    function formatVnDate(date) {
        return pad2(date.getDate()) + '/' + pad2(date.getMonth() + 1) + '/' + date.getFullYear();
    }

    function parseIsoDate(value) {
        if (!value) return null;
        const m = /^([0-9]{4})-([0-9]{2})-([0-9]{2})$/.exec(value);
        if (!m) return null;
        const y = Number(m[1]);
        const mo = Number(m[2]);
        const d = Number(m[3]);
        const dt = new Date(y, mo - 1, d);
        if (Number.isNaN(dt.getTime())) return null;
        return dt;
    }

    function syncEstimatedWarrantyTime(force) {
        if (!receivedDateEl || !estimatedWarrantyTimeEl) return;
        if (!force && estimatedWarrantyTimeEl.value) return;

        const received = parseIsoDate(receivedDateEl.value);
        if (!received) return;

        const target = new Date(received.getTime() + (7 * 24 * 60 * 60 * 1000));
        estimatedWarrantyTimeEl.value = formatVnDate(target);

        if (estimatedReturnDateEl && (force || !estimatedReturnDateEl.value)) {
            estimatedReturnDateEl.value = target.toISOString().split('T')[0];
        }
    }

    if (estimatedWarrantyTimeEl) {
        estimatedWarrantyTimeEl.addEventListener('input', function () {
            unmarkAutofilled(estimatedWarrantyTimeEl);
        });
    }

    if (receivedDateEl) {
        receivedDateEl.addEventListener('change', function () {
            syncEstimatedWarrantyTime(true);
        });
    }
    syncEstimatedWarrantyTime(false);
});
</script>
@endsection