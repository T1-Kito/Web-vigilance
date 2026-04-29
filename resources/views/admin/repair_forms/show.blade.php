@extends('layouts.admin')

@section('title', 'Chi tiết phiếu bảo hành')

@section('content')
<div class="container-fluid">
    <style>
        .rf-page {
            background: #f8fafc;
            color: #0f172a;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 14px;
        }
        .rf-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        .rf-toolbar-left,
        .rf-toolbar-right {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .rf-btn {
            border-radius: 10px;
            min-height: 36px;
            padding: 6px 12px;
            font-weight: 600;
            box-shadow: none !important;
        }
        .rf-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 2px;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 14px;
        }
        .rf-meta-code {
            font-weight: 800;
            color: #0f172a;
        }
        .rf-section {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 12px;
        }
        .rf-section-title {
            margin: 0 0 12px;
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            padding-left: 10px;
            border-left: 4px solid #2563eb;
            line-height: 1.2;
            letter-spacing: .02em;
        }
        .rf-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 24px;
        }
        .rf-row {
            display: grid;
            grid-template-columns: 160px 1fr;
            align-items: start;
            gap: 8px;
            min-height: 30px;
        }
        .rf-label {
            color: #64748b;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .02em;
        }
        .rf-value {
            color: #111827;
            font-weight: 600;
            white-space: pre-wrap;
            word-break: break-word;
            font-size: 14px;
        }
        .rf-value.text-normal {
            font-weight: 500;
        }
        .rf-full {
            grid-column: 1 / -1;
        }
        .rf-file-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border: 1px solid #dbeafe;
            border-radius: 10px;
            padding: 8px 10px;
            background: #f8fbff;
            max-width: 760px;
        }
        .rf-file-left {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }
        .rf-file-name {
            max-width: 380px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #111827;
            font-weight: 600;
            font-size: 13px;
        }
        .rf-file-actions {
            display: inline-flex;
            gap: 6px;
            align-items: center;
        }
        .rf-file-actions .btn {
            border-radius: 999px;
            min-height: 30px;
            padding: 3px 10px;
            font-size: 12px;
            font-weight: 600;
        }
        .rf-upload-inline {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
            max-width: 680px;
        }
        .rf-upload-inline .form-control {
            min-height: 34px;
            border-radius: 9px;
            font-size: 12px;
            padding: 4px 8px;
            background: #fff;
            border-color: #d1d5db;
        }
        .rf-upload-inline .btn {
            min-height: 34px;
            padding: 4px 12px;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.2;
        }
        .rf-note {
            background: #fff8e6;
            border: 1px solid #ffe5a6;
            border-radius: 10px;
            padding: 10px 12px;
            color: #7a5b00;
            margin-top: 12px;
        }
        @media (max-width: 992px) {
            .rf-grid-2 { grid-template-columns: 1fr; }
            .rf-row { grid-template-columns: 140px 1fr; }
            .rf-toolbar { flex-direction: column; align-items: flex-start; }
            .rf-meta { flex-direction: column; align-items: flex-start; gap: 8px; }
            .rf-file-row { flex-direction: column; align-items: flex-start; }
            .rf-file-name { max-width: 100%; }
            .rf-upload-inline { flex-direction: column; align-items: stretch; }
        }

        #printReturnModal .modal-dialog {
            margin: 1rem auto;
        }
        #printReturnModal .modal-content {
            max-height: calc(100vh - 2rem);
            border-radius: 12px;
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

    <div class="rf-page">
        <div class="rf-toolbar">
            <div class="rf-toolbar-left">
                <a href="{{ route('admin.repair-forms.index') }}" class="btn btn-outline-secondary rf-btn">
                    <i class="bi bi-arrow-left me-1"></i>Quay lại
                </a>
                <a href="{{ route('admin.repair-forms.returns') }}" class="btn btn-outline-secondary rf-btn">
                    <i class="bi bi-list-ul me-1"></i>Danh sách phiếu
                </a>
            </div>
            <div class="rf-toolbar-right">
                <a href="{{ route('admin.repair-forms.exportWord', $repairForm) }}" class="btn btn-primary rf-btn" target="_blank">
                    <i class="bi bi-printer me-1"></i>In Phiếu
                </a>
                <button type="button" class="btn btn-outline-primary rf-btn" data-bs-toggle="modal" data-bs-target="#printReturnModal">
                    <i class="bi bi-printer me-1"></i>In Phiếu Trả
                </button>
                <a href="{{ route('admin.repair-forms.edit', $repairForm) }}" class="btn btn-outline-secondary rf-btn">
                    <i class="bi bi-pencil me-1"></i>Sửa
                </a>
                <form action="{{ route('admin.repair-forms.destroy', $repairForm) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phiếu này?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger rf-btn">
                        <i class="bi bi-trash me-1"></i>Xóa
                    </button>
                </form>
            </div>
        </div>

        <div class="rf-meta">
            <div>Số phiếu: <span class="rf-meta-code">{{ $repairForm->form_number }}</span></div>
            <span class="badge bg-{{ $repairForm->status_color }}">{{ $repairForm->status_text }}</span>
        </div>

        <div class="rf-section">
            <h6 class="rf-section-title">THÔNG TIN PHIẾU BẢO HÀNH</h6>
            <div class="rf-grid-2">
                <div class="rf-row"><div class="rf-label">Số phiếu</div><div class="rf-value">{{ $repairForm->form_number }}</div></div>
                <div class="rf-row"><div class="rf-label">Ngày tạo</div><div class="rf-value">{{ $repairForm->created_at->format('d/m/Y H:i') }}</div></div>
                <div class="rf-row"><div class="rf-label">Khách hàng</div><div class="rf-value">{{ $repairForm->customer_company }}</div></div>
                <div class="rf-row"><div class="rf-label">Người liên hệ</div><div class="rf-value">{{ $repairForm->contact_person ?: '(Trống)' }}</div></div>
                <div class="rf-row"><div class="rf-label">Số điện thoại</div><div class="rf-value">{{ $repairForm->contact_phone ?: '(Trống)' }}</div></div>
                <div class="rf-row"><div class="rf-label">Ngày mua</div><div class="rf-value">{{ $repairForm->purchase_date ? $repairForm->purchase_date->format('d/m/Y') : '(Trống)' }}</div></div>
                <div class="rf-row"><div class="rf-label">Ngày tiếp nhận</div><div class="rf-value">{{ $repairForm->received_date ? $repairForm->received_date->format('d/m/Y') : '(Trống)' }}</div></div>
                <div class="rf-row"><div class="rf-label">Người tiếp nhận</div><div class="rf-value">{{ $repairForm->received_by ?: '(Trống)' }}</div></div>
            </div>
        </div>

        <div class="rf-section">
            <h6 class="rf-section-title">THÔNG TIN THIẾT BỊ</h6>
            <div class="rf-grid-2">
                <div class="rf-row"><div class="rf-label">Tên thiết bị</div><div class="rf-value">{{ $repairForm->equipment_name }}</div></div>
                <div class="rf-row"><div class="rf-label">Trạng thái bảo hành</div><div class="rf-value"><span class="badge bg-{{ $repairForm->warranty_status == 'under_warranty' ? 'success' : 'danger' }}">{{ $repairForm->warranty_status_text }}</span></div></div>
                <div class="rf-row rf-full"><div class="rf-label">Số seri</div><div class="rf-value">{{ $repairForm->serial_numbers }}</div></div>
                <div class="rf-row rf-full"><div class="rf-label">Tình trạng lỗi</div><div class="rf-value text-normal">{{ $repairForm->error_status }}</div></div>
                <div class="rf-row"><div class="rf-label">Phụ kiện kèm</div><div class="rf-value">{{ $repairForm->accessories ?: 'Không' }}</div></div>
                <div class="rf-row"><div class="rf-label">TG sửa dự kiến</div><div class="rf-value">{{ $repairForm->repair_time_required ?: '(Trống)' }}</div></div>
                <div class="rf-row"><div class="rf-label">TG BH dự kiến</div><div class="rf-value">{{ $repairForm->estimated_warranty_time ?: '(Trống)' }}</div></div>
                <div class="rf-row"><div class="rf-label">Ngày trả máy</div><div class="rf-value">{{ $repairForm->actual_return_date ? $repairForm->actual_return_date->format('d/m/Y') : '(Trống)' }}</div></div>
            </div>
        </div>

        <div class="rf-section">
            <h6 class="rf-section-title">THÔNG TIN BÀN GIAO (PHIẾU TRẢ)</h6>
            <div class="rf-grid-2">
                <div class="rf-row"><div class="rf-label">Người bàn giao</div><div class="rf-value">{{ $repairForm->handed_over_by ?: '(Trống)' }}</div></div>
                <div class="rf-row"><div class="rf-label">SĐT người bàn giao</div><div class="rf-value">{{ $repairForm->handed_over_by_phone ?: '(Trống)' }}</div></div>
                <div class="rf-row"><div class="rf-label">Phụ trách DVKH</div><div class="rf-value">{{ $repairForm->service_representative ?: '(Trống)' }}</div></div>
                <div class="rf-row"><div class="rf-label">TG BH/Sửa chữa</div><div class="rf-value">{{ $repairForm->handover_check_info ?: '(Trống)' }}</div></div>
                <div class="rf-row rf-full"><div class="rf-label">Thông tin sửa</div><div class="rf-value text-normal">{{ $repairForm->handover_repair_info ?: '(Trống)' }}</div></div>

                <div class="rf-row rf-full">
                    <div class="rf-label">File phiếu trả</div>
                    <div>
                        <div class="rf-file-row">
                            <div class="rf-file-left">
                                @if($repairForm->return_file_path)
                                    <i class="bi bi-file-earmark-pdf text-danger"></i>
                                    <span class="rf-file-name">{{ $repairForm->return_file_original_name ?: 'phieu_tra_BH.pdf' }}</span>
                                @else
                                    <i class="bi bi-file-earmark-x text-muted"></i>
                                    <span class="rf-file-name text-muted">Chưa có file phiếu trả</span>
                                @endif
                            </div>
                            <div class="rf-file-actions">
                                @if($repairForm->return_file_path)
                                    <a href="{{ route('admin.repair-forms.downloadReturnFile', $repairForm) }}" class="btn btn-outline-primary">
                                        <i class="bi bi-download me-1"></i>Tải xuống
                                    </a>
                                    <form action="{{ route('admin.repair-forms.deleteReturnFile', $repairForm) }}" method="POST" onsubmit="return confirm('Xóa file phiếu trả hiện tại?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger">
                                            <i class="bi bi-x-lg me-1"></i>Xóa
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <form action="{{ route('admin.repair-forms.uploadReturnFile', $repairForm) }}" method="POST" enctype="multipart/form-data" class="rf-upload-inline">
                            @csrf
                            <input type="file" name="return_file" id="return_file" class="form-control" accept="application/pdf" required>
                            <button type="submit" class="btn btn-outline-success">
                                <i class="bi bi-upload me-1"></i>{{ $repairForm->return_file_path ? 'Chọn tệp khác' : 'Upload PDF' }}
                            </button>
                        </form>
                        <div class="small text-muted mt-1">Chỉ nhận PDF tối đa 10MB.</div>
                    </div>
                </div>
            </div>
        </div>

        @if($repairForm->notes)
            <div class="rf-note">
                <strong>Ghi chú:</strong> {{ $repairForm->notes }}
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="printReturnModal" tabindex="-1" aria-labelledby="printReturnModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printReturnModalLabel">Cập nhật thông tin phiếu trả</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('admin.repair-forms.savePrintReturnInfo', $repairForm) }}" id="printReturnForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Người bàn giao</label>
                            <input type="text" name="handed_over_by" class="form-control" value="{{ old('handed_over_by', $repairForm->handed_over_by) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SĐT người bàn giao</label>
                            <input type="text" name="handed_over_by_phone" class="form-control" value="{{ old('handed_over_by_phone', $repairForm->handed_over_by_phone) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phụ trách DVKH</label>
                            <input type="text" name="service_representative" class="form-control" value="{{ old('service_representative', $repairForm->service_representative) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ngày trả máy</label>
                            <input type="date" name="actual_return_date" class="form-control" value="{{ old('actual_return_date', optional($repairForm->actual_return_date)->toDateString()) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Thông tin đã sửa chữa</label>
                            <textarea name="handover_repair_info" class="form-control" rows="3">{{ old('handover_repair_info', $repairForm->handover_repair_info) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $repairForm->notes) }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-printer"></i> Lưu & In phiếu trả
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
