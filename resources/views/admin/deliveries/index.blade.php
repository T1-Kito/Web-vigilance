@extends('layouts.admin')

@section('title', 'Phiếu xuất kho')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h4 fw-bold mb-1">Danh sách phiếu xuất kho</h1>
            <div class="text-muted">Quản lý chứng từ xuất kho liên quan đơn bán.</div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-end" method="GET" action="{{ route('admin.deliveries.index') }}">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Mã đơn hàng</label>
                    <input type="text" name="order_code" class="form-control" value="{{ $filters['order_code'] ?? '' }}" placeholder="VD: OD250406ABC123">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Trạng thái phiếu</label>
                    <select name="status" class="form-select">
                        <option value="" @selected(($filters['status'] ?? '') === '')>Tất cả</option>
                        <option value="confirmed" @selected(($filters['status'] ?? '') === 'confirmed')>Đã xuất</option>
                        <option value="cancelled" @selected(($filters['status'] ?? '') === 'cancelled')>Đã hủy</option>
                        <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Nháp</option>
                    </select>
                </div>
                <div class="col-md-5 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Lọc dữ liệu</button>
                    <a href="{{ route('admin.deliveries.index') }}" class="btn btn-light border">Xóa lọc</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive table-actions-visible">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Mã phiếu</th>
                            <th>Đơn hàng</th>
                            <th>Ngày xuất</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($deliveries as $delivery)
                        <tr>
                            <td class="fw-semibold">{{ $delivery->delivery_code }}</td>
                            <td>{{ $delivery->salesOrder->sales_order_code ?? $delivery->order->order_code ?? ('#' . ($delivery->sales_order_id ?? $delivery->order_id)) }}</td>
                            <td>{{ optional($delivery->delivered_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge bg-{{ $delivery->status === 'confirmed' ? 'success' : ($delivery->status === 'cancelled' ? 'danger' : 'secondary') }}">
                                    {{ $delivery->status === 'confirmed' ? 'Đã xuất' : ($delivery->status === 'cancelled' ? 'Đã hủy' : 'Nháp') }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="dropdown d-inline-block">
                                    <button
                                        class="btn btn-link text-secondary p-1 rounded-2"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                        title="Thao tác"
                                        style="background: rgba(15,23,42,0.05); border-radius: 999px; width: 36px; height: 36px; display:inline-flex; align-items:center; justify-content:center;"
                                    >
                                        <i class="bi bi-three-dots-vertical fs-5 lh-1"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-right shadow-sm border-0 small">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.deliveries.show', $delivery) }}">
                                                <i class="bi bi-eye me-2 text-primary"></i>Chi tiết
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.deliveries.destroy', $delivery) }}" onsubmit="return confirm('Xóa phiếu xuất kho này?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i>Xóa
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Chưa có phiếu xuất kho.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">{{ $deliveries->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
<style>
.table-actions-visible {
    overflow-x: auto !important;
    overflow-y: visible !important;
}
.table-actions-visible .dropdown {
    position: static;
}
.table-actions-visible .dropdown-menu {
    z-index: 2000 !important;
}
</style>
@endsection
