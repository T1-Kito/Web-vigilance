@extends('layouts.admin')

@section('title', 'Phát hành hóa đơn')

@section('content')
@php
    $subTotal = (float) $order->items->sum(fn($i) => (float) ($i->price ?? 0) * (int) ($i->quantity ?? 0));
@endphp

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Phát hành hóa đơn</h1>
            <div class="text-muted">Đơn hàng nguồn: <span class="fw-semibold">{{ $order->order_code }}</span></div>
        </div>
        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-secondary">Quay lại đơn hàng</a>
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

    <form method="POST" action="{{ route('admin.invoices.store', $order) }}">
        @csrf
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold">Dòng hàng hóa đơn</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0 align-middle">
                                <thead>
                                    <tr>
                                        <th class="ps-3">Sản phẩm</th>
                                        <th style="width:120px;">Đơn vị</th>
                                        <th style="width:100px;">SL</th>
                                        <th style="width:160px;">Đơn giá</th>
                                        <th style="width:160px;">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($order->items as $item)
                                    @php
                                        $lineTotal = (float) ($item->price ?? 0) * (int) ($item->quantity ?? 0);
                                    @endphp
                                    <tr>
                                        <td class="ps-3">{{ $item->product->name ?? ('Sản phẩm #' . $item->product_id) }}</td>
                                        <td>{{ $item->unit ?: '---' }}</td>
                                        <td>{{ (int) $item->quantity }}</td>
                                        <td>{{ number_format((float) $item->price, 0, ',', '.') }}đ</td>
                                        <td class="fw-semibold">{{ number_format($lineTotal, 0, ',', '.') }}đ</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm" style="position: sticky; top: 16px;">
                    <div class="card-header bg-white fw-bold">Thông số hóa đơn</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Ngày phát hành</label>
                            <input type="datetime-local" name="issued_at" class="form-control" value="{{ old('issued_at', now()->format('Y-m-d\\TH:i')) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Trạng thái</label>
                            <select name="status" class="form-select" required>
                                <option value="issued" @selected(old('status', 'issued') === 'issued')>Đã phát hành</option>
                                <option value="draft" @selected(old('status') === 'draft')>Nháp</option>
                                <option value="cancelled" @selected(old('status') === 'cancelled')>Đã hủy</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Chiết khấu (%)</label>
                            <input type="number" min="0" max="100" step="0.01" name="discount_percent" class="form-control" value="{{ old('discount_percent', 0) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">VAT (%)</label>
                            <input type="number" min="0" max="100" step="0.01" name="vat_percent" class="form-control" value="{{ old('vat_percent', 8) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Ghi chú</label>
                            <textarea name="note" class="form-control" rows="3">{{ old('note') }}</textarea>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between mb-1">
                            <span>Tạm tính:</span>
                            <strong>{{ number_format($subTotal, 0, ',', '.') }}đ</strong>
                        </div>

                        <div class="d-grid gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">Phát hành hóa đơn</button>
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-light border">Hủy</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
