@extends('layouts.admin')

@section('title', 'Tạo phiếu xuất kho')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 fw-bold mb-1">Tạo phiếu xuất kho</h1>
            <div class="text-muted">Đơn hàng: {{ $order->order_code ?? ('#' . $order->id) }}</div>
        </div>
        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-secondary">Quay lại đơn</a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.deliveries.store', $order) }}">
        @csrf
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Thông tin giao vận</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Người giao hàng</label>
                        <input type="text" name="shipper_name" class="form-control" value="{{ old('shipper_name') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SĐT người giao</label>
                        <input type="text" name="shipper_phone" class="form-control" value="{{ old('shipper_phone') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Ghi chú</label>
                        <textarea name="note" class="form-control" rows="2">{{ old('note') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Chi tiết xuất kho</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th class="ps-3">Sản phẩm</th>
                                <th>SL đặt</th>
                                <th>Đã xuất</th>
                                <th>Còn lại</th>
                                <th style="width:160px;">SL xuất lần này</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($order->items as $item)
                            @php
                                $ordered = (int) ($item->quantity ?? 0);
                                $delivered = (int) ($deliveredMap[$item->id] ?? 0);
                                $remaining = max(0, $ordered - $delivered);
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold">{{ $item->product->name ?? ('Sản phẩm #' . $item->product_id) }}</div>
                                </td>
                                <td>{{ $ordered }}</td>
                                <td>{{ $delivered }}</td>
                                <td>
                                    <span class="fw-semibold {{ $remaining > 0 ? 'text-danger' : 'text-success' }}">{{ $remaining }}</span>
                                </td>
                                <td>
                                    <input type="hidden" name="items[{{ $loop->index }}][order_item_id]" value="{{ $item->id }}">
                                    <input type="number" min="0" max="{{ $remaining }}" name="items[{{ $loop->index }}][quantity]" class="form-control" value="{{ old('items.' . $loop->index . '.quantity', 0) }}" {{ $remaining <= 0 ? 'disabled' : '' }}>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">Xác nhận xuất kho</button>
            </div>
        </div>
    </form>
</div>
@endsection
