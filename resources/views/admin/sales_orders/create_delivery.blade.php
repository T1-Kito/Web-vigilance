@extends('layouts.admin')

@section('title', 'Tạo phiếu xuất kho - Đơn bán ngoài')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Tạo phiếu xuất kho</h1>
            <div class="text-muted">Đơn bán: {{ $salesOrder->sales_order_code }}</div>
        </div>
        <a href="{{ route('admin.sales-orders.show', $salesOrder) }}" class="btn btn-outline-secondary">Quay lại</a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.sales-orders.deliveries.store', $salesOrder) }}">
        @csrf
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Họ và tên người nhận hàng <span class="text-danger">*</span></label>
                    <input class="form-control" name="receiver_name" value="{{ old('receiver_name', $salesOrder->invoice_company_name ?: $salesOrder->receiver_name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Địa chỉ <span class="text-danger">*</span></label>
                    <input class="form-control" name="receiver_address" value="{{ old('receiver_address', $salesOrder->receiver_address) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Lý do xuất <span class="text-danger">*</span></label>
                    <input class="form-control" name="delivery_reason" value="{{ old('delivery_reason', 'Xuất kho bán hàng cho ' . ($salesOrder->invoice_company_name ?: $salesOrder->receiver_name)) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Địa điểm giao hàng <span class="text-danger">*</span></label>
                    <input class="form-control" name="delivery_location" value="{{ old('delivery_location', $salesOrder->receiver_address) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Số chứng từ gốc kèm theo</label>
                    <input class="form-control" name="source_document_ref" value="{{ old('source_document_ref', $salesOrder->quote->quote_code ?? '') }}" placeholder="VD: VK11257/2025">
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th class="ps-3">Sản phẩm</th>
                                <th>SL đặt</th>
                                <th>Đã xuất</th>
                                <th>Còn lại</th>
                                <th style="width:180px;">SL xuất lần này</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($salesOrder->items as $item)
                            @php
                                $done = (int) ($deliveredMap[$item->id] ?? 0);
                                $remain = max(0, (int)$item->quantity - $done);
                            @endphp
                            <tr>
                                <td class="ps-3">{{ $item->product->name ?? ('SP #' . $item->product_id) }}</td>
                                <td>{{ (int) $item->quantity }}</td>
                                <td>{{ $done }}</td>
                                <td class="fw-semibold">{{ $remain }}</td>
                                <td>
                                    <input type="hidden" name="items[{{ $loop->index }}][sales_order_item_id]" value="{{ $item->id }}">
                                    <input type="number" class="form-control" min="0" max="{{ $remain }}" name="items[{{ $loop->index }}][quantity]" value="{{ old('items.'.$loop->index.'.quantity', 0) }}">
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white text-end">
                <button class="btn btn-primary" type="submit">Lưu phiếu xuất kho</button>
            </div>
        </div>
    </form>
</div>
@endsection
