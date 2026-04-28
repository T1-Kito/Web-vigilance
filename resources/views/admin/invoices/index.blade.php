@extends('layouts.admin')

@section('title', 'Hóa đơn bán hàng')

@section('content')
<style>
    .invoice-row {
        cursor: pointer;
        transition: background-color .15s ease;
    }

    .invoice-row:hover td {
        background: #eaf3ff !important;
    }

    .invoice-row td {
        vertical-align: middle;
        transition: background-color .15s ease;
    }

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
    .state-chip--gray {
        color: #334155;
        background: linear-gradient(135deg, rgba(148,163,184,.2), rgba(203,213,225,.26));
        border-color: rgba(148,163,184,.4);
    }
</style>

<div class="container-fluid py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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
                        <tr class="invoice-row" data-href="{{ route('admin.invoices.open-misa', $invoice) }}">
                            <td class="fw-semibold">
                                {{ $invoice->invoice_code }}
                                @if($invoice->misa_transaction_id)
                                    <div class="small text-muted">Tra cứu: {{ $invoice->misa_transaction_id }}</div>
                                @endif
                            </td>
                            <td>{{ $invoice->salesOrder->sales_order_code ?? ($invoice->order->order_code ?? ('#' . ($invoice->sales_order_id ?? $invoice->order_id))) }}</td>
                            <td>{{ optional($invoice->issued_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                @php
                                    $invoiceStatusMeta = [
                                        'issued' => ['label' => 'Đã phát hành', 'class' => 'state-chip state-chip--green', 'icon' => 'bi-check2-circle'],
                                        'cancelled' => ['label' => 'Đã hủy', 'class' => 'state-chip state-chip--red', 'icon' => 'bi-x-circle'],
                                        'draft' => ['label' => 'Nháp', 'class' => 'state-chip state-chip--gray', 'icon' => 'bi-journal-text'],
                                        'pending' => ['label' => 'Chờ xử lý', 'class' => 'state-chip state-chip--amber', 'icon' => 'bi-hourglass-split'],
                                    ];
                                    $is = $invoiceStatusMeta[$invoice->status] ?? ['label' => ucfirst((string) $invoice->status), 'class' => 'state-chip state-chip--gray', 'icon' => 'bi-dot'];
                                @endphp
                                <span class="{{ $is['class'] }}"><i class="bi {{ $is['icon'] }}"></i>{{ $is['label'] }}</span>
                            </td>
                            <td class="fw-bold text-danger">{{ number_format((float) $invoice->total_amount, 0, ',', '.') }}đ</td>
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
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 small">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.invoices.open-misa', $invoice) }}" target="_blank" rel="noopener">
                                                <i class="bi bi-box-arrow-up-right me-2 text-success"></i>Xem hóa đơn điện tử
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.invoices.show', $invoice) }}">
                                                <i class="bi bi-eye me-2 text-primary"></i>Chi tiết nội bộ
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('admin.invoices.destroy', $invoice) }}" onsubmit="return confirm('Xóa hóa đơn này?');">
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.invoice-row').forEach(function (row) {
        row.addEventListener('click', function (e) {
            if (e.target.closest('.dropdown') || e.target.closest('a, button, form, input, select, textarea, label')) {
                return;
            }
            const href = row.getAttribute('data-href');
            if (href) {
                window.location.href = href;
            }
        });
    });
});
</script>
@endsection
