@extends('layouts.admin')

@section('title', 'Chi tiết phiếu xuất kho')

@section('content')
@php
    $order = $delivery->order;
    $salesOrder = $delivery->salesOrder;
    $sourceCode = $salesOrder->sales_order_code ?? ($order->order_code ?? ('#' . ($delivery->sales_order_id ?? $delivery->order_id ?? $delivery->id)));
    $quoteCode = $salesOrder?->quote?->quote_code;
    $items = $delivery->items ?? collect();
    $totalQty = (int) $items->sum('quantity');
@endphp

<div class="container-fluid py-4">
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
                        <table class="table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-3">Sản phẩm</th>
                                    <th style="width:130px;">Đơn vị</th>
                                    <th style="width:120px;">SL đặt</th>
                                    <th style="width:150px;">SL xuất</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($items as $line)
                                @php
                                    $soItem = $line->salesOrderItem;
                                    $orderItem = $line->orderItem;
                                    $unit = $soItem->unit ?? ($orderItem->unit ?? '---');
                                    $orderedQty = (int) ($soItem->quantity ?? ($orderItem->quantity ?? 0));
                                @endphp
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold">{{ $line->product->name ?? ('Sản phẩm #' . $line->product_id) }}</div>
                                        <div class="small text-muted">Mã SP: {{ $line->product_id }}</div>
                                    </td>
                                    <td>{{ $unit }}</td>
                                    <td>{{ $orderedQty }}</td>
                                    <td><span class="fw-bold text-primary">{{ (int) $line->quantity }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Không có dòng xuất kho.</td>
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
@endsection
