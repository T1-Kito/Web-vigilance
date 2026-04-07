@extends('layouts.admin')

@section('title', 'Chi tiết hóa đơn')

@section('content')
@php
    $order = $invoice->order;
@endphp

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Hóa đơn: {{ $invoice->invoice_code }}</h1>
            <div class="text-muted">Đơn hàng nguồn: <span class="fw-semibold">{{ $order->order_code ?? ('#' . $invoice->order_id) }}</span></div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-primary">Về đơn hàng</a>
            <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary">Danh sách hóa đơn</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">Dòng hóa đơn</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-3">Sản phẩm</th>
                                    <th style="width:120px;">Đơn vị</th>
                                    <th style="width:100px;">SL</th>
                                    <th style="width:150px;">Đơn giá</th>
                                    <th style="width:160px;">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($invoice->items as $line)
                                <tr>
                                    <td class="ps-3">{{ $line->product->name ?? ('Sản phẩm #' . $line->product_id) }}</td>
                                    <td>{{ $line->unit ?: '---' }}</td>
                                    <td>{{ (int) $line->quantity }}</td>
                                    <td>{{ number_format((float) $line->unit_price, 0, ',', '.') }}đ</td>
                                    <td class="fw-semibold">{{ number_format((float) $line->line_total, 0, ',', '.') }}đ</td>
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
                <div class="card-header bg-white fw-bold">Thông tin chứng từ</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Mã hóa đơn</span>
                        <span class="fw-semibold">{{ $invoice->invoice_code }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Ngày phát hành</span>
                        <span class="fw-semibold">{{ optional($invoice->issued_at)->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Trạng thái</span>
                        <span class="badge bg-{{ $invoice->status === 'issued' ? 'success' : ($invoice->status === 'cancelled' ? 'danger' : 'secondary') }}">
                            {{ $invoice->status === 'issued' ? 'Đã phát hành' : ($invoice->status === 'cancelled' ? 'Đã hủy' : 'Nháp') }}
                        </span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-1">
                        <span>Tạm tính</span>
                        <strong>{{ number_format((float) $invoice->sub_total, 0, ',', '.') }}đ</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Chiết khấu</span>
                        <strong>{{ number_format((float) $invoice->discount_percent, 2, ',', '.') }}%</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>VAT</span>
                        <strong>{{ number_format((float) $invoice->vat_percent, 2, ',', '.') }}%</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Tiền VAT</span>
                        <strong>{{ number_format((float) $invoice->vat_amount, 0, ',', '.') }}đ</strong>
                    </div>
                    <div class="d-flex justify-content-between pt-2 border-top">
                        <span class="fw-semibold">Tổng cộng</span>
                        <strong class="text-danger">{{ number_format((float) $invoice->total_amount, 0, ',', '.') }}đ</strong>
                    </div>

                    <hr>
                    <div>
                        <div class="text-muted small">Ghi chú</div>
                        <div class="fw-semibold">{{ $invoice->note ?: '---' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
