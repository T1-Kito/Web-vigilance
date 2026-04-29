@extends('layouts.admin')

@section('title', 'Chi tiết phiếu xuất kho')

@section('content')
<style>
    .delivery-items-table th,
    .delivery-items-table td {
        vertical-align: middle;
        font-size: .9rem;
    }
    .delivery-items-table thead th {
        white-space: nowrap;
        background: #f8fafc;
    }
    .delivery-items-table .col-product {
        min-width: 260px;
        max-width: 320px;
    }
    .delivery-items-table .product-name {
        font-weight: 600;
        line-height: 1.3;
    }
    .delivery-items-table .money,
    .delivery-items-table .qty,
    .delivery-items-table .tax {
        white-space: nowrap;
    }
    .delivery-items-table .money { text-align: right; }
    .delivery-items-table .qty,
    .delivery-items-table .tax { text-align: center; }
</style>
@php
    $order = $delivery->order;
    $salesOrder = $delivery->salesOrder;
    $sourceCode = $salesOrder->sales_order_code ?? ($order->order_code ?? ('#' . ($delivery->sales_order_id ?? $delivery->order_id ?? $delivery->id)));
    $quoteCode = $salesOrder?->quote?->quote_code;
    $items = $delivery->items ?? collect();
    $totalQty = (int) $items->sum('quantity');
    $discountPercent = (float) ($salesOrder->discount_percent ?? $order->discount_percent ?? 0);
    $totalAmount = (float) $items->sum(function ($line) use ($salesOrder, $discountPercent) {
        $soItem = $line->salesOrderItem;
        $orderItem = $line->orderItem;
        $unitPrice = (float) ($soItem->unit_price ?? ($orderItem->unit_price ?? 0));
        $lineAmount = ((int) ($line->quantity ?? 0)) * $unitPrice;
        $lineAfterDiscount = $lineAmount * (1 - (max(0, min(100, $discountPercent)) / 100));
        $lineVatRate = (float) ($soItem->vat_percent ?? ($salesOrder->vat_percent ?? 0));
        $lineVatAmount = $lineAfterDiscount * max(0, $lineVatRate) / 100;

        return $lineAfterDiscount + $lineVatAmount;
    });
@endphp

