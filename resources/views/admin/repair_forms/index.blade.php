@extends('layouts.admin')

@section('title', 'Quản lý phiếu bảo hành')

@section('content')
<div class="container-fluid py-2">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start align-items-md-center mb-4 flex-column flex-md-row gap-2">
        <div>
            <div class="text-muted small mb-1">Phiếu bảo hành</div>
            <h1 class="h4 mb-0 text-gray-800">Quản lý phiếu bảo hành</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.repair-forms.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i>
                Tạo phiếu mới
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small">Tổng phiếu</div>
                            <div class="h4 mb-0">{{ $repairForms->total() }}</div>
                        </div>
                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small">Chưa gửi trả</div>
                            <div class="h4 mb-0">{{ $repairForms->where('status', 'not_returned')->count() }}</div>
                        </div>
                        <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-12">
            <div class="card shadow-sm border-0 rounded-4 h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small">Đã gửi trả</div>
                            <div class="h4 mb-0">{{ $repairForms->where('status', 'returned')->count() }}</div>
                        </div>
                        <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width:44px;height:44px;">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-column flex-md-row gap-2 mb-3">
                <div>
                    <div class="fw-semibold">Danh sách phiếu</div>
                    <div class="text-muted small">Tìm kiếm và quản lý phiếu bảo hành</div>
                </div>
            </div>

            <form method="GET" class="row g-2 align-items-end mb-3">
                <div class="col-md-6">
                    <label for="serial_search" class="form-label fw-semibold">Tìm theo số seri</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="serial_search" name="serial_search" value="{{ request('serial_search') }}" placeholder="Ví dụ: SN123...">
                    </div>
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        Tìm
                    </button>
                    <a href="{{ route('admin.repair-forms.index') }}" class="btn btn-outline-secondary">
                        Làm mới
                    </a>
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
                        <tr>
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
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.repair-forms.show', $repairForm) }}" class="btn btn-sm btn-info" title="Xem chi tiết">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.repair-forms.exportWord', $repairForm) }}" class="btn btn-sm btn-primary" title="In Phiếu" target="_blank">
                                        <i class="bi bi-printer"></i>
                                    </a>
                                    <a href="{{ route('admin.repair-forms.edit', $repairForm) }}" class="btn btn-sm btn-warning" title="Sửa">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.repair-forms.destroy', $repairForm) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phiếu này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
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
});
</script>
@endsection 