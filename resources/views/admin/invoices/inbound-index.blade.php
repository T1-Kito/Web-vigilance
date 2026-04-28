@extends('layouts.admin')

@section('title', 'Hóa đơn đầu vào')

@section('content')
@php
    $supplierCount = $purchaseOrders->sum(fn($po) => 1);
    $itemCount = $purchaseOrders->sum(fn($po) => $po->items->count());
@endphp

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <div class="text-uppercase text-primary fw-bold small mb-1">Quy trình MISA / đầu vào</div>
            <h1 class="h4 fw-bold mb-1">Hóa đơn đầu vào</h1>
            <div class="text-muted">Ghi nhận hóa đơn từ nhà cung cấp, đối chiếu tên hàng và gán vào đơn mua hàng.</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-file-earmark-text me-1"></i>Đơn mua hàng
            </a>
            <a href="{{ route('admin.invoices.index') }}" class="btn btn-primary">
                <i class="bi bi-receipt-cutoff me-1"></i>Hóa đơn đầu ra
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(!empty($receivedInvoice ?? []))
        <div class="alert alert-info">
            <div class="fw-bold">Đã ghi nhận / gán hóa đơn</div>
            <div class="small">Số hóa đơn: {{ $receivedInvoice['invoice_number'] ?? '---' }} | Đơn mua: {{ $receivedInvoice['purchase_order_code'] ?? '---' }} | Trạng thái: {{ $receivedInvoice['status'] ?? 'received' }}</div>
        </div>
    @endif
    @if(!empty($inboundWarnings ?? []))
        <div class="alert alert-warning">
            <div class="fw-bold mb-1">Cảnh báo đối chiếu</div>
            @foreach($inboundWarnings as $warning)
                <div class="small">{{ $warning['message'] }} — {{ $warning['left_label'] }}: {{ $warning['left_name'] }} | {{ $warning['right_label'] }}: {{ $warning['right_name'] }}</div>
            @endforeach
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Đơn mua hàng liên quan</div>
                    <div class="display-6 fw-bold">{{ $purchaseOrders->total() }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Nhà cung cấp có thể đối chiếu</div>
                    <div class="display-6 fw-bold">{{ $supplierCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Dòng hàng chờ so khớp</div>
                    <div class="display-6 fw-bold">{{ $itemCount }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2 align-items-center small">
                <span class="badge bg-success-subtle text-success px-3 py-2">1. Nhận hóa đơn</span>
                <i class="bi bi-chevron-right text-muted"></i>
                <span class="badge bg-warning-subtle text-warning px-3 py-2">2. Đối chiếu tên hàng</span>
                <i class="bi bi-chevron-right text-muted"></i>
                <span class="badge bg-primary-subtle text-primary px-3 py-2">3. Gán vào đơn mua</span>
                <i class="bi bi-chevron-right text-muted"></i>
                <span class="badge bg-dark-subtle text-dark px-3 py-2">4. Khóa chứng từ</span>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Từ khóa</label>
                    <input type="text" name="q" class="form-control" placeholder="Nhà cung cấp / mã PO / MST..." value="{{ $filters['q'] ?? request('q') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Trạng thái</label>
                    <select class="form-select" name="status">
                        <option value="">Tất cả</option>
                        <option value="received" @selected(($filters['status'] ?? request('status')) === 'received')>Đã nhận</option>
                        <option value="matched" @selected(($filters['status'] ?? request('status')) === 'matched')>Đã đối chiếu</option>
                        <option value="assigned" @selected(($filters['status'] ?? request('status')) === 'assigned')>Đã gán</option>
                    </select>
                </div>
                <div class="col-md-5 d-flex gap-2">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-funnel me-1"></i>Lọc</button>
                    <a href="{{ route('admin.invoices.inbound.index') }}" class="btn btn-light border">Xóa lọc</a>
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
                            <th>Đơn mua hàng</th>
                            <th>Nhà cung cấp</th>
                            <th>Số hàng</th>
                            <th>Ngày tạo</th>
                            <th>Quy trình</th>
                            <th class="text-end pe-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($purchaseOrders as $po)
                        @php $status = $po->inbound_status ?? 'received'; @endphp
                        <tr>
                            <td class="fw-semibold">{{ $po->code }}</td>
                            <td>
                                <div class="fw-semibold">{{ $po->supplier_name }}</div>
                                <div class="small text-muted">{{ $po->supplier_tax_code ?: '---' }}</div>
                            </td>
                            <td>{{ $po->items->count() }} dòng</td>
                            <td>{{ optional($po->created_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge bg-{{ $status === 'assigned' ? 'success' : ($status === 'matched' ? 'warning' : 'secondary') }}">
                                    {{ $status === 'assigned' ? 'Đã gán' : ($status === 'matched' ? 'Đã đối chiếu' : 'Đã nhận') }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.purchase-orders.show', $po) }}" class="btn btn-sm btn-outline-primary me-2">
                                    <i class="bi bi-eye me-1"></i>Xem
                                </a>
                                <form method="POST" action="{{ route('admin.invoices.inbound.assign') }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="purchase_order_id" value="{{ $po->id }}">
                                    <input type="hidden" name="invoice_number" value="{{ $po->code }}-INV">
                                    <button class="btn btn-sm btn-warning" type="submit"><i class="bi bi-link-45deg me-1"></i>Gán</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">Chưa có đơn mua hàng nào để theo dõi hóa đơn đầu vào.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $purchaseOrders->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection