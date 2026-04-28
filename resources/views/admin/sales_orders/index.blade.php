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
                                    $statusMeta = [
                                        'pending' => ['label' => 'Chờ xử lý', 'class' => 'state-chip state-chip--amber', 'icon' => 'bi-hourglass-split'],
                                        'processing' => ['label' => 'Đang xử lý', 'class' => 'state-chip state-chip--blue', 'icon' => 'bi-gear'],
                                        'completed' => ['label' => 'Hoàn thành', 'class' => 'state-chip state-chip--green', 'icon' => 'bi-check2-circle'],
                                        'cancelled' => ['label' => 'Đã hủy', 'class' => 'state-chip state-chip--red', 'icon' => 'bi-x-circle'],
                                    ];
                                    $s = $statusMeta[$so->status] ?? ['label' => ucfirst((string) $so->status), 'class' => 'state-chip state-chip--gray', 'icon' => 'bi-dot'];
                                @endphp
                                <span class="{{ $s['class'] }}"><i class="bi {{ $s['icon'] }}"></i>{{ $s['label'] }}</span>
                            </td>
                            <td>
                                @php
                                    $paymentStatus = (string) optional($so->debt)->status ?: (string) ($so->payment_status ?? 'unpaid');
                                    $paymentMeta = [
                                        'unpaid' => ['label' => 'Chưa thanh toán', 'class' => 'state-chip state-chip--slate', 'icon' => 'bi-wallet2'],
                                        'partial' => ['label' => 'Thanh toán một phần', 'class' => 'state-chip state-chip--amber', 'icon' => 'bi-pie-chart'],
                                        'paid' => ['label' => 'Đã thanh toán', 'class' => 'state-chip state-chip--green', 'icon' => 'bi-check-circle'],
                                        'overdue' => ['label' => 'Quá hạn', 'class' => 'state-chip state-chip--red', 'icon' => 'bi-exclamation-triangle'],
                                    ];
                                    $p = $paymentMeta[$paymentStatus] ?? ['label' => $paymentStatus, 'class' => 'state-chip state-chip--gray', 'icon' => 'bi-dot'];
                                @endphp
                                <span class="{{ $p['class'] }}"><i class="bi {{ $p['icon'] }}"></i>{{ $p['label'] }}</span>
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

    .state-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 700;
        line-height: 1;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .state-chip i { font-size: .78rem; line-height: 1; }

    .state-chip--green {
        color: #065f46;
        background: linear-gradient(135deg, rgba(16,185,129,.18), rgba(52,211,153,.22));
        border-color: rgba(16,185,129,.35);
    }
    .state-chip--blue {
        color: #1e40af;
        background: linear-gradient(135deg, rgba(59,130,246,.15), rgba(99,102,241,.2));
        border-color: rgba(59,130,246,.35);
    }
    .state-chip--amber {
        color: #92400e;
        background: linear-gradient(135deg, rgba(245,158,11,.2), rgba(251,191,36,.22));
        border-color: rgba(245,158,11,.38);
    }
    .state-chip--red {
        color: #991b1b;
        background: linear-gradient(135deg, rgba(239,68,68,.16), rgba(248,113,113,.2));
        border-color: rgba(239,68,68,.35);
    }
    .state-chip--slate,
    .state-chip--gray {
        color: #334155;
        background: linear-gradient(135deg, rgba(148,163,184,.2), rgba(203,213,225,.26));
        border-color: rgba(148,163,184,.4);
    }

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
