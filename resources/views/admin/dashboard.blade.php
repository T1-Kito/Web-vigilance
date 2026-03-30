@extends('layouts.admin')

@section('title', 'Bảng điều khiển')

@section('content')
<div class="container-fluid py-4 px-2 px-lg-3">
    <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-dark">Bảng điều khiển</h1>
            <p class="text-muted mb-0 small">Tổng quan hệ thống Vigilance Admin</p>
        </div>
        <div class="text-muted small">
            <i class="bi bi-calendar3 me-1"></i>{{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 rounded-3 overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase text-muted fw-semibold" style="font-size: 0.7rem; letter-spacing: .06em;">Đơn hàng</div>
                            <div class="fs-3 fw-bold text-primary mt-1">{{ number_format($ordersTotal) }}</div>
                            <div class="small text-muted">Tổng đơn</div>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary" style="width: 48px; height: 48px;">
                            <i class="bi bi-cart-check fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-2 small">
                        <span class="text-warning fw-semibold">{{ number_format($ordersPending) }}</span>
                        <span class="text-muted"> chờ / xử lý</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 rounded-3 overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase text-muted fw-semibold" style="font-size: 0.7rem; letter-spacing: .06em;">Khách hàng</div>
                            <div class="fs-3 fw-bold text-success mt-1">{{ number_format($customersCount) }}</div>
                            <div class="small text-muted">Trong hệ thống</div>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success" style="width: 48px; height: 48px;">
                            <i class="bi bi-person-badge fs-4"></i>
                        </div>
                    </div>
                    <a href="{{ route('admin.customers.index') }}" class="stretched-link text-decoration-none small text-success">Quản lý →</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 rounded-3 overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase text-muted fw-semibold" style="font-size: 0.7rem; letter-spacing: .06em;">Sản phẩm</div>
                            <div class="fs-3 fw-bold text-info mt-1">{{ number_format($productsCount) }}</div>
                            <div class="small text-muted">Danh mục catalogue</div>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-info bg-opacity-10 text-info" style="width: 48px; height: 48px;">
                            <i class="bi bi-tags fs-4"></i>
                        </div>
                    </div>
                    <a href="{{ route('admin.products.index') }}" class="stretched-link text-decoration-none small text-info">Quản lý →</a>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 rounded-3 overflow-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase text-muted fw-semibold" style="font-size: 0.7rem; letter-spacing: .06em;">Hoàn thành</div>
                            <div class="fs-3 fw-bold text-secondary mt-1">{{ number_format($ordersCompleted) }}</div>
                            <div class="small text-muted">Đơn đã hoàn tất</div>
                        </div>
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-secondary bg-opacity-10 text-secondary" style="width: 48px; height: 48px;">
                            <i class="bi bi-check2-circle fs-4"></i>
                        </div>
                    </div>
                    <a href="{{ route('admin.orders.index') }}" class="stretched-link text-decoration-none small text-secondary">Đơn hàng →</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-shield-check me-2 text-primary"></i>Tổng hợp bảo hành</h6>
                    <a href="{{ route('admin.warranties.index') }}" class="btn btn-sm btn-outline-primary rounded-pill">Quản lý bảo hành</a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-4 col-lg">
                            <div class="p-3 rounded-3 bg-light border border-light">
                                <div class="small text-muted text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: .05em;">Phiếu bảo hành</div>
                                <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($warrantiesTotal) }}</div>
                                <div class="small text-muted"><span class="text-success fw-semibold">{{ number_format($warrantiesActive) }}</span> trạng thái đang hoạt động</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg">
                            <div class="p-3 rounded-3 bg-light border border-light">
                                <div class="small text-muted text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: .05em;">Yêu cầu chờ</div>
                                <div class="fs-4 fw-bold text-warning mt-1">{{ number_format($claimsPending) }}</div>
                                <a href="{{ route('admin.warranties.claims') }}" class="small text-decoration-none">Xem yêu cầu →</a>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg">
                            <div class="p-3 rounded-3 bg-light border border-light">
                                <div class="small text-muted text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: .05em;">Đang xử lý</div>
                                <div class="fs-4 fw-bold text-primary mt-1">{{ number_format($claimsInProgress) }}</div>
                                <div class="small text-muted">Đang sửa chữa</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg">
                            <div class="p-3 rounded-3 bg-light border border-light">
                                <div class="small text-muted text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: .05em;">Mở (tổng)</div>
                                <div class="fs-4 fw-bold text-danger mt-1">{{ number_format($claimsOpen) }}</div>
                                <div class="small text-muted">Chưa hoàn thành</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg">
                            <div class="p-3 rounded-3 bg-light border border-light">
                                <div class="small text-muted text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: .05em;">Phiếu BH &amp; sửa chữa</div>
                                <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($repairFormsTotal) }}</div>
                                <a href="{{ route('admin.repair-forms.index') }}" class="small text-decoration-none">Phiếu nhận &amp; trả →</a>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg">
                            <div class="p-3 rounded-3 bg-light border border-light">
                                <div class="small text-muted text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: .05em;">Mượn hàng</div>
                                <div class="fs-4 fw-bold text-indigo mt-1" style="color: #4f46e5;">{{ number_format($borrowActive) }}</div>
                                <a href="{{ route('admin.borrow-requests.index') }}" class="small text-decoration-none">Phiếu mượn →</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-receipt me-2 text-primary"></i>Đơn hàng gần đây</h6>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill">Tất cả đơn</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Ngày</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-4">Tổng</th>
                            <th class="text-center pe-4" style="width: 72px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            @php
                                $total = $order->items->sum(fn ($i) => (float) $i->price * (float) $i->quantity);
                                $st = (string) $order->status;
                            @endphp
                            <tr>
                                <td class="ps-4 fw-semibold">{{ $order->order_code ?? ('VK' . str_pad($order->id, 6, '0', STR_PAD_LEFT)) }}</td>
                                <td>
                                    <div class="fw-medium">{{ $order->receiver_name ?: '—' }}</div>
                                    @if($order->receiver_phone)
                                        <div class="small text-muted">{{ $order->receiver_phone }}</div>
                                    @endif
                                </td>
                                <td class="small text-muted">{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if($st === 'pending' || $st === 'processing')
                                        <span class="badge rounded-pill text-bg-warning">Chờ xử lý</span>
                                    @elseif($st === 'cancelled')
                                        <span class="badge rounded-pill text-bg-secondary">Đã hủy</span>
                                    @else
                                        <span class="badge rounded-pill text-bg-success">Hoàn thành</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4 fw-semibold text-danger">{{ number_format($total, 0, ',', '.') }}đ</td>
                                <td class="text-center pe-4">
                                    <div class="dropdown">
                                        <button class="btn btn-link text-secondary p-1 rounded-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Thao tác">
                                            <i class="bi bi-three-dots-vertical fs-5 lh-1"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 small">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.orders.show', $order) }}">
                                                    <i class="bi bi-eye me-2 text-primary"></i>Xem
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">Chưa có đơn hàng.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
