@extends('layouts.admin')

@section('title', 'Quản lý phiếu bảo hành')

@section('content')
<style>
    .rf-page {
        color: #0f172a;
    }
    .rf-page .card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: none;
        background: #fff;
    }

    .rf-page .row.g-3.mb-4 .card {
        border: 0 !important;
        border-radius: 16px !important;
    }
    .rf-page .btn {
        border-radius: 10px;
    }
    .rf-page .btn-primary { background: #2563eb; border-color: #2563eb; }
    .rf-page .table thead th {
        background: #f8fafc;
        color: #334155;
        border-bottom: 1px solid #e2e8f0;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .02em;
    }
    .rf-page .table td {
        border-color: #eef2f7;
        vertical-align: middle;
    }
    .rf-page .table-hover tbody tr:hover {
        background: #eaf2ff;
    }
    .rf-page .table-hover tbody tr:hover td {
        background: #eaf2ff;
    }
    .rf-page .rf-clickable-row {
        cursor: pointer;
    }
    .rf-page .badge {
        border-radius: 999px;
        padding: 6px 12px;
        font-weight: 700;
    }
    .rf-page .btn-group .btn {
        min-width: 36px;
    }
    .rf-toolbar-btn {
        border-radius: 10px !important;
        padding: 10px 14px !important;
        font-weight: 600 !important;
    }
    .rf-filter .form-control,
    .rf-filter .form-select {
        border-radius: 10px !important;
        border: 1px solid #dbe2ea !important;
        min-height: 42px;
    }
    .rf-filter .form-control:focus,
    .rf-filter .form-select:focus {
        border-color: #93c5fd !important;
        box-shadow: 0 0 0 0.2rem rgba(59,130,246,.12) !important;
    }
</style>
<div class="container-fluid py-2 rf-page">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <h1 class="h4 mb-0 text-gray-800">Quản lý phiếu bảo hành</h1>
    </div>

    <!-- Stats Cards + CTA -->
    <div class="row g-3 mb-3 align-items-stretch">
        <div class="col-xl-3 col-md-6">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small">Tổng phiếu</div>
                            <div class="h4 mb-0">{{ $totalForms ?? $repairForms->total() }}</div>
                        </div>
                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small">Chưa gửi trả</div>
                            <div class="h4 mb-0">{{ $notReturnedCount ?? 0 }}</div>
                        </div>
                        <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small">Đã gửi trả</div>
                            <div class="h4 mb-0">{{ $returnedCount ?? 0 }}</div>
                        </div>
                        <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 d-flex align-items-center justify-content-xl-end">
            <a href="{{ route('admin.repair-forms.create') }}" class="btn btn-primary rf-toolbar-btn w-100 w-xl-auto">
                <i class="bi bi-plus-circle me-1"></i>
                Tạo phiếu mới
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end mb-3 rf-filter">
                <div class="col-lg-6">
                    <input type="text" class="form-control" id="serial_search" name="serial_search" value="{{ request('serial_search') }}" placeholder="🔍 Tìm theo số seri, tên khách hàng...">
                </div>
                <div class="col-lg-2 col-md-4">
                    <select name="status_filter" class="form-select">
                        <option value="">Trạng thái: Tất cả</option>
                        <option value="not_returned" {{ request('status_filter') === 'not_returned' ? 'selected' : '' }}>Chưa gửi trả</option>
                        <option value="returned" {{ request('status_filter') === 'returned' ? 'selected' : '' }}>Đã gửi trả</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-4">
                    <button type="submit" class="btn btn-primary rf-toolbar-btn w-100">Tìm kiếm</button>
                </div>
                <div class="col-lg-2 col-md-4">
                    <a href="{{ route('admin.repair-forms.index') }}" class="btn btn-outline-secondary rf-toolbar-btn w-100">Làm mới</a>
                </div>
            </form>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Số seri</th>
                            <th>Khách hàng</th>
                            <th>Thiết bị</th>
                            <th>Ngày tiếp nhận</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($repairForms as $repairForm)
                        <tr class="rf-clickable-row" data-href="{{ route('admin.repair-forms.show', $repairForm) }}">
                            <td>
                                <div class="fw-semibold">{{ $repairForm->serial_numbers }}</div>
                                <div class="text-muted small">{{ $repairForm->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $repairForm->customer_company }}</div>
                                <div class="text-muted small">{{ $repairForm->contact_phone }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $repairForm->equipment_name }}</div>
                                <div class="text-muted small">SN: {{ $repairForm->serial_numbers }}</div>
                            </td>
                            <td>{{ $repairForm->received_date->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $repairForm->status_color }}">
                                    {{ $repairForm->status_text }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route('admin.repair-forms.show', $repairForm) }}" class="btn btn-sm btn-outline-secondary" title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.repair-forms.exportWord', $repairForm) }}" target="_blank">
                                                    <i class="bi bi-printer me-2"></i>In
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.repair-forms.edit', $repairForm) }}">
                                                    <i class="bi bi-pencil me-2"></i>Sửa
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('admin.repair-forms.destroy', $repairForm) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phiếu này?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="bi bi-trash me-2"></i>Xóa
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $repairForms->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Vietnamese.json"
        },
        "pageLength": 25,
        "order": [[0, "desc"]]
    });

    $('#dataTable').on('click', '.rf-clickable-row', function(e) {
        if ($(e.target).closest('a,button,form,input,select,textarea,label').length) {
            return;
        }
        const href = $(this).data('href');
        if (href) {
            window.location.href = href;
        }
    });
});
</script>
@endsection 