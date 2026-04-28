@extends('layouts.admin')

@section('title', 'Danh sách phiếu trả')

@section('content')
<style>
    .rr-page { color: #0f172a; }
    .rr-page .card {
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        box-shadow: none;
        background: #fff;
    }
    .rr-stat {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .rr-stat .rr-icon {
        width: 44px;
        height: 44px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .rr-filter .form-control,
    .rr-filter .form-select {
        border-radius: 10px;
        border-color: #dbe2ea;
        min-height: 42px;
    }
    .rr-filter .form-control:focus,
    .rr-filter .form-select:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 0.2rem rgba(59,130,246,.12);
    }
    .rr-btn {
        border-radius: 10px !important;
        min-height: 42px;
        font-weight: 600;
    }
    .rr-table thead th {
        background: #f8fafc;
        color: #334155;
        border-bottom: 1px solid #e2e8f0;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .02em;
        white-space: nowrap;
    }
    .rr-table td {
        border-color: #eef2f7;
        vertical-align: middle;
    }
    .rr-status-badge {
        border-radius: 999px;
        padding: 6px 12px;
        font-weight: 700;
    }
    .rr-clickable-row { cursor: pointer; }
</style>

<div class="container-fluid py-2 rr-page">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h1 class="h4 mb-1 text-gray-800">Danh sách phiếu trả</h1>
            <div class="text-muted small">Quản lý phiếu đã gửi trả và file PDF đính kèm.</div>
        </div>
        <a href="{{ route('admin.repair-forms.index') }}" class="btn btn-outline-secondary rr-btn">
            <i class="bi bi-arrow-left me-1"></i>Phiếu bảo hành
        </a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="card h-100">
                <div class="card-body rr-stat">
                    <div>
                        <div class="text-muted small">Tổng phiếu trả</div>
                        <div class="h4 mb-0">{{ $totalReturned }}</div>
                    </div>
                    <span class="rr-icon bg-primary-subtle text-primary"><i class="bi bi-journal-check"></i></span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card h-100">
                <div class="card-body rr-stat">
                    <div>
                        <div class="text-muted small">Có file PDF</div>
                        <div class="h4 mb-0">{{ $withFileCount }}</div>
                    </div>
                    <span class="rr-icon bg-success-subtle text-success"><i class="bi bi-file-earmark-pdf"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end mb-3 rr-filter">
                <div class="col-lg-6">
                    <input type="text" class="form-control" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm theo số phiếu, số seri, khách hàng...">
                </div>
                <div class="col-lg-3 col-md-5">
                    <select name="has_file" class="form-select">
                        <option value="">File PDF: Tất cả</option>
                        <option value="yes" {{ request('has_file') === 'yes' ? 'selected' : '' }}>Có file PDF</option>
                        <option value="no" {{ request('has_file') === 'no' ? 'selected' : '' }}>Chưa có file PDF</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-7 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rr-btn w-100">Lọc</button>
                    <a href="{{ route('admin.repair-forms.returns') }}" class="btn btn-outline-secondary rr-btn w-100">Làm mới</a>
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
                <table class="table table-hover align-middle rr-table">
                    <thead>
                        <tr>
                            <th>Số phiếu</th>
                            <th>Khách hàng</th>
                            <th>Số seri</th>
                            <th>Ngày trả</th>
                            <th>PDF</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($returnForms as $form)
                        <tr class="rr-clickable-row" onclick="if(!event.target.closest('a,button,form,input,select,textarea,label')){window.location='{{ route('admin.repair-forms.show', $form) }}'}">
                            <td>
                                <div class="fw-semibold">{{ $form->form_number }}</div>
                                <div class="small text-muted">Cập nhật: {{ optional($form->updated_at)->format('d/m/Y H:i') }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $form->customer_company }}</div>
                                <div class="small text-muted">{{ $form->contact_person ?: '—' }}</div>
                            </td>
                            <td>{{ $form->serial_numbers }}</td>
                            <td>{{ optional($form->actual_return_date)->format('d/m/Y') ?: '—' }}</td>
                            <td>
                                @if($form->return_file_path)
                                    <span class="badge text-bg-success rr-status-badge">Đã upload</span>
                                @else
                                    <span class="badge text-bg-secondary rr-status-badge">Chưa có</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('admin.repair-forms.show', $form) }}" class="btn btn-sm btn-outline-primary">Chi tiết</a>
                                    @if($form->return_file_path)
                                        <a href="{{ route('admin.repair-forms.downloadReturnFile', $form) }}" class="btn btn-sm btn-success">Tải PDF</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Chưa có phiếu trả nào.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-center">
                {{ $returnForms->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
