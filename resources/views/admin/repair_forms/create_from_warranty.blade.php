@extends('layouts.admin')

@section('title', 'Tạo phiếu bảo hành từ bảo hành')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-file-earmark-plus"></i> Tạo phiếu bảo hành
            </h1>
            <p class="text-muted">Tạo phiếu từ bảo hành: <strong>{{ $warranty->serial_number }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.warranties.show', $warranty) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- Thông tin bảo hành -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông tin bảo hành</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Số seri:</label>
                        <p class="mb-0 fs-5">{{ $warranty->serial_number }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Sản phẩm:</label>
                        @if($warranty->product)
                        <div class="d-flex align-items-center">
                            @if($warranty->product->image)
                                <img src="{{ asset('images/products/' . $warranty->product->image) }}" 
                                     alt="{{ $warranty->product->name }}" 
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; margin-right: 10px;">
                            @endif
                            <div>
                                <p class="mb-0 fs-5">{{ $warranty->product->name }}</p>
                                <small class="text-muted">{{ $warranty->product->category->name ?? '' }}</small>
                            </div>
                        </div>
                        @else
                        <p class="mb-0 text-muted">-</p>
                        @endif
                    </div>
                    @if($warranty->customer_name)
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Khách hàng:</label>
                        <p class="mb-0 fs-5">{{ $warranty->customer_name }}</p>
                    </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Số điện thoại:</label>
                        <p class="mb-0 fs-5">{{ $warranty->customer_phone }}</p>
                    </div>
                    @if($warranty->customer_email)
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Email:</label>
                        <p class="mb-0 fs-5">{{ $warranty->customer_email }}</p>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Ngày mua:</label>
                        <p class="mb-0 fs-5">{{ $warranty->purchase_date->format('d/m/Y') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Trạng thái bảo hành:</label>
                        <span class="badge bg-{{ $warranty->warranty_status_color }} fs-6">
                            {{ $warranty->warranty_status_text }}
                        </span>
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
                
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-3 text-primary">Thông tin khách hàng</h5>
                        
                        <div class="mb-3">
                            <label for="customer_company" class="form-label fw-bold">Tên công ty <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customer_company" name="customer_company" value="{{ $warranty->customer_name ?? '' }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="contact_person" class="form-label fw-bold">Người liên hệ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contact_person" name="contact_person" value="{{ old('contact_person') }}" required placeholder="Nhập người liên hệ">
                        </div>

                        <div class="mb-3">
                            <label for="contact_phone" class="form-label fw-bold">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contact_phone" name="contact_phone" value="{{ $warranty->customer_phone }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ $warranty->customer_email }}">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="alternate_contact" class="form-label fw-bold">Người liên hệ khác</label>
                            <input type="text" class="form-control" id="alternate_contact" name="alternate_contact">
                        </div>

                        <div class="mb-3">
                            <label for="alternate_phone" class="form-label fw-bold">SĐT khác</label>
                            <input type="text" class="form-control" id="alternate_phone" name="alternate_phone">
                        </div>

                        <div class="mb-3">
                            <label for="company_phone" class="form-label fw-bold">Điện thoại công ty</label>
                            <input type="text" class="form-control" id="company_phone" name="company_phone">
                        </div>


                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-3 text-primary">Thông tin thiết bị</h5>
                        
                        <div class="mb-3">
                            <label for="equipment_name" class="form-label fw-bold">Tên thiết bị <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="equipment_name" name="equipment_name" value="{{ $warranty->product->name ?? '' }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="error_status" class="form-label fw-bold">Tình trạng lỗi <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="error_status" name="error_status" rows="3" required placeholder="Mô tả tình trạng lỗi của thiết bị..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="accessories" class="form-label fw-bold">Phụ kiện kèm theo</label>
                            <input type="text" class="form-control" id="accessories" name="accessories" placeholder="Nhập phụ kiện kèm theo (nếu có)">
                        </div>

                        <div class="mb-3">
                            <label for="serial_numbers" class="form-label fw-bold">Số seri <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="serial_numbers" name="serial_numbers" value="{{ $warranty->serial_number }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="purchase_date" class="form-label fw-bold">Ngày mua <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="{{ $warranty->purchase_date->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="warranty_status" class="form-label fw-bold">Trạng thái bảo hành <span class="text-danger">*</span></label>
                            <select name="warranty_status" id="warranty_status" class="form-select" required>
                                <option value="under_warranty" {{ $warranty->canClaimWarranty() ? 'selected' : '' }}>Còn bảo hành</option>
                                <option value="out_of_warranty" {{ !$warranty->canClaimWarranty() ? 'selected' : '' }}>Hết bảo hành</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="employee_count" class="form-label fw-bold">Số nhân viên</label>
                            <input type="number" class="form-control" id="employee_count" name="employee_count" min="1">
                        </div>

                        <div class="mb-3">
                            <label for="repair_time_required" class="form-label fw-bold">Thời gian sửa chữa cần thiết</label>
                            <input type="text" class="form-control" id="repair_time_required" name="repair_time_required" placeholder="VD: 3-5 ngày">
                        </div>

                        <div class="mb-3">
                            <label for="estimated_warranty_time" class="form-label fw-bold">Thời gian bảo hành dự kiến</label>
                            <input type="text" class="form-control" id="estimated_warranty_time" name="estimated_warranty_time" placeholder="VD: 30 ngày">
                        </div>

                        <div class="mb-3">
                            <label for="estimated_return_date" class="form-label fw-bold">Ngày dự kiến trả</label>
                            <input type="date" class="form-control" id="estimated_return_date" name="estimated_return_date">
                        </div>

                        <div class="mb-3">
                            <label for="actual_return_date" class="form-label fw-bold">Ngày trả máy cho khách</label>
                            <input type="date" class="form-control" id="actual_return_date" name="actual_return_date">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="received_date" class="form-label fw-bold">Ngày tiếp nhận <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="received_date" name="received_date" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="received_by" class="form-label fw-bold">Người tiếp nhận</label>
                            <input type="text" class="form-control" id="received_by" name="received_by" value="{{ old('received_by') }}" placeholder="Nhập người tiếp nhận">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="received_by_phone" class="form-label fw-bold">Số điện thoại người tiếp nhận</label>
                            <input type="text" class="form-control" id="received_by_phone" name="received_by_phone" value="{{ old('received_by_phone') }}" placeholder="Nhập số điện thoại người tiếp nhận">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label fw-bold">Trạng thái phiếu <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="draft">Nháp</option>
                                <option value="submitted">Đã gửi</option>
                                <option value="in_progress">Đang xử lý</option>
                                <option value="completed">Hoàn thành</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label fw-bold">Ghi chú</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Ghi chú thêm về phiếu bảo hành..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.warranties.show', $warranty) }}" class="btn btn-secondary">
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
    const receivedDateEl = document.getElementById('received_date');
    const estimatedReturnDateEl = document.getElementById('estimated_return_date');
    const estimatedWarrantyTimeEl = document.getElementById('estimated_warranty_time');

    // Set default received date to today
    if (receivedDateEl && !receivedDateEl.value) {
        receivedDateEl.value = new Date().toISOString().split('T')[0];
    }

    // Auto-calculate estimated return date (7 days from today)
    if (estimatedReturnDateEl && !estimatedReturnDateEl.value) {
        const today = new Date();
        const estimatedDate = new Date(today.getTime() + (7 * 24 * 60 * 60 * 1000));
        estimatedReturnDateEl.value = estimatedDate.toISOString().split('T')[0];
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