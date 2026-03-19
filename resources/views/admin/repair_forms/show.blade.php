@extends('layouts.admin')

@section('title', 'Chi tiết phiếu bảo hành')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-file-earmark-text"></i> Chi tiết phiếu bảo hành
            </h1>
            <p class="text-muted">Số phiếu: <strong>{{ $repairForm->form_number }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.repair-forms.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
            <a href="{{ route('admin.repair-forms.exportWord', $repairForm) }}" class="btn btn-primary" target="_blank">
                <i class="bi bi-printer"></i> In Phiếu
            </a>
            <a href="{{ route('admin.repair-forms.printReturn', $repairForm) }}?v={{ time() }}" class="btn btn-outline-success" target="_blank">
                <i class="bi bi-printer"></i> In Phiếu Trả
            </a>
            <a href="{{ route('admin.repair-forms.edit', $repairForm) }}" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Sửa
            </a>
            <form action="{{ route('admin.repair-forms.destroy', $repairForm) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phiếu này?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Xóa
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Thông tin phiếu -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin phiếu bảo hành</h6>
                    <span class="badge bg-{{ $repairForm->status_color }} fs-6">
                        {{ $repairForm->status_text }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="text-muted">Số phiếu</div>
                                <div class="fw-semibold text-end">{{ $repairForm->form_number }}</div>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="text-muted">Ngày tạo</div>
                                <div class="fw-semibold text-end">{{ $repairForm->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="text-muted">Tên công ty</div>
                                <div class="fw-semibold text-end">{{ $repairForm->customer_company }}</div>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="text-muted">Người liên hệ</div>
                                <div class="fw-semibold text-end">{{ $repairForm->contact_person }}</div>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="text-muted">Số điện thoại</div>
                                <div class="fw-semibold text-end">{{ $repairForm->contact_phone }}</div>
                            </div>
                            @if($repairForm->alternate_contact)
                            <hr class="my-2">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="text-muted">Người liên hệ khác</div>
                                <div class="fw-semibold text-end">{{ $repairForm->alternate_contact }}</div>
                            </div>
                            @endif
                            @if($repairForm->alternate_phone)
                            <hr class="my-2">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="text-muted">SĐT khác</div>
                                <div class="fw-semibold text-end">{{ $repairForm->alternate_phone }}</div>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="text-muted">Ngày mua</div>
                                <div class="fw-semibold text-end">{{ $repairForm->purchase_date ? $repairForm->purchase_date->format('d/m/Y') : '' }}</div>
                            </div>
                            @if($repairForm->company_phone)
                            <hr class="my-2">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="text-muted">Điện thoại công ty</div>
                                <div class="fw-semibold text-end">{{ $repairForm->company_phone }}</div>
                            </div>
                            @endif
                            @if($repairForm->fax)
                            <hr class="my-2">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="text-muted">Fax</div>
                                <div class="fw-semibold text-end">{{ $repairForm->fax }}</div>
                            </div>
                            @endif
                            @if($repairForm->email)
                            <hr class="my-2">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="text-muted">Email</div>
                                <div class="fw-semibold text-end">{{ $repairForm->email }}</div>
                            </div>
                            @endif
                            <hr class="my-2">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="text-muted">Ngày tiếp nhận</div>
                                <div class="fw-semibold text-end">{{ $repairForm->received_date->format('d/m/Y') }}</div>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="text-muted">Người tiếp nhận</div>
                                <div class="fw-semibold text-end">{{ $repairForm->received_by }}</div>
                            </div>
                            @if($repairForm->received_by_phone)
                            <hr class="my-2">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="text-muted">SĐT người tiếp nhận</div>
                                <div class="fw-semibold text-end">{{ $repairForm->received_by_phone }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Thông tin thiết bị -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin thiết bị</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="text-muted">Tên thiết bị</div>
                                <div class="fw-semibold text-end">{{ $repairForm->equipment_name }}</div>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="text-muted">Số seri</div>
                                <div class="fw-semibold text-end">{{ $repairForm->serial_numbers }}</div>
                            </div>
                            <hr class="my-2">
                            <div>
                                <div class="text-muted mb-1">Tình trạng lỗi</div>
                                <div class="fw-semibold">{{ $repairForm->error_status }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between gap-3 align-items-center">
                                <div class="text-muted">Trạng thái bảo hành</div>
                                <div>
                                    <span class="badge bg-{{ $repairForm->warranty_status == 'under_warranty' ? 'success' : 'danger' }}">
                                        {{ $repairForm->warranty_status_text }}
                                    </span>
                                </div>
                            </div>
                            @if($repairForm->accessories)
                            <hr class="my-2">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="text-muted">Phụ kiện kèm theo</div>
                                <div class="fw-semibold text-end">{{ $repairForm->accessories }}</div>
                            </div>
                            @endif
                            @if($repairForm->employee_count)
                            <hr class="my-2">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="text-muted">Số nhân viên</div>
                                <div class="fw-semibold text-end">{{ $repairForm->employee_count }}</div>
                            </div>
                            @endif
                            <hr class="my-2">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="text-muted">Thời gian sửa chữa cần thiết</div>
                                <div class="fw-semibold text-end">{{ $repairForm->repair_time_required }}</div>
                            </div>
                            @if($repairForm->estimated_warranty_time)
                            <hr class="my-2">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="text-muted">Thời gian bảo hành dự kiến</div>
                                <div class="fw-semibold text-end">{{ $repairForm->estimated_warranty_time }}</div>
                            </div>
                            @endif
                            @if($repairForm->estimated_return_date)
                            <hr class="my-2">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="text-muted">Ngày dự kiến trả</div>
                                <div class="fw-semibold text-end">{{ $repairForm->estimated_return_date->format('d/m/Y') }}</div>
                            </div>
                            @endif
                            @if($repairForm->actual_return_date)
                            <hr class="my-2">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="text-muted">Ngày trả máy cho khách</div>
                                <div class="fw-semibold text-end">{{ $repairForm->actual_return_date->format('d/m/Y') }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if($repairForm->handed_over_by || $repairForm->handed_over_by_phone || $repairForm->handover_repair_info || $repairForm->handover_check_info)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin bàn giao (Phiếu trả)</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="text-muted">Người bàn giao</div>
                                <div class="fw-semibold text-end">{{ $repairForm->handed_over_by }}</div>
                            </div>
                            @if($repairForm->handed_over_by_phone)
                            <hr class="my-2">
                            <div class="d-flex justify-content-between gap-3">
                                <div class="text-muted">SĐT người bàn giao</div>
                                <div class="fw-semibold text-end">{{ $repairForm->handed_over_by_phone }}</div>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($repairForm->handover_repair_info)
                            <div>
                                <div class="text-muted mb-1">Thông tin đã sửa chữa</div>
                                <div class="fw-semibold">{{ $repairForm->handover_repair_info }}</div>
                            </div>
                            @endif
                            @if($repairForm->handover_check_info)
                            <hr class="my-2">
                            <div>
                                <div class="text-muted mb-1">Thời gian bảo hành/sửa chữa</div>
                                <div class="fw-semibold">{{ $repairForm->handover_check_info }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if($repairForm->notes)
            <!-- Ghi chú -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Ghi chú</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $repairForm->notes }}</p>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Thông tin bảo hành liên quan -->
            @if($repairForm->warranty)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin bảo hành</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Số seri:</label>
                        <p class="mb-0">{{ $repairForm->warranty->serial_number }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Sản phẩm:</label>
                        <p class="mb-0">{{ optional(optional($repairForm->warranty)->product)->name }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Khách hàng:</label>
                        <p class="mb-0">{{ $repairForm->warranty->customer_name }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Trạng thái:</label>
                        <span class="badge bg-{{ $repairForm->warranty->warranty_status_color }}">
                            {{ $repairForm->warranty->warranty_status_text }}
                        </span>
                    </div>
                    <a href="{{ route('admin.warranties.show', $repairForm->warranty) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye"></i> Xem chi tiết
                    </a>
                </div>
            </div>
            @endif

            <!-- Thông tin yêu cầu bảo hành -->
            @if($repairForm->warrantyClaim)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Yêu cầu bảo hành</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Số yêu cầu:</label>
                        <p class="mb-0">{{ $repairForm->warrantyClaim->claim_number }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Ngày yêu cầu:</label>
                        <p class="mb-0">{{ $repairForm->warrantyClaim->claim_date->format('d/m/Y') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold text-muted">Trạng thái:</label>
                        <span class="badge bg-{{ $repairForm->warrantyClaim->status_color }}">
                            {{ $repairForm->warrantyClaim->status_text }}
                        </span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Lịch sử cập nhật -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Lịch sử cập nhật</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Tạo lúc:</small>
                        <p class="mb-0">{{ $repairForm->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @if($repairForm->updated_at != $repairForm->created_at)
                    <div class="mb-3">
                        <small class="text-muted">Cập nhật lúc:</small>
                        <p class="mb-0">{{ $repairForm->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 