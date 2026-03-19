@extends('layouts.admin')

@section('title', 'Sửa phiếu bảo hành')

@section('content')
<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-pencil"></i> Sửa phiếu bảo hành
            </h1>
            <p class="text-muted">Số phiếu: <strong>{{ $repairForm->form_number }}</strong></p>
        </div>
        <a href="{{ route('admin.repair-forms.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin phiếu bảo hành</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.repair-forms.update', $repairForm) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="serial_numbers" class="form-label fw-bold">Số seri (SN) <span class="text-danger">*</span></label>
                            <input type="text" name="serial_numbers" id="serial_numbers" class="form-control" list="serial_number_list" value="{{ old('serial_numbers', $repairForm->serial_numbers) }}" required>
                            <datalist id="serial_number_list">
                                @foreach($warranties as $warranty)
                                    <option value="{{ $warranty->serial_number }}">
                                @endforeach
                            </datalist>
                        </div>

                        <div class="mb-3">
                            <label for="customer_company" class="form-label fw-bold">Khách hàng <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customer_company" name="customer_company" value="{{ old('customer_company', $repairForm->customer_company) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="contact_person" class="form-label fw-bold">Người liên hệ</label>
                            <input type="text" class="form-control" id="contact_person" name="contact_person" value="{{ old('contact_person', $repairForm->contact_person) }}" placeholder="Nhập người liên hệ">
                        </div>

                        <div class="mb-3">
                            <label for="contact_phone" class="form-label fw-bold">Số điện thoại</label>
                            <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $repairForm->contact_phone) }}">
                        </div>

                        <div class="mb-3">
                            <label for="purchase_date" class="form-label fw-bold">Ngày mua hàng</label>
                            <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="{{ old('purchase_date', $repairForm->purchase_date ? $repairForm->purchase_date->format('Y-m-d') : '') }}">
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="purchase_date_unknown" name="purchase_date_unknown" {{ old('purchase_date_unknown', $repairForm->purchase_date ? 0 : 1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="purchase_date_unknown">Không</label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="equipment_name" class="form-label fw-bold">Tên thiết bị <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="equipment_name" name="equipment_name" value="{{ old('equipment_name', $repairForm->equipment_name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="received_date" class="form-label fw-bold">Ngày tiếp nhận <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="received_date" name="received_date" value="{{ old('received_date', $repairForm->received_date ? $repairForm->received_date->format('Y-m-d') : '') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="warranty_status" class="form-label fw-bold">Trạng thái bảo hành</label>
                            <select name="warranty_status" id="warranty_status" class="form-select">
                                <option value="">Tự động</option>
                                <option value="under_warranty" {{ old('warranty_status', $repairForm->warranty_status) == 'under_warranty' ? 'selected' : '' }}>Còn bảo hành</option>
                                <option value="out_of_warranty" {{ old('warranty_status', $repairForm->warranty_status) == 'out_of_warranty' ? 'selected' : '' }}>Hết bảo hành</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="error_status" class="form-label fw-bold">Tình trạng lỗi <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="error_status" name="error_status" rows="3" required>{{ old('error_status', $repairForm->error_status) }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="accessories" class="form-label fw-bold">Phụ kiện kèm theo</label>
                            <input type="text" class="form-control" id="accessories" name="accessories" value="{{ old('accessories', $repairForm->accessories) }}" placeholder="Nhập phụ kiện kèm theo (nếu có)">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="repair_time_required" class="form-label fw-bold">Thời gian sửa chữa cần thiết</label>
                            <input type="text" class="form-control" id="repair_time_required" name="repair_time_required" value="{{ old('repair_time_required', $repairForm->repair_time_required) }}" placeholder="VD: 3-5 ngày">
                        </div>

                        <div class="mb-3">
                            <label for="estimated_warranty_time" class="form-label fw-bold">Thời gian bảo hành dự kiến</label>
                            <input type="text" class="form-control" id="estimated_warranty_time" name="estimated_warranty_time" value="{{ old('estimated_warranty_time', $repairForm->estimated_warranty_time) }}" placeholder="VD: 30 ngày">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="estimated_return_date" class="form-label fw-bold">Ngày trả máy (dự kiến)</label>
                            <input type="date" class="form-control" id="estimated_return_date" name="estimated_return_date" value="{{ old('estimated_return_date', $repairForm->estimated_return_date ? $repairForm->estimated_return_date->format('Y-m-d') : '') }}">
                        </div>

                        <div class="mb-3">
                            <label for="actual_return_date" class="form-label fw-bold">Ngày trả máy cho khách</label>
                            <input type="date" class="form-control" id="actual_return_date" name="actual_return_date" value="{{ old('actual_return_date', $repairForm->actual_return_date ? $repairForm->actual_return_date->format('Y-m-d') : '') }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <hr>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <h6 class="fw-bold text-primary mb-3">Thông tin bàn giao (Phiếu trả)</h6>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="handed_over_by" class="form-label fw-bold">Người bàn giao</label>
                            <input type="text" class="form-control" id="handed_over_by" name="handed_over_by" value="{{ old('handed_over_by', $repairForm->handed_over_by) }}" placeholder="Nhập người bàn giao">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="handed_over_by_phone" class="form-label fw-bold">Số điện thoại người bàn giao</label>
                            <input type="text" class="form-control" id="handed_over_by_phone" name="handed_over_by_phone" value="{{ old('handed_over_by_phone', $repairForm->handed_over_by_phone) }}" placeholder="Nhập số điện thoại người bàn giao">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="handover_repair_info" class="form-label fw-bold">Thông tin đã sửa chữa</label>
                            <textarea class="form-control" id="handover_repair_info" name="handover_repair_info" rows="3">{{ old('handover_repair_info', $repairForm->handover_repair_info) }}</textarea>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="handover_check_info" class="form-label fw-bold">Thời gian bảo hành/sửa chữa</label>
                            <textarea class="form-control" id="handover_check_info" name="handover_check_info" rows="3">{{ old('handover_check_info', $repairForm->handover_check_info) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="received_by" class="form-label fw-bold">Người tiếp nhận</label>
                            <input type="text" class="form-control" id="received_by" name="received_by" value="{{ old('received_by', $repairForm->received_by) }}" placeholder="Nhập người tiếp nhận">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="received_by_phone" class="form-label fw-bold">Số điện thoại người tiếp nhận</label>
                            <input type="text" class="form-control" id="received_by_phone" name="received_by_phone" value="{{ old('received_by_phone', $repairForm->received_by_phone) }}" placeholder="Nhập số điện thoại người tiếp nhận">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="service_representative" class="form-label fw-bold">Phụ trách dịch vụ khách hàng</label>
                            <input type="text" class="form-control" id="service_representative" name="service_representative" value="{{ old('service_representative', $repairForm->service_representative) }}" placeholder="Nhập tên phụ trách dịch vụ khách hàng">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="status" class="form-label fw-bold">Trạng thái <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="not_returned" {{ old('status', $repairForm->status) == 'not_returned' ? 'selected' : '' }}>Chưa gửi trả</option>
                                <option value="returned" {{ old('status', $repairForm->status) == 'returned' ? 'selected' : '' }}>Đã gửi trả</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="notes" class="form-label fw-bold">Ghi chú</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $repairForm->notes) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check"></i> Cập nhật phiếu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const warrantyMap = @json($warrantyMap);

    const purchaseDateUnknownEl = document.getElementById('purchase_date_unknown');
    const purchaseDateEl = document.getElementById('purchase_date');
    const serialNumbersEl = document.getElementById('serial_numbers');
    const customerCompanyEl = document.getElementById('customer_company');
    const contactPhoneEl = document.getElementById('contact_phone');
    const equipmentNameEl = document.getElementById('equipment_name');
    const receivedDateEl = document.getElementById('received_date');
    const estimatedWarrantyTimeEl = document.getElementById('estimated_warranty_time');
    const estimatedReturnDateEl = document.getElementById('estimated_return_date');

    function syncPurchaseDateUnknown() {
        const unknown = !!(purchaseDateUnknownEl && purchaseDateUnknownEl.checked);
        if (purchaseDateEl) {
            purchaseDateEl.disabled = unknown;
            if (unknown) {
                purchaseDateEl.value = '';
            }
        }
    }

    if (purchaseDateUnknownEl) {
        purchaseDateUnknownEl.addEventListener('change', syncPurchaseDateUnknown);
    }
    syncPurchaseDateUnknown();

    function tryFillFromSerial() {
        const sn = (serialNumbersEl && serialNumbersEl.value ? serialNumbersEl.value : '').trim();
        const info = warrantyMap ? warrantyMap[sn] : null;
        if (!info) {
            return;
        }

        if (customerCompanyEl && !customerCompanyEl.value) {
            customerCompanyEl.value = info.customer || '';
        }
        if (contactPhoneEl && !contactPhoneEl.value) {
            contactPhoneEl.value = info.phone || '';
        }
        if (equipmentNameEl && !equipmentNameEl.value) {
            equipmentNameEl.value = info.product || '';
        }
    }

    if (serialNumbersEl) {
        serialNumbersEl.addEventListener('change', tryFillFromSerial);
        serialNumbersEl.addEventListener('keyup', tryFillFromSerial);
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

    if (receivedDateEl) {
        receivedDateEl.addEventListener('change', function () {
            syncEstimatedWarrantyTime(true);
        });
    }
    syncEstimatedWarrantyTime(false);
});
</script>
@endsection