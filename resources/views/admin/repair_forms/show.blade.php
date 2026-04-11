@extends('layouts.admin')

@section('title', 'Chi tiết phiếu bảo hành')

@section('content')
<div class="container-fluid">
    <style>
        .rf-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 16px;
        }
        .rf-toolbar-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .rf-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 16px;
        }
        .rf-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
            overflow: hidden;
        }
        .rf-card + .rf-card {
            margin-top: 16px;
        }
        .rf-card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            border-bottom: 1px solid #f1f3f5;
            background: #fafcff;
        }
        .rf-card-title {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            color: #0d6efd;
        }
        .rf-card-body {
            padding: 14px 16px;
        }
        .rf-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 24px;
        }
        .rf-item {
            display: grid;
            grid-template-columns: 160px 1fr;
            gap: 10px;
            align-items: start;
            border-bottom: 1px dashed #edf0f2;
            padding-bottom: 8px;
        }
        .rf-item.full {
            grid-column: 1 / -1;
            grid-template-columns: 160px 1fr;
        }
        .rf-label {
            color: #6c757d;
            font-size: 13px;
        }
        .rf-value {
            font-weight: 600;
            color: #212529;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .rf-note {
            background: #fff8e6;
            border: 1px solid #ffe5a6;
            border-radius: 8px;
            padding: 10px 12px;
            color: #7a5b00;
        }
        @media (max-width: 1200px) {
            .rf-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 768px) {
            .rf-toolbar {
                flex-direction: column;
            }
            .rf-info-grid {
                grid-template-columns: 1fr;
            }
            .rf-item,
            .rf-item.full {
                grid-template-columns: 1fr;
                gap: 4px;
            }
        }

        #printReturnModal .modal-dialog {
            margin: 1rem auto;
        }
        #printReturnModal .modal-content {
            max-height: calc(100vh - 2rem);
            border-radius: 10px;
            overflow: hidden;
        }
        #printReturnModal .modal-body {
            overflow-y: auto;
            padding-bottom: 12px;
        }
        #printReturnModal .modal-footer {
            position: sticky;
            bottom: 0;
            background: #fff;
            z-index: 2;
            border-top: 1px solid #dee2e6;
        }
    </style>

    <div class="rf-toolbar">
        <div>
            <h1 class="h3 mb-1 text-gray-800">
                <i class="bi bi-file-earmark-text"></i> Chi tiết phiếu bảo hành
            </h1>
            <p class="text-muted mb-0">Số phiếu: <strong>{{ $repairForm->form_number }}</strong></p>
        </div>
        <div class="rf-toolbar-actions">
            <a href="{{ route('admin.repair-forms.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
            <a href="{{ route('admin.repair-forms.exportWord', $repairForm) }}" class="btn btn-primary" target="_blank">
                <i class="bi bi-printer"></i> In Phiếu
            </a>
            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#printReturnModal">
                <i class="bi bi-printer"></i> In Phiếu Trả
            </button>
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

    <div class="rf-grid">
        <div>
            <div class="rf-card">
                <div class="rf-card-head">
                    <h6 class="rf-card-title">Thông tin phiếu bảo hành</h6>
                    <span class="badge bg-{{ $repairForm->status_color }}">{{ $repairForm->status_text }}</span>
                </div>
                <div class="rf-card-body">
                    <div class="rf-info-grid">
                        <div class="rf-item"><div class="rf-label">Số phiếu</div><div class="rf-value">{{ $repairForm->form_number }}</div></div>
                        <div class="rf-item"><div class="rf-label">Ngày tạo</div><div class="rf-value">{{ $repairForm->created_at->format('d/m/Y H:i') }}</div></div>
                        <div class="rf-item"><div class="rf-label">Khách hàng</div><div class="rf-value">{{ $repairForm->customer_company }}</div></div>
                        <div class="rf-item"><div class="rf-label">Người liên hệ</div><div class="rf-value">{{ $repairForm->contact_person }}</div></div>
                        <div class="rf-item"><div class="rf-label">Số điện thoại</div><div class="rf-value">{{ $repairForm->contact_phone }}</div></div>
                        <div class="rf-item"><div class="rf-label">Ngày mua</div><div class="rf-value">{{ $repairForm->purchase_date ? $repairForm->purchase_date->format('d/m/Y') : '' }}</div></div>
                        <div class="rf-item"><div class="rf-label">Ngày tiếp nhận</div><div class="rf-value">{{ $repairForm->received_date ? $repairForm->received_date->format('d/m/Y') : '' }}</div></div>
                        <div class="rf-item"><div class="rf-label">Người tiếp nhận</div><div class="rf-value">{{ $repairForm->received_by }}</div></div>
                        @if($repairForm->received_by_phone)
                            <div class="rf-item"><div class="rf-label">SĐT người tiếp nhận</div><div class="rf-value">{{ $repairForm->received_by_phone }}</div></div>
                        @endif
                        @if($repairForm->email)
                            <div class="rf-item"><div class="rf-label">Email</div><div class="rf-value">{{ $repairForm->email }}</div></div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="rf-card">
                <div class="rf-card-head">
                    <h6 class="rf-card-title">Thông tin thiết bị</h6>
                </div>
                <div class="rf-card-body">
                    <div class="rf-info-grid">
                        <div class="rf-item"><div class="rf-label">Tên thiết bị</div><div class="rf-value">{{ $repairForm->equipment_name }}</div></div>
                        <div class="rf-item"><div class="rf-label">Trạng thái bảo hành</div><div class="rf-value"><span class="badge bg-{{ $repairForm->warranty_status == 'under_warranty' ? 'success' : 'danger' }}">{{ $repairForm->warranty_status_text }}</span></div></div>
                        <div class="rf-item full"><div class="rf-label">Số seri</div><div class="rf-value">{{ $repairForm->serial_numbers }}</div></div>
                        <div class="rf-item full"><div class="rf-label">Tình trạng lỗi</div><div class="rf-value">{{ $repairForm->error_status }}</div></div>
                        <div class="rf-item"><div class="rf-label">Phụ kiện kèm theo</div><div class="rf-value">{{ $repairForm->accessories ?: 'Không' }}</div></div>
                        <div class="rf-item"><div class="rf-label">TG sửa chữa cần thiết</div><div class="rf-value">{{ $repairForm->repair_time_required }}</div></div>
                        <div class="rf-item"><div class="rf-label">TG bảo hành dự kiến</div><div class="rf-value">{{ $repairForm->estimated_warranty_time }}</div></div>
                        <div class="rf-item"><div class="rf-label">Ngày dự kiến trả</div><div class="rf-value">{{ $repairForm->estimated_return_date ? $repairForm->estimated_return_date->format('d/m/Y') : '' }}</div></div>
                        <div class="rf-item"><div class="rf-label">Ngày trả máy</div><div class="rf-value">{{ $repairForm->actual_return_date ? $repairForm->actual_return_date->format('d/m/Y') : '' }}</div></div>
                    </div>
                </div>
            </div>

            <div class="rf-card">
                <div class="rf-card-head">
                    <h6 class="rf-card-title">Thông tin bàn giao (phiếu trả)</h6>
                </div>
                <div class="rf-card-body">
                    <div class="rf-info-grid">
                        <div class="rf-item"><div class="rf-label">Người bàn giao</div><div class="rf-value">{{ $repairForm->handed_over_by }}</div></div>
                        <div class="rf-item"><div class="rf-label">SĐT người bàn giao</div><div class="rf-value">{{ $repairForm->handed_over_by_phone }}</div></div>
                        <div class="rf-item"><div class="rf-label">Phụ trách DVKH</div><div class="rf-value">{{ $repairForm->service_representative }}</div></div>
                        <div class="rf-item"><div class="rf-label">TG BH/sửa chữa</div><div class="rf-value">{{ $repairForm->handover_check_info }}</div></div>
                        <div class="rf-item full"><div class="rf-label">Thông tin đã sửa chữa</div><div class="rf-value">{{ $repairForm->handover_repair_info }}</div></div>
                    </div>
                </div>
            </div>

            @if($repairForm->notes)
                <div class="rf-note">
                    <strong>Ghi chú:</strong> {{ $repairForm->notes }}
                </div>
            @endif
        </div>

        <div>
            @if($repairForm->warranty)
                <div class="rf-card">
                    <div class="rf-card-head"><h6 class="rf-card-title">Thông tin bảo hành</h6></div>
                    <div class="rf-card-body">
                        <div class="rf-item"><div class="rf-label">Số seri</div><div class="rf-value">{{ $repairForm->warranty->serial_number }}</div></div>
                        <div class="rf-item"><div class="rf-label">Sản phẩm</div><div class="rf-value">{{ optional(optional($repairForm->warranty)->product)->name }}</div></div>
                        <div class="rf-item"><div class="rf-label">Khách hàng</div><div class="rf-value">{{ $repairForm->warranty->customer_name }}</div></div>
                        <div class="rf-item"><div class="rf-label">Trạng thái</div><div class="rf-value"><span class="badge bg-{{ $repairForm->warranty->warranty_status_color }}">{{ $repairForm->warranty->warranty_status_text }}</span></div></div>
                        <a href="{{ route('admin.warranties.show', $repairForm->warranty) }}" class="btn btn-outline-primary btn-sm mt-2">
                            <i class="bi bi-eye"></i> Xem chi tiết
                        </a>
                    </div>
                </div>
            @endif

            @if($repairForm->warrantyClaim)
                <div class="rf-card">
                    <div class="rf-card-head"><h6 class="rf-card-title">Yêu cầu bảo hành</h6></div>
                    <div class="rf-card-body">
                        <div class="rf-item"><div class="rf-label">Số yêu cầu</div><div class="rf-value">{{ $repairForm->warrantyClaim->claim_number }}</div></div>
                        <div class="rf-item"><div class="rf-label">Ngày yêu cầu</div><div class="rf-value">{{ $repairForm->warrantyClaim->claim_date->format('d/m/Y') }}</div></div>
                        <div class="rf-item"><div class="rf-label">Trạng thái</div><div class="rf-value"><span class="badge bg-{{ $repairForm->warrantyClaim->status_color }}">{{ $repairForm->warrantyClaim->status_text }}</span></div></div>
                    </div>
                </div>
            @endif

            <div class="rf-card">
                <div class="rf-card-head"><h6 class="rf-card-title">Lịch sử cập nhật</h6></div>
                <div class="rf-card-body">
                    <div class="rf-item"><div class="rf-label">Tạo lúc</div><div class="rf-value">{{ $repairForm->created_at->format('d/m/Y H:i') }}</div></div>
                    @if($repairForm->updated_at != $repairForm->created_at)
                        <div class="rf-item"><div class="rf-label">Cập nhật lúc</div><div class="rf-value">{{ $repairForm->updated_at->format('d/m/Y H:i') }}</div></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="printReturnModal" tabindex="-1" aria-labelledby="printReturnModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.repair-forms.savePrintReturnInfo', $repairForm) }}" target="_blank">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="printReturnModalLabel">Cập nhật thông tin phiếu trả</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="modal_handed_over_by" class="form-label fw-bold">Người bàn giao</label>
                            <select id="modal_handed_over_by" name="handed_over_by" class="form-select">
                                <option value="">-- Chọn người bàn giao --</option>
                                <option value="Nguyễn Thị Hồng Vi" {{ old('handed_over_by', $repairForm->handed_over_by) === 'Nguyễn Thị Hồng Vi' ? 'selected' : '' }}>Nguyễn Thị Hồng Vi</option>
                                <option value="Bùi Nguyễn Tường Vy" {{ old('handed_over_by', $repairForm->handed_over_by) === 'Bùi Nguyễn Tường Vy' ? 'selected' : '' }}>Bùi Nguyễn Tường Vy</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="modal_handed_over_by_phone" class="form-label fw-bold">SĐT người bàn giao</label>
                            <input id="modal_handed_over_by_phone" name="handed_over_by_phone" type="text" class="form-control" value="{{ old('handed_over_by_phone', $repairForm->handed_over_by_phone) }}">
                        </div>

                        <div class="col-md-6">
                            <label for="modal_actual_return_date" class="form-label fw-bold">Ngày trả máy cho khách</label>
                            <input id="modal_actual_return_date" name="actual_return_date" type="date" class="form-control" value="{{ old('actual_return_date', optional($repairForm->actual_return_date)->toDateString() ?: now()->toDateString()) }}">
                        </div>
                        <div class="col-md-6">
                            <label for="modal_handover_check_info" class="form-label fw-bold">Thời gian BH/sửa chữa</label>
                            <input id="modal_handover_check_info" name="handover_check_info" type="text" class="form-control" value="{{ old('handover_check_info', $repairForm->handover_check_info) }}" placeholder="Tự động tính theo ngày trả" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ngày tiếp nhận</label>
                            <input type="text" class="form-control" value="{{ optional($repairForm->received_date)->format('d/m/Y') }}" readonly>
                        </div>

                        <div class="col-md-6">
                            <label for="modal_service_representative" class="form-label fw-bold">Phụ trách DVKH</label>
                            <select class="form-select" id="modal_service_representative" name="service_representative">
                                <option value="">-- Chọn phụ trách DVKH --</option>
                                <option value="Nguyễn Thị Hồng Vi" {{ old('service_representative', $repairForm->service_representative) === 'Nguyễn Thị Hồng Vi' ? 'selected' : '' }}>Nguyễn Thị Hồng Vi</option>
                                <option value="Bùi Nguyễn Tường Vy" {{ old('service_representative', $repairForm->service_representative) === 'Bùi Nguyễn Tường Vy' ? 'selected' : '' }}>Bùi Nguyễn Tường Vy</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Phụ kiện đi kèm (từ phiếu tiếp nhận)</label>
                            <input type="text" class="form-control" value="{{ $repairForm->accessories }}" readonly>
                        </div>

                        <div class="col-12">
                            <label for="modal_handover_repair_info" class="form-label fw-bold">Thông tin đã sửa chữa</label>
                            <textarea id="modal_handover_repair_info" name="handover_repair_info" rows="3" class="form-control">{{ old('handover_repair_info', $repairForm->handover_repair_info) }}</textarea>
                        </div>

                        <div class="col-12">
                            <label for="modal_notes" class="form-label fw-bold">Ghi chú</label>
                            <textarea id="modal_notes" name="notes" rows="3" class="form-control">{{ old('notes', $repairForm->notes) }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-printer"></i> Lưu & In phiếu trả
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dateEl = document.getElementById('modal_actual_return_date');
    const durationEl = document.getElementById('modal_handover_check_info');
    const receivedRaw = '{{ optional($repairForm->received_date)->toDateString() }}';

    function parseIsoDate(value) {
        if (!value) return null;
        const m = /^([0-9]{4})-([0-9]{2})-([0-9]{2})$/.exec(value);
        if (!m) return null;
        const y = Number(m[1]);
        const mo = Number(m[2]);
        const d = Number(m[3]);
        const dt = new Date(y, mo - 1, d);
        return Number.isNaN(dt.getTime()) ? null : dt;
    }

    function diffDays(a, b) {
        const one = new Date(a.getFullYear(), a.getMonth(), a.getDate()).getTime();
        const two = new Date(b.getFullYear(), b.getMonth(), b.getDate()).getTime();
        return Math.round((two - one) / (24 * 60 * 60 * 1000));
    }

    function syncDuration() {
        if (!durationEl) return;
        const received = parseIsoDate(receivedRaw);
        const returned = parseIsoDate(dateEl ? dateEl.value : '');
        if (!received || !returned) return;

        const days = diffDays(received, returned);
        if (days < 0) {
            durationEl.value = '0 ngày';
        } else if (days === 0) {
            durationEl.value = 'Trong ngày';
        } else {
            durationEl.value = days + ' ngày';
        }
    }

    if (dateEl) {
        dateEl.addEventListener('change', syncDuration);
    }
    syncDuration();
});
</script>
@endsection
