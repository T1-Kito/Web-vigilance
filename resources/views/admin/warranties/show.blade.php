@extends('layouts.admin')

@section('title', 'Chi tiết bảo hành')

@section('content')
<div class="container-fluid py-3 warranty-detail-page">
    <style>
        .warranty-shell {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
        }
        .warranty-shell__header {
            padding: 16px 18px;
            border-bottom: 1px solid #e5e7eb;
            background: #fff;
        }
        .warranty-title {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 700;
            color: #111827;
        }
        .warranty-subtitle {
            margin: 2px 0 0;
            color: #6b7280;
            font-size: .9rem;
        }
        .warranty-shell__body {
            padding: 16px 18px 18px;
            background: #fff;
        }
        .master-grid {
            border: 1px solid #edf0f4;
            border-radius: 10px;
            padding: 14px;
            background: #fff;
        }
        .master-item {
            margin-bottom: 12px;
        }
        .master-item:last-child {
            margin-bottom: 0;
        }
        .master-label {
            display: block;
            color: #6b7280;
            font-size: .8rem;
            font-weight: 600;
            margin-bottom: 2px;
        }
        .master-value {
            color: #111827;
            font-size: .96rem;
            font-weight: 600;
            word-break: break-word;
        }
        .master-value.normal {
            font-weight: 500;
        }
        .section-title {
            color: #111827;
            font-weight: 700;
            font-size: .98rem;
            margin-bottom: 10px;
        }
        .nav-tabs.warranty-tabs {
            border-bottom: 1px solid #e5e7eb;
            gap: 4px;
        }
        .nav-tabs.warranty-tabs .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            color: #4b5563;
            font-weight: 600;
            padding: 10px 14px;
            border-radius: 0;
        }
        .nav-tabs.warranty-tabs .nav-link.active {
            color: #1d4ed8;
            border-bottom-color: #1d4ed8;
            background: transparent;
        }
        .tab-pane-content {
            padding: 14px 4px 2px;
        }
        .inline-card {
            border: 1px solid #eef2f7;
            border-radius: 10px;
            padding: 12px;
            background: #fff;
        }
        .timeline-item {
            border-bottom: 1px solid #edf0f4;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .timeline-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
        }
        .status-active { color: #047857; }
        .status-expired { color: #b91c1c; }
    </style>

    <div class="warranty-shell">
        <div class="warranty-shell__header d-flex justify-content-between align-items-start gap-3 flex-wrap">
            <div>
                <h1 class="warranty-title">Chi tiết bảo hành #{{ $warranty->id }}</h1>
                <p class="warranty-subtitle">Theo dõi thông tin tổng quan và lịch sử xử lý bảo hành.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.warranties.index') }}" class="btn btn-outline-secondary btn-sm">Quay lại</a>
                <a href="{{ route('admin.warranties.edit', $warranty) }}" class="btn btn-primary btn-sm">Sửa</a>
            </div>
        </div>

        <div class="warranty-shell__body">
            <div class="section-title">Thông tin chung</div>
            <div class="master-grid mb-3">
                <div class="row g-2">
                    <div class="col-md-4 master-item">
                        <span class="master-label">Số seri</span>
                        <div class="master-value">{{ $warranty->serial_number ?: '-' }}</div>
                    </div>
                    <div class="col-md-4 master-item">
                        <span class="master-label">Mã hóa đơn</span>
                        <div class="master-value normal">{{ $warranty->invoice_number ?: '-' }}</div>
                    </div>
                    <div class="col-md-4 master-item">
                        <span class="master-label">Sản phẩm / Model</span>
                        <div class="master-value normal">{{ $warranty->model_name ?? optional($warranty->product)->name ?? '-' }}</div>
                    </div>

                    <div class="col-md-4 master-item">
                        <span class="master-label">Tên khách hàng</span>
                        <div class="master-value normal">{{ $warranty->customer_name ?: '-' }}</div>
                    </div>

                    <div class="col-md-4 master-item">
                        <span class="master-label">Ngày mua</span>
                        <div class="master-value normal">{{ optional($warranty->purchase_date)->format('d/m/Y') ?: '-' }}</div>
                    </div>
                    <div class="col-md-4 master-item">
                        <span class="master-label">Bắt đầu bảo hành</span>
                        <div class="master-value normal">{{ optional($warranty->warranty_start_date)->format('d/m/Y') ?: '-' }}</div>
                    </div>
                    <div class="col-md-4 master-item">
                        <span class="master-label">Kết thúc bảo hành</span>
                        <div class="master-value normal">{{ optional($warranty->warranty_end_date)->format('d/m/Y') ?: '-' }}</div>
                    </div>

                    <div class="col-md-4 master-item">
                        <span class="master-label">Thời hạn bảo hành</span>
                        <div class="master-value normal">{{ $warranty->warranty_period_months }} tháng</div>
                    </div>
                    <div class="col-md-4 master-item">
                        <span class="master-label">Trạng thái</span>
                        <div class="master-value {{ $warranty->is_expired ? 'status-expired' : 'status-active' }}">{{ $warranty->warranty_status_text }}</div>
                    </div>
                    <div class="col-md-4 master-item">
                        <span class="master-label">Thời gian còn lại</span>
                        <div class="master-value {{ $warranty->is_expired ? 'status-expired' : 'status-active' }}">
                            {{ $warranty->is_expired ? $warranty->expired_time_text : $warranty->remaining_time_text }}
                        </div>
                    </div>
                </div>
            </div>

            <ul class="nav nav-tabs warranty-tabs" id="warrantyDetailTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-detail-btn" data-bs-toggle="tab" data-bs-target="#tab-detail" type="button" role="tab" aria-controls="tab-detail" aria-selected="true">Thông tin chi tiết</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-claims-btn" data-bs-toggle="tab" data-bs-target="#tab-claims" type="button" role="tab" aria-controls="tab-claims" aria-selected="false">Yêu cầu bảo hành ({{ $warranty->claims->count() }})</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-history-btn" data-bs-toggle="tab" data-bs-target="#tab-history" type="button" role="tab" aria-controls="tab-history" aria-selected="false">Lịch sử thay đổi</button>
                </li>
            </ul>

            <div class="tab-content" id="warrantyDetailTabsContent">
                <div class="tab-pane fade show active" id="tab-detail" role="tabpanel" aria-labelledby="tab-detail-btn">
                    <div class="tab-pane-content">
                        <div class="row g-3">
                            @if($warranty->product)
                            <div class="col-lg-6">
                                <div class="inline-card h-100">
                                    <div class="section-title mb-2">Sản phẩm</div>
                                    <div class="d-flex gap-3 align-items-center">
                                        @if($warranty->product->image)
                                            <img src="{{ asset('images/products/' . $warranty->product->image) }}" alt="{{ $warranty->product->name }}" style="width:72px;height:72px;object-fit:cover;border-radius:8px;border:1px solid #eef2f7;">
                                        @endif
                                        <div>
                                            <div class="master-value normal">{{ $warranty->product->name }}</div>
                                            <div class="master-label mb-0">{{ optional($warranty->product->category)->name ?: '-' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="col-lg-6">
                                <div class="inline-card h-100">
                                    <div class="section-title mb-2">Thông tin nhanh</div>
                                    <div class="master-item">
                                        <span class="master-label">Ngày tạo</span>
                                        <div class="master-value normal">{{ $warranty->created_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                    <div class="master-item">
                                        <span class="master-label">Cập nhật lần cuối</span>
                                        <div class="master-value normal">{{ $warranty->updated_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                    @if($warranty->notes)
                                    <div class="master-item">
                                        <span class="master-label">Ghi chú</span>
                                        <div class="master-value normal">{{ $warranty->notes }}</div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-claims" role="tabpanel" aria-labelledby="tab-claims-btn">
                    <div class="tab-pane-content">
                        @if($warranty->claims->count() > 0)
                            @php
                                $claimsAsc = $warranty->claims->sortBy('created_at')->values();
                                $claimSeqMap = $claimsAsc->mapWithKeys(function ($c, $i) {
                                    return [$c->id => $i + 1];
                                });
                            @endphp
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Lần</th>
                                            <th>Mã yêu cầu</th>
                                            <th>Ngày yêu cầu</th>
                                            <th>Mô tả hư</th>
                                            <th>Ghi chú sửa</th>
                                            <th>Trạng thái</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($warranty->claims as $claim)
                                        <tr>
                                            <td class="fw-semibold">{{ $claimSeqMap[$claim->id] ?? '-' }}</td>
                                            <td>{{ $claim->claim_number }}</td>
                                            <td>{{ optional($claim->claim_date)->format('d/m/Y') }}</td>
                                            <td>{{ Str::limit($claim->issue_description, 60) }}</td>
                                            <td>{{ Str::limit($claim->repair_notes, 60) }}</td>
                                            <td><span class="badge bg-{{ $claim->status_color }}">{{ $claim->status_text }}</span></td>
                                            <td class="text-end">
                                                <a href="{{ route('admin.warranties.claims') }}?claim_id={{ $claim->id }}" class="btn btn-sm btn-outline-primary">Xem</a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-muted">Chưa có yêu cầu bảo hành nào.</div>
                        @endif
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-history" role="tabpanel" aria-labelledby="tab-history-btn">
                    <div class="tab-pane-content">
                        @if($warranty->statuses->count() > 0)
                            @foreach($warranty->statuses as $status)
                                <div class="timeline-item">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <span class="badge bg-{{ $status->status === 'created' ? 'success' : ($status->status === 'expired' ? 'warning' : 'info') }}">
                                            {{ $status->status === 'created' ? 'Tạo mới' : ($status->status === 'expired' ? 'Hết hạn' : ucfirst($status->status)) }}
                                        </span>
                                        <small class="text-muted">{{ $status->created_at->format('d/m/Y H:i') }}</small>
                                    </div>
                                    @if($status->notes)
                                        <div class="small text-muted mt-1">{{ $status->notes }}</div>
                                    @endif
                                    <div class="small text-muted mt-1">Bởi: {{ $status->changed_by ?? 'Hệ thống' }}</div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-muted">Chưa có lịch sử thay đổi.</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <a href="{{ route('admin.warranties.edit', $warranty) }}" class="btn btn-outline-primary btn-sm">Sửa bảo hành</a>
                <form action="{{ route('admin.warranties.destroy', $warranty) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa bảo hành này?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">Xóa bảo hành</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
