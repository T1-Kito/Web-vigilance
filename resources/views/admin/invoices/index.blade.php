@extends('layouts.admin')

@section('title', 'Hóa đơn bán hàng')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h4 fw-bold mb-1">Danh sách hóa đơn</h1>
            <div class="text-muted">Quản lý chứng từ hóa đơn phát hành từ đơn bán hàng.</div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-end" method="GET" action="{{ route('admin.invoices.index') }}">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Mã đơn hàng</label>
                    <input type="text" name="order_code" class="form-control" value="{{ $filters['order_code'] ?? '' }}" placeholder="VD: SO202604-0001">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Trạng thái hóa đơn</label>
                    <select name="status" class="form-select">
                        <option value="" @selected(($filters['status'] ?? '') === '')>Tất cả</option>
                        <option value="issued" @selected(($filters['status'] ?? '') === 'issued')>Đã phát hành</option>
                        <option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Nháp</option>
                        <option value="cancelled" @selected(($filters['status'] ?? '') === 'cancelled')>Đã hủy</option>
                    </select>
                </div>
                <div class="col-md-5 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Lọc dữ liệu</button>
                    <a href="{{ route('admin.invoices.index') }}" class="btn btn-light border">Xóa lọc</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Mã hóa đơn</th>
                            <th>Đơn hàng</th>
                            <th>Ngày phát hành</th>
                            <th>Trạng thái</th>
                            <th>Tổng tiền</th>
                            <th class="text-end pe-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td class="fw-semibold">{{ $invoice->invoice_code }}</td>
                            <td>{{ $invoice->salesOrder->sales_order_code ?? ($invoice->order->order_code ?? ('#' . ($invoice->sales_order_id ?? $invoice->order_id))) }}</td>
                            <td>{{ optional($invoice->issued_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge bg-{{ $invoice->status === 'issued' ? 'success' : ($invoice->status === 'cancelled' ? 'danger' : 'secondary') }}">
                                    {{ $invoice->status === 'issued' ? 'Đã phát hành' : ($invoice->status === 'cancelled' ? 'Đã hủy' : 'Nháp') }}
                                </span>
                            </td>
                            <td class="fw-bold text-danger">{{ number_format((float) $invoice->total_amount, 0, ',', '.') }}đ</td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary">Chi tiết</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Chưa có hóa đơn nào.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">{{ $invoices->links('pagination::bootstrap-5') }}</div>
    </div>
</div>
@endsection
