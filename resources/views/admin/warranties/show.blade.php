@extends('layouts.admin')

@section('title', 'Chi tiết bảo hành')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0" style="color: #2c3e50; font-weight: 700;">
                <i class="bi bi-shield-check me-2" style="color: #3498db;"></i> Chi tiết bảo hành
            </h1>
            <p class="text-muted mb-0" style="font-size: 1.1em;">Thông tin chi tiết bảo hành #{{ $warranty->id }}</p>
        </div>
        <div class="d-flex gap-3">
            <a href="{{ route('admin.warranties.index') }}" class="btn btn-secondary" style="border-radius: 12px; padding: 12px 24px; font-weight: 600;">
                <i class="bi bi-arrow-left me-2"></i> Quay lại
            </a>
            <a href="{{ route('admin.warranties.edit', $warranty) }}" class="btn btn-warning" style="border-radius: 12px; padding: 12px 24px; font-weight: 600;">
                <i class="bi bi-pencil me-2"></i> Sửa
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Thông tin chính -->
        <div class="col-lg-8">
            <!-- Thông tin bảo hành -->
            <div class="card shadow mb-4" style="border: none; border-radius: 16px;">
                <div class="card-header py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 16px 16px 0 0;">
                    <h6 class="m-0 font-weight-bold" style="font-size: 1.2em;">
                        <i class="bi bi-info-circle me-2"></i> Thông tin bảo hành
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted mb-1">Số seri:</label>
                            <p class="mb-0 fs-5" style="color: #2c3e50;">{{ $warranty->serial_number }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted mb-1">Mã hóa đơn:</label>
                            <p class="mb-0 fs-5" style="color: #2c3e50;">{{ $warranty->invoice_number ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted mb-1">Sản phẩm / Model:</label>
                            <p class="mb-0 fs-5" style="color: #2c3e50;">
                                {{ $warranty->model_name ?? optional($warranty->product)->name ?? '-' }}
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted mb-1">Ngày mua:</label>
                            <p class="mb-0 fs-5" style="color: #2c3e50;">{{ $warranty->purchase_date->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted mb-1">Ngày bắt đầu bảo hành:</label>
                            <p class="mb-0 fs-5" style="color: #2c3e50;">{{ $warranty->warranty_start_date->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted mb-1">Ngày kết thúc bảo hành:</label>
                            <p class="mb-0 fs-5" style="color: #2c3e50;">{{ $warranty->warranty_end_date->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted mb-1">Thời hạn bảo hành:</label>
                            <p class="mb-0 fs-5" style="color: #2c3e50;">{{ $warranty->warranty_period_months }} tháng</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted mb-1">Trạng thái:</label>
                            <div>
                                <span class="badge" style="background: {{ $warranty->warranty_status_color == 'success' ? 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)' : ($warranty->warranty_status_color == 'danger' ? 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)' : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)') }}; color: white; border-radius: 20px; padding: 8px 16px; font-weight: 600; font-size: 1em;">
                                    {{ $warranty->warranty_status_text }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted mb-1">Thời gian còn lại:</label>
                            <p class="mb-0 fs-5" style="color: {{ $warranty->is_expired ? '#e74c3c' : '#27ae60' }};">
                                @if($warranty->is_expired)
                                    <span class="text-danger">{{ $warranty->expired_time_text }}</span>
                                @else
                                    <span class="text-success">{{ $warranty->remaining_time_text }}</span>
                                @endif
                            </p>
                        </div>
                        @if($warranty->notes)
                        <div class="col-12 mb-3">
                            <label class="fw-bold text-muted mb-1">Ghi chú:</label>
                            <p class="mb-0" style="color: #2c3e50;">{{ $warranty->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Thông tin sản phẩm chi tiết (nếu có liên kết sản phẩm) -->
            @if($warranty->product)
            <div class="card shadow mb-4" style="border: none; border-radius: 16px;">
                <div class="card-header py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 16px 16px 0 0;">
                    <h6 class="m-0 font-weight-bold" style="font-size: 1.2em;">
                        <i class="bi bi-box-seam me-2"></i> Thông tin sản phẩm
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center">
                        @if($warranty->product->image)
                            <img src="{{ asset('images/products/' . $warranty->product->image) }}" 
                                 alt="{{ $warranty->product->name }}" 
                                 style="width: 100px; height: 100px; object-fit: cover; border-radius: 12px; margin-right: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                        @endif
                        <div>
                            <h5 class="mb-2" style="color: #2c3e50;">{{ $warranty->product->name }}</h5>
                            @if($warranty->product->category)
                                <p class="text-muted mb-0">{{ $warranty->product->category->name }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Thông tin khách hàng -->
            @if($warranty->customer_name || $warranty->customer_phone || $warranty->customer_email || $warranty->customer_address)
            <div class="card shadow mb-4" style="border: none; border-radius: 16px;">
                <div class="card-header py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 16px 16px 0 0;">
                    <h6 class="m-0 font-weight-bold" style="font-size: 1.2em;">
                        <i class="bi bi-person me-2"></i> Thông tin khách hàng
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        @if($warranty->customer_name)
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted mb-1">Tên khách hàng:</label>
                            <p class="mb-0 fs-5" style="color: #2c3e50;">{{ $warranty->customer_name }}</p>
                        </div>
                        @endif
                        @if($warranty->customer_phone)
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted mb-1">Số điện thoại:</label>
                            <p class="mb-0 fs-5" style="color: #2c3e50;">{{ $warranty->customer_phone }}</p>
                        </div>
                        @endif
                        @if($warranty->customer_email)
                        <div class="col-md-6 mb-3">
                            <label class="fw-bold text-muted mb-1">Email:</label>
                            <p class="mb-0 fs-5" style="color: #2c3e50;">{{ $warranty->customer_email }}</p>
                        </div>
                        @endif
                        @if($warranty->customer_address)
                        <div class="col-12 mb-3">
                            <label class="fw-bold text-muted mb-1">Địa chỉ:</label>
                            <p class="mb-0" style="color: #2c3e50;">{{ $warranty->customer_address }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Danh sách yêu cầu bảo hành -->
            <div class="card shadow mb-4" style="border: none; border-radius: 16px;">
                <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 16px 16px 0 0;">
                    <h6 class="m-0 font-weight-bold" style="font-size: 1.2em;">
                        <i class="bi bi-list-check me-2"></i> Yêu cầu bảo hành ({{ $warranty->claims->count() }})
                    </h6>
                </div>
                <div class="card-body p-4">
                    @if($warranty->claims->count() > 0)
                        @php
                            $claimsAsc = $warranty->claims->sortBy('created_at')->values();
                            $claimSeqMap = $claimsAsc->mapWithKeys(function ($c, $i) {
                                return [$c->id => $i + 1];
                            });
                        @endphp
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Lần</th>
                                        <th>Mã yêu cầu</th>
                                        <th>Ngày yêu cầu</th>
                                        <th>Hư</th>
                                        <th>Sửa</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($warranty->claims as $claim)
                                    <tr>
                                        <td class="fw-bold">{{ $claimSeqMap[$claim->id] ?? '-' }}</td>
                                        <td>{{ $claim->claim_number }}</td>
                                        <td>{{ $claim->claim_date->format('d/m/Y') }}</td>
                                        <td>{{ Str::limit($claim->issue_description, 50) }}</td>
                                        <td>{{ Str::limit($claim->repair_notes, 50) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $claim->status_color }}">
                                                {{ $claim->status_text }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.warranties.claims') }}?claim_id={{ $claim->id }}" class="btn btn-sm btn-primary">
                                                <i class="bi bi-eye"></i> Xem
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">Chưa có yêu cầu bảo hành nào.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Lịch sử thay đổi trạng thái -->
            <div class="card shadow mb-4" style="border: none; border-radius: 16px;">
                <div class="card-header py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 16px 16px 0 0;">
                    <h6 class="m-0 font-weight-bold" style="font-size: 1.2em;">
                        <i class="bi bi-clock-history me-2"></i> Lịch sử thay đổi
                    </h6>
                </div>
                <div class="card-body p-4">
                    @if($warranty->statuses->count() > 0)
                        <div class="timeline">
                            @foreach($warranty->statuses as $status)
                            <div class="mb-3 pb-3" style="border-bottom: 1px solid #e9ecef;">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <span class="badge bg-{{ $status->status === 'created' ? 'success' : ($status->status === 'expired' ? 'warning' : 'info') }}">
                                        {{ $status->status === 'created' ? 'Tạo mới' : ($status->status === 'expired' ? 'Hết hạn' : ucfirst($status->status)) }}
                                    </span>
                                    <small class="text-muted">{{ $status->created_at->format('d/m/Y H:i') }}</small>
                                </div>
                                @if($status->notes)
                                    <p class="mb-0 small text-muted">{{ $status->notes }}</p>
                                @endif
                                <small class="text-muted">Bởi: {{ $status->changed_by ?? 'Hệ thống' }}</small>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">Chưa có lịch sử thay đổi.</p>
                    @endif
                </div>
            </div>

            <!-- Thông tin nhanh -->
            <div class="card shadow" style="border: none; border-radius: 16px;">
                <div class="card-header py-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 16px 16px 0 0;">
                    <h6 class="m-0 font-weight-bold" style="font-size: 1.2em;">
                        <i class="bi bi-info-circle me-2"></i> Thông tin nhanh
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="fw-bold text-muted mb-1">Ngày tạo:</label>
                        <p class="mb-0">{{ $warranty->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold text-muted mb-1">Cập nhật lần cuối:</label>
                        <p class="mb-0">{{ $warranty->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="d-grid gap-2 mt-4">
                        <a href="{{ route('admin.warranties.edit', $warranty) }}" class="btn btn-warning">
                            <i class="bi bi-pencil me-2"></i> Sửa bảo hành
                        </a>
                        <form action="{{ route('admin.warranties.destroy', $warranty) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa bảo hành này?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-trash me-2"></i> Xóa bảo hành
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
