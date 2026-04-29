@extends('layouts.admin')

@section('title', 'Chi tiết đơn hàng')

@section('content')
@php
    $subTotal = (float) $salesOrder->items->sum(fn($i) => (float) ($i->unit_price ?? 0) * (int) ($i->quantity ?? 0));
    $lineVatTotal = (float) $salesOrder->items->sum(function ($i) {
        $line = (float) ($i->unit_price ?? 0) * (int) ($i->quantity ?? 0);
        $vatRate = (float) ($i->vat_percent ?? $salesOrder->vat_percent ?? 0);
        return $line * max(0, $vatRate) / 100;
    });
    $total = $subTotal + $lineVatTotal;
    $completionRate = $totalOrdered > 0 ? min(100, round(($totalDelivered / $totalOrdered) * 100)) : 0;
@endphp

<style>
    .so-page {
        --bg-soft: #f6f8fc;
        --bg-card: #ffffff;
        --txt-main: #0f172a;
        --txt-muted: #64748b;
        --brand: #2563eb;
        --ok: #16a34a;
        --warn: #d97706;
        --danger: #dc2626;
    }

    .so-page .so-surface {
        background: var(--bg-soft);
        border-radius: 20px;
        padding: 16px;
    }

    .so-page .so-action-bar {
        position: sticky;
        top: 8px;
        z-index: 10;
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(6px);
        border-radius: 16px;
        padding: 14px 16px;
        box-shadow: 0 6px 20px rgba(15, 23, 42, 0.08);
        margin-bottom: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .so-page .so-title-wrap h1 {
        font-size: 1.2rem;
        font-weight: 700;
        margin: 0;
        color: var(--txt-main);
    }

    .so-page .so-subtitle {
        margin-top: 4px;
        color: var(--txt-muted);
        font-size: 0.9rem;
    }

    .so-page .so-main-card,
    .so-page .so-side-card {
        background: var(--bg-card);
        border-radius: 16px;
        padding: 18px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
    }

    .so-page .so-main-card + .so-main-card {
        margin-top: 14px;
    }

    .so-page .section-title {
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 12px;
        color: var(--txt-main);
    }

    .so-page .kv {
        display: grid;
        grid-template-columns: 150px 1fr;
        gap: 8px;
        margin-bottom: 8px;
        font-size: 0.93rem;
    }

    .so-page .kv .k { color: var(--txt-muted); }
    .so-page .kv .v { color: var(--txt-main); font-weight: 600; }

    .so-page .soft-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        overflow: hidden;
        border-radius: 12px;
    }

    .so-page .soft-table thead th {
        background: #f8fafc;
        color: #334155;
        font-weight: 700;
        font-size: 0.85rem;
        padding: 10px 12px;
    }

    .so-page .soft-table tbody td {
        padding: 11px 12px;
        font-size: 0.92rem;
        border-top: 1px solid #eef2f7;
        color: #0f172a;
        vertical-align: middle;
    }

    .so-page .soft-table th,
    .so-page .soft-table td {
        white-space: nowrap;
    }

    .so-page .soft-table .col-product {
        width: 30%;
        min-width: 260px;
        white-space: normal;
    }

    .so-page .product-name {
        font-weight: 600;
        line-height: 1.35;
        word-break: break-word;
    }

    .so-page .col-center {
        text-align: center;
    }

    .so-page .col-money {
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    .so-page .so-metric-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 14px;
    }

    .so-page .metric-item {
        background: #fff;
        border-radius: 14px;
        padding: 12px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
    }

    .so-page .metric-item .label {
        color: var(--txt-muted);
        font-size: 0.8rem;
    }

    .so-page .metric-item .value {
        margin-top: 6px;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--txt-main);
    }

    .so-page .side-kv {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        padding: 8px 0;
        font-size: 0.9rem;
    }

    .so-page .side-kv .k { color: var(--txt-muted); }
    .so-page .side-kv .v { font-weight: 700; color: var(--txt-main); text-align: right; }

    .so-page .total-line {
        border-top: 1px solid #e5e7eb;
        margin-top: 8px;
        padding-top: 10px;
    }

    @media (max-width: 992px) {
        .so-page .so-metric-grid { grid-template-columns: 1fr; }
        .so-page .kv { grid-template-columns: 1fr; gap: 2px; }
    }
</style>

