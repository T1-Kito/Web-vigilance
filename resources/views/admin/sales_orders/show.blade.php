@extends('layouts.admin')

@section('title', 'Chi tiết đơn bán ngoài')

@section('content')
@php
    $total = (float) $salesOrder->items->sum(fn($i) => (float) ($i->unit_price ?? 0) * (int) ($i->quantity ?? 0));
    $completionRate = $totalOrdered > 0 ? min(100, round(($totalDelivered / $totalOrdered) * 100)) : 0;
@endphp

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Đơn bán ngoài: {{ $salesOrder->sales_order_code }}</h1>
            <div class="text-muted">Nguồn báo giá: {{ $salesOrder->quote->quote_code ?? '---' }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.sales-orders.index') }}" class="btn btn-outline-secondary">Quay lại danh sách</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Sản phẩm đơn bán</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-3">Tên sản phẩm</th>
                                    <th style="width:120px;">Đơn vị</th>
                                    <th style="width:110px;">SL</th>
                                    <th style="width:150px;">Đơn giá</th>
                                    <th style="width:160px;">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($salesOrder->items as $item)
                                <tr>
                                    <td class="ps-3">{{ $item->product->name ?? ('SP #' . $item->product_id) }}</td>
                                    <td>{{ $item->unit ?: '---' }}</td>
                                    <td>{{ (int) $item->quantity }}</td>
                                    <td>{{ number_format((float) $item->unit_price, 0, ',', '.') }}đ</td>
                                    <td class="fw-semibold">{{ number_format((float) $item->unit_price * (int) $item->quantity, 0, ',', '.') }}đ</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Phiếu xuất kho</div>
                <div class="card-body">
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <div class="small text-muted">Tiến độ giao hàng: {{ $totalDelivered }}/{{ $totalOrdered }} ({{ $completionRate }}%)</div>
                        <a href="{{ route('admin.sales-orders.deliveries.create', $salesOrder) }}" class="btn btn-sm btn-primary">Tạo phiếu xuất kho</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
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
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">Hóa đơn</div>
                <div class="card-body">
                    <div class="mb-3 text-end">
                        <a href="{{ route('admin.sales-orders.invoices.create', $salesOrder) }}" class="btn btn-sm btn-primary">Phát hành hóa đơn</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
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
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="position: sticky; top: 16px;">
                <div class="card-header bg-white fw-bold">Thông tin đơn bán</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Mã đơn</span><span class="fw-semibold">{{ $salesOrder->sales_order_code }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Khách hàng</span><span class="fw-semibold">{{ $salesOrder->invoice_company_name ?: $salesOrder->receiver_name }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">MST</span><span class="fw-semibold">{{ $salesOrder->customer_tax_code ?: '---' }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Trạng thái</span><span class="badge bg-secondary">{{ $salesOrder->status }}</span></div>
                    <div class="d-flex justify-content-between pt-2 border-top"><span class="fw-semibold">Tổng tiền</span><span class="fw-bold text-danger">{{ number_format($total, 0, ',', '.') }}đ</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