<style>
    .delivery-items-table {
        table-layout: fixed;
        width: 100%;
    }
    .delivery-items-table th,
    .delivery-items-table td {
        font-size: .86rem;
        vertical-align: middle;
        padding-top: .55rem;
        padding-bottom: .55rem;
    }
    .delivery-items-table .col-product {
        width: 28%;
    }
    .delivery-items-table .product-name {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.28;
        max-height: 2.56em;
    }
    .delivery-items-table .money {
        text-align: right;
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }
    .delivery-items-table .qty,
    .delivery-items-table .tax {
        text-align: center;
        white-space: nowrap;
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

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Chi tiết phiếu xuất kho</h1>
            <div class="text-muted">Mã phiếu: <span class="fw-semibold">{{ $delivery->delivery_code }}</span></div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.deliveries.print', $delivery) }}" target="_blank" rel="noopener" class="btn btn-primary">
                <i class="bi bi-printer me-1"></i>In phiếu xuất
            </a>
            @if($salesOrder)
                @php
                    $hasIssuedInvoice = (bool) $salesOrder->invoices()->where('status', 'issued')->exists();
                    $latestIssuedInvoice = $hasIssuedInvoice
                        ? $salesOrder->invoices()->where('status', 'issued')->latest('id')->first()
                        : null;
                @endphp

                @if($hasIssuedInvoice)
                    <a href="{{ route('admin.invoices.show', $latestIssuedInvoice) }}" class="btn btn-outline-success">
                        <i class="bi bi-check2-circle me-1"></i>Đã xuất hóa đơn
                    </a>
                @else
                    <form id="issueMisaFromDeliveryForm" method="POST" action="{{ route('admin.sales-orders.invoices.issue-misa', $salesOrder) }}" class="d-inline">
                        @csrf
                        <button
                            type="button"
                            class="btn btn-success"
                            @disabled($delivery->status !== 'confirmed')
                            data-bs-toggle="modal"
                            data-bs-target="#issueMisaDeliveryConfirmModal"
                        >
                            <i class="bi bi-receipt me-1"></i>Phát hành hóa đơn MISA
                        </button>
                    </form>
                @endif

                <a href="{{ route('admin.sales-orders.show', $salesOrder) }}" class="btn btn-outline-primary">Về đơn bán ngoài</a>
            @elseif($order)
                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-primary">Về đơn hàng</a>
            @endif
            <a href="{{ route('admin.deliveries.index') }}" class="btn btn-outline-secondary">Danh sách phiếu xuất</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Thông tin giao nhận</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Nguồn đơn</div>
                            <div class="fw-semibold">{{ $sourceCode }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Báo giá nguồn</div>
                            <div class="fw-semibold">{{ $quoteCode ?: '---' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Họ và tên người nhận hàng</div>
                            <div class="fw-semibold">{{ $delivery->receiver_name ?: ($salesOrder->invoice_company_name ?? $salesOrder->receiver_name ?? $order->receiver_name ?? '---') }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Địa điểm giao hàng</div>
                            <div class="fw-semibold">{{ $delivery->delivery_location ?: ($salesOrder->receiver_address ?? $order->receiver_address ?? '---') }}</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">Địa chỉ</div>
                            <div class="fw-semibold">{{ $delivery->receiver_address ?: ($salesOrder->receiver_address ?? $order->receiver_address ?? '---') }}</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted small">Lý do xuất</div>
                            <div class="fw-semibold">{{ $delivery->delivery_reason ?: '---' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Chi tiết hàng đã xuất</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle delivery-items-table">
                            <thead>
                                <tr>
                                    <th class="ps-3 col-product">Sản phẩm</th>
                                    <th class="qty" style="width:72px;">Đơn vị</th>
                                    <th class="qty" style="width:64px;">SL đặt</th>
                                    <th class="qty" style="width:64px;">SL xuất</th>
                                    <th class="money" style="width:118px;">Đơn giá</th>
                                    <th class="tax" style="width:74px;">Thuế suất</th>
                                    <th class="money" style="width:106px;">Tiền thuế</th>
                                    <th class="money" style="width:118px;">Tiền hàng</th>
                                    <th class="money" style="width:124px;">Sau thuế</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($items as $line)
                                @php
                                    $soItem = $line->salesOrderItem;
                                    $orderItem = $line->orderItem;
                                    $unit = $soItem->unit ?? ($orderItem->unit ?? '---');
                                    $orderedQty = (int) ($soItem->quantity ?? ($orderItem->quantity ?? 0));
                                    $unitPrice = (float) ($soItem->unit_price ?? ($orderItem->unit_price ?? 0));
                                    $lineTotal = $unitPrice * (int) ($line->quantity ?? 0);
                                    $lineVatRate = (float) ($soItem->vat_percent ?? ($salesOrder->vat_percent ?? 0));
                                    $lineVatAmount = $lineTotal * max(0, $lineVatRate) / 100;
                                    $lineAfterTax = $lineTotal + $lineVatAmount;
                                @endphp
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold">{{ $line->product->name ?? ('Sản phẩm #' . $line->product_id) }}</div>
                                        <div class="small text-muted">Mã SP: {{ $line->product_id }}</div>
                                    </td>
                                    <td class="text-center">{{ $unit }}</td>
                                    <td class="text-center">{{ $orderedQty }}</td>
                                    <td class="text-center"><span class="fw-bold text-primary">{{ (int) $line->quantity }}</span></td>
                                    <td class="text-end">{{ number_format($unitPrice, 0, ',', '.') }}đ</td>
                                    <td class="text-center">{{ $lineVatRate == 0 ? 'KCT/0%' : (rtrim(rtrim(number_format($lineVatRate, 2, '.', ''), '0'), '.') . '%') }}</td>
                                    <td class="text-end">{{ number_format($lineVatAmount, 0, ',', '.') }}đ</td>
                                    <td class="text-end fw-semibold">{{ number_format($lineTotal, 0, ',', '.') }}đ</td>
                                    <td class="text-end fw-bold text-danger">{{ number_format($lineAfterTax, 0, ',', '.') }}đ</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">Không có dòng xuất kho.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="position: sticky; top: 16px;">
                <div class="card-header bg-white fw-bold">Thông tin chứng từ</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Mã phiếu</span>
                        <span class="fw-semibold">{{ $delivery->delivery_code }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Ngày xuất</span>
                        <span class="fw-semibold">{{ optional($delivery->delivered_at)->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Trạng thái</span>
                        <span class="badge bg-{{ $delivery->status === 'confirmed' ? 'success' : ($delivery->status === 'cancelled' ? 'danger' : 'secondary') }}">
                            {{ $delivery->status === 'confirmed' ? 'Đã xuất' : ($delivery->status === 'cancelled' ? 'Đã hủy' : 'Nháp') }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tổng SL xuất</span>
                        <span class="fw-bold text-danger">{{ $totalQty }}</span>
                    </div>

                    <hr>

                    <div class="mb-2">
                        <div class="text-muted small">Người giao hàng</div>
                        <div class="fw-semibold">{{ $delivery->shipper_name ?: '---' }}</div>
                    </div>
                    <div class="mb-2">
                        <div class="text-muted small">SĐT người giao</div>
                        <div class="fw-semibold">{{ $delivery->shipper_phone ?: '---' }}</div>
                    </div>
                    <div class="mb-2">
                        <div class="text-muted small">Lý do xuất</div>
                        <div class="fw-semibold">{{ $delivery->delivery_reason ?: '---' }}</div>
                    </div>
                    <div class="mb-0">
                        <div class="text-muted small">Ghi chú</div>
                        <div class="fw-semibold">{{ $delivery->note ?: '---' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($salesOrder && empty($hasIssuedInvoice))
<div class="modal fade" id="issueMisaDeliveryConfirmModal" tabindex="-1" aria-labelledby="issueMisaDeliveryConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="issueMisaDeliveryConfirmModalLabel">Xác nhận phát hành hóa đơn MISA</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="row g-3 align-items-start border-bottom pb-3 mb-3">
                    <div class="col-md-8">
                        <div class="small text-muted">Khách hàng</div>
                        <div class="fw-semibold">{{ $salesOrder->invoice_company_name ?: ($salesOrder->receiver_name ?: '---') }}</div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <div class="small text-muted">Tổng tiền thanh toán</div>
                        <div class="fw-bold text-dark" style="font-size: 1.65rem; line-height: 1.2;">{{ number_format($totalAmount, 0, ',', '.') }}đ</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Ký hiệu hóa đơn</div>
                        <div class="fw-semibold">{{ config('services.meinvoice.inv_series') ?: 'Theo cấu hình MISA' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Ngày hóa đơn</div>
                        <div class="fw-semibold">{{ now()->format('d/m/Y') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Mã số thuế</div>
                        <div class="fw-semibold">{{ $salesOrder->customer_tax_code ?: '---' }}</div>
                    </div>
                </div>

                <div class="row g-2">
                    <div class="col-12">
                        <div class="row g-3 align-items-stretch">
                            <div class="col-lg-8 col-md-7">
                                <div class="h-100 d-flex flex-column justify-content-center">
                                    <div class="mb-3">
                                        <label class="form-label small text-muted mb-1" for="misaReceiverName">Tên người nhận</label>
                                        <input
                                            id="misaReceiverName"
                                            name="receiver_name"
                                            form="issueMisaFromDeliveryForm"
                                            type="text"
                                            class="form-control"
                                            value="{{ old('receiver_name', $salesOrder->customer_contact_person ?: ($salesOrder->receiver_name ?: '')) }}"
                                            placeholder="Nhập tên người nhận"
                                        >
                                    </div>
                                    <div>
                                        <label class="form-label small text-muted mb-1" for="misaReceiverEmail">Email nhận hóa đơn</label>
                                        <input
                                            id="misaReceiverEmail"
                                            name="receiver_email"
                                            form="issueMisaFromDeliveryForm"
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
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div class="small fst-italic text-muted">Vui lòng kiểm tra thông tin trước khi phát hành.</div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" id="confirmIssueFromDeliveryBtn">Xác nhận & Phát hành</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const issueButton = document.getElementById('confirmIssueFromDeliveryBtn');
        const form = document.getElementById('issueMisaFromDeliveryForm');

        if (!issueButton || !form) return;

        issueButton.addEventListener('click', function () {
            issueButton.disabled = true;
            form.action = '{{ route('admin.sales-orders.invoices.misa.publish', $salesOrder) }}';
            form.submit();
        });
    })();
</script>
@endif
@endsection