<div class="container-fluid py-3 so-page">
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="so-surface">
        <div class="so-action-bar">
            <div class="so-title-wrap">
                <h1>Số đơn hàng: {{ $salesOrder->sales_order_code }}</h1>
                <div class="so-subtitle">Nguồn báo giá: {{ $salesOrder->quote->quote_code ?? '---' }}</div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.sales-orders.pdf', $salesOrder) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                </a>
                <a href="{{ route('admin.sales-orders.deliveries.create', $salesOrder) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-truck me-1"></i>Tạo xuất kho
                </a>
                <form id="issueMisaForm" method="POST" action="{{ route('admin.sales-orders.invoices.misa.publish', $salesOrder) }}" class="d-inline">
                    @csrf
                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#issueMisaConfirmModal"
                    >
                        <i class="bi bi-receipt me-1"></i>Phát hành hóa đơn
                    </button>
                </form>
                <a href="{{ route('admin.sales-orders.index') }}" class="btn btn-light border btn-sm">Quay lại</a>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="so-metric-grid">
                    <div class="metric-item">
                        <div class="label">Giá trị đơn hàng</div>
                        <div class="value text-danger">{{ number_format($total, 0, ',', '.') }}đ</div>
                    </div>
                    <div class="metric-item">
                        <div class="label">Tiến độ giao</div>
                        <div class="value">{{ $totalDelivered }}/{{ $totalOrdered }} ({{ $completionRate }}%)</div>
                    </div>
                    <div class="metric-item">
                        <div class="label">Số chứng từ</div>
                        <div class="value">PX: {{ $deliveries->count() }} | HĐ: {{ $invoices->count() }}</div>
                    </div>
                </div>

                <div class="so-main-card">
                    <div class="section-title">Thông tin khách hàng & giao nhận</div>
                    <div class="kv"><div class="k">Khách hàng xuất hóa đơn</div><div class="v">{{ $salesOrder->invoice_company_name ?: '---' }}</div></div>
                    <div class="kv"><div class="k">Mã số thuế</div><div class="v">{{ $salesOrder->customer_tax_code ?: '---' }}</div></div>
                    <div class="kv"><div class="k">Người liên hệ</div><div class="v">{{ $salesOrder->customer_contact_person ?: '---' }}</div></div>
                    <div class="kv"><div class="k">SĐT / Email</div><div class="v">{{ $salesOrder->customer_phone ?: '---' }} @if($salesOrder->customer_email) / {{ $salesOrder->customer_email }} @endif</div></div>
                    <div class="kv"><div class="k">Địa chỉ hóa đơn</div><div class="v">{{ $salesOrder->invoice_address ?: '---' }}</div></div>
                    <div class="kv"><div class="k">Người nhận hàng</div><div class="v">{{ $salesOrder->receiver_name ?: '---' }} ({{ $salesOrder->receiver_phone ?: '---' }})</div></div>
                    <div class="kv"><div class="k">Địa chỉ giao hàng</div><div class="v">{{ $salesOrder->receiver_address ?: '---' }}</div></div>
                </div>

                <div class="so-main-card">
                    <div class="section-title">Chi tiết sản phẩm</div>
                    <div class="table-responsive">
                        <table class="soft-table">
                            <thead>
                                <tr>
                                    <th class="col-product">Tên sản phẩm</th>
                                    <th class="col-center" style="width:100px;">Đơn vị</th>
                                    <th class="col-center" style="width:70px;">SL</th>
                                    <th class="col-money" style="width:140px;">Đơn giá</th>
                                    <th class="col-center" style="width:90px;">Thuế suất</th>
                                    <th class="col-money" style="width:130px;">Tiền thuế</th>
                                    <th class="col-money" style="width:160px;">Tiền hàng</th>
                                    <th class="col-money" style="width:170px;">Sau thuế</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($salesOrder->items as $item)
                                @php
                                    $lineAmount = (float) $item->unit_price * (int) $item->quantity;
                                    $lineVatRate = (float) ($item->vat_percent ?? $salesOrder->vat_percent ?? 0);
                                    $lineVatAmount = $lineAmount * max(0, $lineVatRate) / 100;
                                @endphp
                                <tr>
                                    <td class="col-product"><div class="product-name">{{ $item->product->name ?? ('SP #' . $item->product_id) }}</div></td>
                                    <td class="col-center">{{ $item->unit ?: '---' }}</td>
                                    <td class="col-center">{{ (int) $item->quantity }}</td>
                                    <td class="col-money">{{ number_format((float) $item->unit_price, 0, ',', '.') }}đ</td>
                                    <td class="col-center">{{ $lineVatRate == 0 ? 'KCT/0%' : (rtrim(rtrim(number_format($lineVatRate, 2, '.', ''), '0'), '.') . '%') }}</td>
                                    <td class="col-money">{{ number_format($lineVatAmount, 0, ',', '.') }}đ</td>
                                    <td class="col-money"><strong>{{ number_format($lineAmount, 0, ',', '.') }}đ</strong></td>
                                    <td class="col-money"><strong class="text-danger">{{ number_format($lineAmount + $lineVatAmount, 0, ',', '.') }}đ</strong></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="so-main-card">
                    <div class="section-title d-flex justify-content-between align-items-center">
                        <span>Lịch sử phiếu xuất kho</span>
                        <a href="{{ route('admin.sales-orders.deliveries.create', $salesOrder) }}" class="btn btn-sm btn-outline-primary">Tạo phiếu xuất kho</a>
                    </div>
                    <div class="table-responsive">
                        <table class="soft-table">
                            <thead>
                                <tr>
                                    <th>Mã phiếu</th>
                                    <th>Ngày xuất</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Xem</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($deliveries as $d)
                                <tr>
                                    <td>{{ $d->delivery_code }}</td>
                                    <td>{{ optional($d->delivered_at)->format('d/m/Y H:i') }}</td>
                                    <td><span class="badge bg-{{ $d->status === 'confirmed' ? 'success' : 'secondary' }}">{{ $d->status }}</span></td>
                                    <td class="text-end"><a href="{{ route('admin.deliveries.show', $d) }}" class="btn btn-sm btn-outline-secondary">Chi tiết</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Chưa có phiếu xuất kho.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="so-main-card">
                    <div class="section-title">Lịch sử hóa đơn</div>
                    <div class="table-responsive">
                        <table class="soft-table">
                            <thead>
                                <tr>
                                    <th>Mã hóa đơn</th>
                                    <th>Ngày phát hành</th>
                                    <th>Tổng tiền</th>
                                    <th>Trạng thái</th>
                                    <th class="text-end">Xem</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($invoices as $inv)
                                <tr>
                                    <td>{{ $inv->invoice_code }}</td>
                                    <td>{{ optional($inv->issued_at)->format('d/m/Y H:i') }}</td>
                                    <td>{{ number_format((float) $inv->total_amount, 0, ',', '.') }}đ</td>
                                    <td><span class="badge bg-{{ $inv->status === 'issued' ? 'success' : 'secondary' }}">{{ $inv->status }}</span></td>
                                    <td class="text-end"><a href="{{ route('admin.invoices.show', $inv) }}" class="btn btn-sm btn-outline-secondary">Chi tiết</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">Chưa có hóa đơn.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="so-side-card" style="position: sticky; top: 84px;">
                    <div class="section-title">Thông tin xử lý đơn hàng</div>
                    <div class="side-kv"><div class="k">Số đơn hàng</div><div class="v">{{ $salesOrder->sales_order_code }}</div></div>
                    <div class="side-kv"><div class="k">Ngày tạo</div><div class="v">{{ optional($salesOrder->created_at)->format('d/m/Y H:i') }}</div></div>
                    <div class="side-kv"><div class="k">Hạn giao hàng</div><div class="v">{{ optional($salesOrder->delivery_due_date)->format('d/m/Y') ?: '---' }}</div></div>
                    <div class="side-kv"><div class="k">Hạn thanh toán</div><div class="v">{{ optional($salesOrder->payment_due_date)->format('d/m/Y') ?: '---' }}</div></div>
                    <div class="side-kv"><div class="k">Trạng thái đơn</div><div class="v"><span class="badge bg-secondary">{{ $salesOrder->status }}</span></div></div>
                    <div class="side-kv"><div class="k">Hình thức TT</div><div class="v">
                        @if(($salesOrder->payment_term ?? 'full_advance') === 'debt')
                            Công nợ {{ (int) ($salesOrder->payment_due_days ?? 0) }} ngày
                        @elseif(($salesOrder->payment_term ?? 'full_advance') === 'deposit')
                            Đặt cọc {{ (float) ($salesOrder->deposit_percent ?? 0) }}%
                        @else
                            Thanh toán 100%
                        @endif
                    </div></div>

                    @php
                        $payMap = ['unpaid' => 'Chưa thanh toán', 'partial' => 'Thanh toán một phần', 'paid' => 'Đã thanh toán', 'overdue' => 'Quá hạn'];
                        $payLabel = $payMap[$salesOrder->payment_status ?? 'unpaid'] ?? ($salesOrder->payment_status ?? '---');
                        $payBadge = ($salesOrder->payment_status ?? 'unpaid') === 'paid' ? 'success' : ((($salesOrder->payment_status ?? 'unpaid') === 'partial') ? 'warning' : ((($salesOrder->payment_status ?? 'unpaid') === 'overdue') ? 'danger' : 'secondary'));
                    @endphp
                    <div class="side-kv"><div class="k">Trạng thái công nợ</div><div class="v"><span class="badge bg-{{ $payBadge }}">{{ $payLabel }}</span></div></div>
                    <div class="side-kv"><div class="k">Đã thanh toán</div><div class="v text-success">{{ number_format((float) ($paidAmount ?? 0), 0, ',', '.') }}đ</div></div>
                    <div class="side-kv"><div class="k">Còn phải thu</div><div class="v text-danger">{{ number_format((float) ($remainingDebt ?? 0), 0, ',', '.') }}đ</div></div>

                    <div class="total-line">
                        <div class="side-kv"><div class="k"><strong>Tổng tiền</strong></div><div class="v text-danger">{{ number_format($total, 0, ',', '.') }}đ</div></div>
                    </div>

                    <div class="mt-3 d-grid gap-2">
                        @if($salesOrder->quote)
                            <a href="{{ route('admin.quotes.show', $salesOrder->quote) }}" class="btn btn-outline-secondary btn-sm">Xem báo giá admin</a>
                        @endif
                        <a href="{{ route('admin.sales-orders.deliveries.create', $salesOrder) }}" class="btn btn-outline-primary btn-sm">Tạo phiếu xuất kho</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="issueMisaConfirmModal" tabindex="-1" aria-labelledby="issueMisaConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="issueMisaConfirmModalLabel">Xác nhận phát hành hóa đơn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3">
                    Vui lòng kiểm tra kỹ toàn bộ thông tin trước khi phát hành. Dữ liệu hóa đơn liên quan trực tiếp đến nghĩa vụ pháp lý và thuế.
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="small text-muted">Ký hiệu hóa đơn</div>
                        <div class="fw-semibold">{{ $salesOrder->misa_inv_series ?? (config('services.meinvoice.inv_series') ?: 'Theo cấu hình MISA') }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Ngày hóa đơn</div>
                        <div class="fw-semibold">{{ now()->format('d/m/Y') }}</div>
                    </div>
                    <div class="col-md-8">
                        <div class="small text-muted">Khách hàng xuất hóa đơn</div>
                        <div class="fw-semibold">{{ $salesOrder->invoice_company_name ?: ($salesOrder->receiver_name ?: '---') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Mã số thuế</div>
                        <div class="fw-semibold">{{ $salesOrder->customer_tax_code ?: '---' }}</div>
                    </div>
                    <div class="col-12">
                        <div class="row g-3 align-items-stretch">
                            <div class="col-lg-8 col-md-7">
                                <div class="h-100 d-flex flex-column justify-content-center">
                                    <div class="mb-3">
                                        <label class="form-label small text-muted mb-1" for="misaReceiverNameOrder">Tên người nhận</label>
                                        <input
                                            id="misaReceiverNameOrder"
                                            name="receiver_name"
                                            form="issueMisaForm"
                                            type="text"
                                            class="form-control"
                                            value="{{ old('receiver_name', $salesOrder->customer_contact_person ?: ($salesOrder->receiver_name ?: '')) }}"
                                            placeholder="Nhập tên người nhận"
                                        >
                                    </div>
                                    <div>
                                        <label class="form-label small text-muted mb-1" for="misaReceiverEmailOrder">Email nhận hóa đơn</label>
                                        <input
                                            id="misaReceiverEmailOrder"
                                            name="receiver_email"
                                            form="issueMisaForm"
                                            type="email"
                                            class="form-control"
                                            value="{{ old('receiver_email', $salesOrder->customer_email ?? '') }}"
                                            placeholder="email@domain.com"
                                        >
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-5">
                                <div class="h-100 d-flex align-items-center justify-content-center bg-light rounded-3 p-2">
                                    <img src="{{ asset('hoadon.png') }}" alt="Minh họa gửi hóa đơn" style="max-width: 100%; max-height: 150px; object-fit: contain;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="small text-muted">Địa chỉ hóa đơn</div>
                        <div class="fw-semibold">{{ $salesOrder->invoice_address ?: ($salesOrder->receiver_address ?: '---') }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">Tổng tiền hàng</div>
                        <div class="fw-semibold text-danger">{{ number_format((float) $total, 0, ',', '.') }}đ</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small text-muted">VAT (%)</div>
                        <div class="fw-semibold">{{ number_format((float) ($salesOrder->vat_percent ?? 0), 0, ',', '.') }}%</div>
                    </div>
                </div>

            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary" id="confirmIssueMisaBtn">Xác nhận & Phát hành</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const issueBtn = document.getElementById('confirmIssueMisaBtn');
        const form = document.getElementById('issueMisaForm');

        if (!issueBtn || !form) return;

        issueBtn.addEventListener('click', function () {
            issueBtn.disabled = true;
            form.action = '{{ route('admin.sales-orders.invoices.misa.publish', $salesOrder) }}';
            form.submit();
        });
    })();
</script>
@endsection
