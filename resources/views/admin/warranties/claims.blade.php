@extends('layouts.admin')

@section('title', 'Quản lý yêu cầu bảo hành')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-list-check"></i> Quản lý yêu cầu bảo hành
            </h1>
            <p class="text-muted">Xử lý các yêu cầu bảo hành từ khách hàng</p>
        </div>
        <a href="{{ route('admin.warranties.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Chờ xử lý
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $claims->where('claim_status', 'pending')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Đã duyệt
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $claims->where('claim_status', 'approved')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Đang sửa chữa
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $claims->where('claim_status', 'in_progress')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-tools fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Hoàn thành
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $claims->where('claim_status', 'completed')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-check2-all fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách yêu cầu bảo hành</h6>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Mã yêu cầu</th>
                            <th>Số seri</th>
                            <th>Khách hàng</th>
                            <th>Ngày yêu cầu</th>
                            <th>Trạng thái</th>
                            <th>Dự kiến</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($claims as $claim)
                        <tr>
                            <td>
                                <strong>{{ $claim->claim_number }}</strong>
                                @if($claim->is_overdue)
                                    <br><small class="text-danger">Quá hạn {{ $claim->days_overdue }} ngày</small>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $claim->warranty->serial_number }}</strong>
                                @if($claim->warranty->product)
                                <br><small class="text-muted">{{ $claim->warranty->product->name }}</small>
                                @endif
                            </td>
                            <td>
                                @if($claim->warranty->customer_name)
                                <strong>{{ $claim->warranty->customer_name }}</strong>
                                @endif
                                @if($claim->warranty->customer_phone)
                                <br><small class="text-muted">{{ $claim->warranty->customer_phone }}</small>
                                @endif
                            </td>
                            <td>{{ $claim->claim_date->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-{{ $claim->status_color }}">
                                    {{ $claim->status_text }}
                                </span>
                            </td>
                            <td>
                                @if($claim->estimated_completion_date)
                                    {{ $claim->estimated_completion_date->format('d/m/Y') }}
                                @else
                                    <span class="text-muted">Chưa có</span>
                                @endif
                            </td>
                                                               <td>
                                       <div class="btn-group" role="group">
                                                                                       <a href="{{ route('admin.repair-forms.createFromClaim', $claim) }}" 
                                              class="btn btn-sm btn-outline-success" 
                                              title="Tạo phiếu bảo hành">
                                               <i class="bi bi-file-earmark-text"></i>
                                           </a>
                                           <button type="button" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   data-bs-toggle="modal" 
                                                   data-bs-target="#claimModal{{ $claim->id }}"
                                                   title="Cập nhật trạng thái">
                                               <i class="bi bi-pencil"></i>
                                           </button>
                                       </div>
                                   </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $claims->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Modals for updating claim status -->
@foreach($claims as $claim)
<div class="modal fade" id="claimModal{{ $claim->id }}" tabindex="-1" aria-labelledby="claimModalLabel{{ $claim->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="claimModalLabel{{ $claim->id }}">
                    Cập nhật yêu cầu bảo hành: {{ $claim->claim_number }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.warranties.claims.update-status', $claim) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Số seri:</label>
                                <p class="mb-0">{{ $claim->warranty->serial_number }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Khách hàng:</label>
                                <p class="mb-0">{{ $claim->warranty->customer_name }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Số điện thoại:</label>
                                <p class="mb-0">{{ $claim->warranty->customer_phone }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ngày yêu cầu:</label>
                                <p class="mb-0">{{ $claim->claim_date->format('d/m/Y') }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Mô tả lỗi:</label>
                                <p class="mb-0">{{ Str::limit($claim->issue_description, 100) }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Khiếu nại:</label>
                                <p class="mb-0">{{ Str::limit($claim->customer_complaint, 100) }}</p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="claim_status_{{ $claim->id }}" class="form-label fw-bold">Trạng thái *</label>
                                <select class="form-select" id="claim_status_{{ $claim->id }}" name="claim_status" required>
                                    <option value="pending" {{ $claim->claim_status == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                                    <option value="approved" {{ $claim->claim_status == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                                    <option value="rejected" {{ $claim->claim_status == 'rejected' ? 'selected' : '' }}>Từ chối</option>
                                    <option value="in_progress" {{ $claim->claim_status == 'in_progress' ? 'selected' : '' }}>Đang sửa chữa</option>
                                    <option value="completed" {{ $claim->claim_status == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="estimated_completion_date_{{ $claim->id }}" class="form-label fw-bold">Dự kiến hoàn thành</label>
                                <input type="date" 
                                       class="form-control" 
                                       id="estimated_completion_date_{{ $claim->id }}" 
                                       name="estimated_completion_date" 
                                       value="{{ $claim->estimated_completion_date ? $claim->estimated_completion_date->format('Y-m-d') : '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="repair_cost_{{ $claim->id }}" class="form-label fw-bold">Chi phí sửa chữa (VNĐ)</label>
                                <input type="number" 
                                       class="form-control" 
                                       id="repair_cost_{{ $claim->id }}" 
                                       name="repair_cost" 
                                       value="{{ $claim->repair_cost }}" 
                                       min="0" 
                                       step="1000">
                            </div>
                            <div class="mb-3">
                                <label for="technician_name_{{ $claim->id }}" class="form-label fw-bold">Kỹ thuật viên</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="technician_name_{{ $claim->id }}" 
                                       name="technician_name" 
                                       value="{{ $claim->technician_name }}">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="admin_notes_{{ $claim->id }}" class="form-label fw-bold">Ghi chú admin</label>
                        <textarea class="form-control" 
                                  id="admin_notes_{{ $claim->id }}" 
                                  name="admin_notes" 
                                  rows="3">{{ $claim->admin_notes }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="repair_notes_{{ $claim->id }}" class="form-label fw-bold">Ghi chú sửa chữa</label>
                        <textarea class="form-control" 
                                  id="repair_notes_{{ $claim->id }}" 
                                  name="repair_notes" 
                                  rows="3">{{ $claim->repair_notes }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Vietnamese.json"
        },
        "pageLength": 25,
        "order": [[3, "desc"]]
    });
});
</script>
@endsection 