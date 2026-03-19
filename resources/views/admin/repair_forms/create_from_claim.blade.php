@extends('layouts.admin')

@section('title', 'Tạo phiếu bảo hành từ yêu cầu')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-file-earmark-plus"></i> Tạo phiếu bảo hành từ yêu cầu
            </h1>
            <p class="text-muted">Yêu cầu: <strong>{{ $warrantyClaim->claim_number }}</strong></p>
        </div>
        <a href="{{ route('admin.repair-forms.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <!-- Thông tin yêu cầu bảo hành -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin yêu cầu bảo hành</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Số yêu cầu:</label>
                        <p class="mb-0">{{ $warrantyClaim->claim_number }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Ngày yêu cầu:</label>
                        <p class="mb-0">{{ $warrantyClaim->claim_date->format('d/m/Y') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Mô tả lỗi:</label>
                        <p class="mb-0">{{ $warrantyClaim->issue_description }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    @if($warranty->product)
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Sản phẩm:</label>
                        <p class="mb-0">{{ $warranty->product->name }}</p>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Số seri:</label>
                        <p class="mb-0">{{ $warranty->serial_number }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Khách hàng:</label>
                        <p class="mb-0">{{ $warranty->customer_name }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form tạo phiếu -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin phiếu bảo hành</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.repair-forms.store') }}" method="POST">
                @csrf
                
                <input type="hidden" name="warranty_id" value="{{ $warranty->id }}">
                <input type="hidden" name="warranty_claim_id" value="{{ $warrantyClaim->id }}">
                
                <div class="row">
                    <!-- Thông tin khách hàng -->
                    <div class="col-md-6">
                        <h6 class="font-weight-bold text-primary mb-3">Thông tin khách hàng</h6>
                        
                        <div class="mb-3">
                            <label for="customer_company" class="form-label">Tên công ty <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customer_company" name="customer_company" 
                                   value="{{ old('customer_company', $warranty->customer_name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="contact_person" class="form-label">Người liên hệ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contact_person" name="contact_person" 
                                   value="{{ old('contact_person') }}" required placeholder="Nhập người liên hệ">
                        </div>

                        <div class="mb-3">
                            <label for="contact_phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contact_phone" name="contact_phone" 
                                   value="{{ old('contact_phone', $warranty->customer_phone) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="{{ old('email', $warranty->customer_email) }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="alternate_contact" class="form-label">Người liên hệ khác</label>
                            <input type="text" class="form-control" id="alternate_contact" name="alternate_contact" 
                                   value="{{ old('alternate_contact') }}">
                        </div>

                        <div class="mb-3">
                            <label for="alternate_phone" class="form-label">SĐT khác</label>
                            <input type="text" class="form-control" id="alternate_phone" name="alternate_phone" 
                                   value="{{ old('alternate_phone') }}">
                        </div>

                        <div class="mb-3">
                            <label for="purchase_date" class="form-label">Ngày mua <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="purchase_date" name="purchase_date" 
                                   value="{{ old('purchase_date', $warranty->purchase_date->format('Y-m-d')) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="company_phone" class="form-label">Điện thoại công ty</label>
                            <input type="text" class="form-control" id="company_phone" name="company_phone" 
                                   value="{{ old('company_phone') }}">
                        </div>

                        <div class="mb-3">
                            <label for="fax" class="form-label">Fax</label>
                            <input type="text" class="form-control" id="fax" name="fax" 
                                   value="{{ old('fax') }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Thông tin thiết bị -->
                    <div class="col-md-6">
                        <h6 class="font-weight-bold text-primary mb-3">Thông tin thiết bị</h6>
                        
                        <div class="mb-3">
                            <label for="equipment_name" class="form-label">Tên thiết bị <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="equipment_name" name="equipment_name" 
                                   value="{{ old('equipment_name', $warranty->product->name ?? '') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="error_status" class="form-label">Tình trạng lỗi <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="error_status" name="error_status" rows="3" required>{{ old('error_status', $warrantyClaim->issue_description) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="accessories" class="form-label">Phụ kiện kèm theo</label>
                            <input type="text" class="form-control" id="accessories" name="accessories" value="{{ old('accessories') }}" placeholder="Nhập phụ kiện kèm theo (nếu có)">
                        </div>

                        <div class="mb-3">
                            <label for="serial_numbers" class="form-label">Số seri <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="serial_numbers" name="serial_numbers" 
                                   value="{{ old('serial_numbers', $warranty->serial_number) }}" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="warranty_status" class="form-label">Trạng thái bảo hành <span class="text-danger">*</span></label>
                            <select name="warranty_status" id="warranty_status" class="form-select" required>
                                <option value="under_warranty" {{ $warranty->warranty_status == 'under_warranty' ? 'selected' : '' }}>Còn bảo hành</option>
                                <option value="out_of_warranty" {{ $warranty->warranty_status == 'expired' ? 'selected' : '' }}>Hết bảo hành</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="employee_count" class="form-label">Số nhân viên sử dụng</label>
                            <input type="number" class="form-control" id="employee_count" name="employee_count" 
                                   value="{{ old('employee_count') }}" min="1">
                        </div>

                        <div class="mb-3">
                            <label for="repair_time_required" class="form-label">Thời gian sửa chữa cần thiết</label>
                            <input type="text" class="form-control" id="repair_time_required" name="repair_time_required" 
                                   value="{{ old('repair_time_required') }}" 
                                   placeholder="VD: 5-7 ngày">
                        </div>

                        <div class="mb-3">
                            <label for="estimated_warranty_time" class="form-label">Thời gian bảo hành dự kiến</label>
                            <input type="text" class="form-control" id="estimated_warranty_time" name="estimated_warranty_time" value="{{ old('estimated_warranty_time') }}" placeholder="VD: 30 ngày">
                        </div>

                        <div class="mb-3">
                            <label for="estimated_return_date" class="form-label">Ngày dự kiến trả</label>
                            <input type="date" class="form-control" id="estimated_return_date" name="estimated_return_date" 
                                   value="{{ old('estimated_return_date') }}">
                        </div>

                        <div class="mb-3">
                            <label for="actual_return_date" class="form-label">Ngày trả máy cho khách</label>
                            <input type="date" class="form-control" id="actual_return_date" name="actual_return_date" value="{{ old('actual_return_date') }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="received_by" class="form-label">Người tiếp nhận</label>
                            <input type="text" class="form-control" id="received_by" name="received_by" 
                                   value="{{ old('received_by') }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="received_by_phone" class="form-label">Số điện thoại người tiếp nhận</label>
                            <input type="text" class="form-control" id="received_by_phone" name="received_by_phone" value="{{ old('received_by_phone') }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="mb-3">
                            <label for="received_date" class="form-label">Ngày tiếp nhận <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="received_date" name="received_date" 
                                   value="{{ old('received_date', date('Y-m-d')) }}" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Nháp</option>
                                <option value="submitted" {{ old('status') == 'submitted' ? 'selected' : '' }}>Đã gửi</option>
                                <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>Đang xử lý</option>
                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="notes" class="form-label">Ghi chú</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check"></i> Tạo phiếu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const receivedDateEl = document.getElementById('received_date');
    const estimatedWarrantyTimeEl = document.getElementById('estimated_warranty_time');

    // Set default received date to today
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