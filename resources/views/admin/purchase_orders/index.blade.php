@extends('layouts.admin')

@section('title', 'Danh sách đơn hàng mua')

@section('content')
<style>
    .po-page {
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 16px;
        padding: 16px;
    }

    .po-hero {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
        padding: 16px;
        margin-bottom: 12px;
    }

    .po-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }

    .po-title {
        margin: 0;
        font-size: 1.65rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: .2px;
    }

    .po-sub {
        margin: 4px 0 0;
        color: #64748b;
        font-size: .93rem;
    }

    .po-create-btn {
        border-radius: 12px;
        min-height: 44px;
        padding: 0 16px;
        font-weight: 700;
        border: none;
        background: linear-gradient(135deg, #16a34a 0%, #059669 100%);
        box-shadow: 0 10px 22px rgba(5, 150, 105, 0.28);
    }

    .po-kpis {
        display: grid;
        grid-template-columns: 1fr 1fr 2fr;
        gap: 12px;
        align-items: stretch;
    }

    .po-kpi-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px;
        height: 100%;
    }

    .po-kpi-label {
        font-size: .82rem;
        color: #64748b;
        margin-bottom: 4px;
        font-weight: 700;
    }

    .po-kpi-value {
        font-size: 1.55rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }

    .po-kpi-value--money {
        color: #0284c7;
        font-size: 1.35rem;
    }

    .po-chart-box {
        height: 112px;
        display: flex;
        flex-direction: column;
    }

    .po-chart-box canvas {
        width: 100% !important;
        height: 72px !important;
        max-height: 72px;
    }

    .po-chart-title {
        margin: 0 0 6px;
        color: #334155;
        font-weight: 700;
        font-size: .85rem;
    }

    .po-filter-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px;
        margin-bottom: 12px;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
    }

    .po-filter-card .form-label {
        font-size: .78rem;
        color: #64748b;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .po-filter-card .form-control,
    .po-filter-card .form-select {
        border-radius: 10px;
        min-height: 42px;
        border-color: #cbd5e1;
    }

    .po-table-wrap {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
    }

    .po-table { margin-bottom: 0; }

    .po-table thead th {
        background: #f8fafc;
        color: #0f172a;
        font-weight: 700;
        font-size: .86rem;
        white-space: nowrap;
        border-bottom: 1px solid #e2e8f0;
    }

    .po-table tbody td {
        border-color: #edf2f7;
        vertical-align: middle;
    }

    .po-table tbody tr.po-clickable-row {
        cursor: pointer;
        transition: background-color .15s ease;
    }

    .po-table tbody tr.po-clickable-row:hover {
        background: #eaf2ff;
    }

    .po-table tbody tr.po-clickable-row:hover td {
        background: #eaf2ff;
    }

    .po-row-code { font-weight: 700; color: #0f172a; }
    .po-supplier { font-weight: 600; color: #1e293b; }
    .po-sub-code { color: #94a3b8; font-size: .8rem; }

    .po-pill {
        border-radius: 999px;
        padding: 4px 10px;
        font-size: .75rem;
        font-weight: 700;
    }

    .po-actions .btn {
        border-radius: 8px;
        min-width: 52px;
    }

    .po-pagination {
        padding: 14px 18px;
        border-top: 1px solid #e2e8f0;
        background: #fff;
    }

    @media (max-width: 992px) {
        .po-kpis { grid-template-columns: 1fr; }
        .po-chart-box { height: 120px; }
    }
</style>

<div class="po-page">
    <div class="po-hero">
        <div class="po-header">
            <div>
                <h1 class="po-title">Danh sách đơn hàng mua</h1>
                <p class="po-sub">Theo dõi nhanh đơn hàng, chi phí và hiệu suất 7 ngày gần đây.</p>
            </div>
            <a href="{{ route('admin.purchase-orders.create') }}" class="btn btn-success po-create-btn">
                <i class="bi bi-plus-circle me-1"></i>Tạo đề nghị mới
            </a>
        </div>

            <div class="po-kpis">
            <div class="po-kpi-card">
                <div class="po-kpi-label">Tổng số đơn</div>
                <div class="po-kpi-value">{{ number_format((int) ($totalOrders ?? 0), 0, ',', '.') }}</div>
            </div>
            <div class="po-kpi-card">
                <div class="po-kpi-label">Tổng tiền</div>
                <div class="po-kpi-value po-kpi-value--money">{{ number_format((float) ($totalAmount ?? 0), 0, ',', '.') }}</div>
            </div>
            <div class="po-kpi-card po-chart-box">
                <p class="po-chart-title">Biểu đồ tổng tiền 7 ngày</p>
                <canvas id="poMiniChart" height="78"></canvas>
            </div>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.purchase-orders.index') }}" class="po-filter-card">
        <div class="row g-2 align-items-end">
            <div class="col-lg-3 col-md-6">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="order" {{ $status === 'order' ? 'selected' : '' }}>Đặt hàng</option>
                    <option value="return" {{ $status === 'return' ? 'selected' : '' }}>Trả hàng</option>
                </select>
            </div>
            <div class="col-lg-5 col-md-6">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Mã đơn / nhà cung cấp / MST...">
            </div>
            <div class="col-lg-4 col-md-12 d-flex gap-2 flex-wrap">
                <button class="btn btn-primary" type="submit"><i class="bi bi-funnel me-1"></i>Lọc</button>
                @if($status !== '' || $q !== '')
                    <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i>Xóa lọc</a>
                @endif
            </div>
        </div>
    </form>

    <div class="po-table-wrap">
        <div style="overflow-x:auto;">
            <table class="table po-table align-middle">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Trạng thái</th>
                        <th>Nhà cung cấp</th>
                        <th>Ngày giao</th>
                        <th>Số dòng</th>
                        <th class="text-end">Tổng tiền</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $o)
                        @php
                            $total = $o->items->sum(function ($it) {
                                $amount = (float) ($it->amount ?? 0);
                                $tax = (float) ($it->tax_percent ?? 0);
                                return $amount + ($amount * $tax / 100);
                            });
                        @endphp
                        <tr class="po-clickable-row" data-href="{{ route('admin.purchase-orders.show', $o) }}">
                            <td class="po-row-code">{{ $o->code }}</td>
                            <td>
                                <span class="po-pill {{ $o->order_type === 'return' ? 'bg-warning text-dark' : 'bg-primary text-white' }}">
                                    {{ $o->order_type === 'return' ? 'Trả hàng' : 'Đặt hàng' }}
                                </span>
                            </td>
                            <td>
                                <div class="po-supplier">{{ $o->supplier_name }}</div>
                                <div class="po-sub-code">{{ $o->supplier_code ?: '—' }}</div>
                            </td>
                            <td>{{ optional($o->delivery_date)->format('d/m/Y') ?: '—' }}</td>
                            <td>{{ $o->items->count() }}</td>
                            <td class="text-end fw-semibold">{{ number_format((float) $total, 0, ',', '.') }}</td>
                            <td class="text-center po-actions">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.purchase-orders.show', $o) }}">Xem</a>
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.purchase-orders.edit', $o) }}">Sửa</a>
                                <form action="{{ route('admin.purchase-orders.destroy', $o) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa đơn này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">Chưa có đơn mua hàng.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="po-pagination">{{ $orders->links('pagination::bootstrap-5') }}</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function () {
    const ctx = document.getElementById('poMiniChart');
    if (!ctx) return;

    const labels = @json($chartLabels ?? []);
    const values = @json($chartValues ?? []);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                data: values,
                borderColor: '#0ea5e9',
                backgroundColor: 'rgba(14, 165, 233, 0.12)',
                fill: true,
                tension: 0.35,
                borderWidth: 2,
                pointRadius: 2,
                pointHoverRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => new Intl.NumberFormat('vi-VN').format(ctx.parsed.y || 0)
                    }
                }
            },
            scales: {
                x: {
                    ticks: { color: '#64748b', font: { size: 10 } },
                    grid: { display: false }
                },
                y: {
                    ticks: {
                        color: '#64748b',
                        font: { size: 10 },
                        callback: (value) => new Intl.NumberFormat('vi-VN', { notation: 'compact' }).format(value)
                    },
                    grid: { color: 'rgba(148,163,184,.2)' }
                }
            }
        }
    });
})();

(function () {
    const rows = document.querySelectorAll('.po-clickable-row');
    rows.forEach((row) => {
        row.addEventListener('click', function (e) {
            const blocked = e.target.closest('a, button, form, input, select, textarea, label');
            if (blocked) return;

            const href = row.getAttribute('data-href');
            if (href) window.location.href = href;
        });
    });
})();
</script>
@endsection