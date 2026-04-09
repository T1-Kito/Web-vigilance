@extends('layouts.admin')

@section('title', 'Đơn bán ngoài')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Danh sách đơn hàng</h1>
            <div class="text-muted">Đơn hàng tạo từ báo giá, tách biệt với đơn web.</div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form class="row g-2" method="GET" action="{{ route('admin.sales-orders.index') }}">
                <div class="col-md-5">
                    <input class="form-control" type="text" name="q" value="{{ request('q') }}" placeholder="Số đơn hàng / khách / MST / SĐT">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">Tất cả trạng thái đơn</option>
                        @foreach(['pending' => 'Chờ xử lý', 'processing' => 'Đang xử lý', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'] as $k => $lb)
                            <option value="{{ $k }}" @selected(request('status') === $k)>{{ $lb }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="payment_status">
                        <option value="">Tất cả công nợ</option>
                        @foreach(['unpaid' => 'Chưa thanh toán', 'partial' => 'Thanh toán một phần', 'paid' => 'Đã thanh toán', 'overdue' => 'Quá hạn'] as $k => $lb)
                            <option value="{{ $k }}" @selected(request('payment_status') === $k)>{{ $lb }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Lọc</button>
                    <a class="btn btn-light border" href="{{ route('admin.sales-orders.index') }}">Xóa lọc</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive sales-order-table-wrap">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Số đơn hàng</th>
                            <th>Khách hàng</th>
                            <th>Ngày tạo</th>
                            <th>Trạng thái đơn</th>
                            <th>Trạng thái công nợ</th>
                            <th class="text-end pe-3">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($salesOrders as $so)
                        <tr class="so-row" data-href="{{ route('admin.sales-orders.show', $so) }}">
                            <td class="fw-semibold">{{ $so->sales_order_code }}</td>
                            <td>{{ $so->invoice_company_name ?: $so->receiver_name }}</td>
                            <td>{{ optional($so->created_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                @php
                                    $badge = $so->status === 'completed' ? 'success' : ($so->status === 'cancelled' ? 'danger' : ($so->status === 'processing' ? 'warning' : 'secondary'));
                                @endphp
                                <span class="badge bg-{{ $badge }}">{{ $so->status }}</span>
                            </td>
                            <td>
                                @php
                                    $paymentStatus = (string) optional($so->debt)->status ?: (string) ($so->payment_status ?? 'unpaid');
                                    $pBadge = $paymentStatus === 'paid' ? 'success' : (($paymentStatus === 'partial') ? 'warning' : (($paymentStatus === 'overdue') ? 'danger' : 'secondary'));
                                    $pLabel = [
                                        'unpaid' => 'Chưa thanh toán',
                                        'partial' => 'Thanh toán một phần',
                                        'paid' => 'Đã thanh toán',
                                        'overdue' => 'Quá hạn',
                                    ][$paymentStatus] ?? $paymentStatus;
                                @endphp
                                <span class="badge bg-{{ $pBadge }}">{{ $pLabel }}</span>
                            </td>
                            <td class="text-end pe-3" onclick="event.stopPropagation();">
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
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 small">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.sales-orders.show', $so) }}">
                                                <i class="bi bi-eye me-2 text-primary"></i>Chi tiết
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.sales-orders.destroy', $so) }}" onsubmit="return confirm('Xóa đơn bán này?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i>Xóa
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.sales-orders.destroy', $so) }}" onsubmit="return confirm('XÓA CƯỠNG BỨC: sẽ xóa cả phiếu xuất kho + hóa đơn liên quan. Tiếp tục?');">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="force_delete" value="1">
                                                <button type="submit" class="dropdown-item text-danger fw-semibold">
                                                    <i class="bi bi-exclamation-triangle me-2"></i>Xóa cưỡng bức
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">Chưa có đơn hàng.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">{{ $salesOrders->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

<style>
    .so-row { transition: background-color .16s ease; cursor: pointer; }
    .so-row:hover { background: #eaf3ff; }
    .so-row:hover td { background: #eaf3ff; }

    .sales-order-table-wrap td,
    .sales-order-table-wrap th {
        overflow: visible;
        vertical-align: middle;
    }

    .sales-order-table-wrap .dropdown {
        position: relative;
    }

    .sales-order-table-wrap .dropdown-menu {
        position: absolute !important;
        right: 0 !important;
        left: auto !important;
        top: 100% !important;
        transform: translateY(6px) !important;
        z-index: 2000 !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.so-row').forEach(function (row) {
        row.addEventListener('click', function (e) {
            const blocked = e.target.closest('a,button,form,input,select,textarea,label,.dropdown-menu,.dropdown-toggle');
            if (blocked) return;

            const href = row.getAttribute('data-href');
            if (href) window.location.href = href;
        });
    });
});
</script>
@endsection
