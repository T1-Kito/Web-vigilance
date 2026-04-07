@extends('layouts.admin')

@section('title', 'Chi tiết báo giá')

@section('content')
@php
    $orderCode = $quote->quote_code ?? ('BG' . str_pad($quote->id, 6, '0', STR_PAD_LEFT));
    $items = $quote->items ?? collect();
    $subTotal = (float) $items->sum(fn($i) => (float) ($i->price ?? 0) * (int) ($i->quantity ?? 0));
    $discount = (float) ($quote->discount_percent ?? 0);
    $vat = (float) ($quote->vat_percent ?? 8);
    $afterDiscount = max(0, $subTotal * (1 - $discount / 100));
    $vatAmount = $afterDiscount * ($vat / 100);
    $total = $afterDiscount + $vatAmount;

    $statusMap = [
        'pending' => ['label' => 'Chờ xử lý', 'class' => 'warning'],
        'approved' => ['label' => 'Đã duyệt', 'class' => 'info'],
        'won' => ['label' => 'Chốt thành công', 'class' => 'success'],
        'lost' => ['label' => 'Không chốt', 'class' => 'secondary'],
        'cancelled' => ['label' => 'Đã hủy', 'class' => 'danger'],
    ];
    $status = $statusMap[$quote->status] ?? ['label' => (string) $quote->status, 'class' => 'secondary'];

    $salesOrder = $quote->convertedSalesOrder;
    $deliveries = collect();
    $invoices = collect();
    if ($salesOrder) {
        $deliveries = \App\Models\Delivery::query()->where('sales_order_id', $salesOrder->id)->orderByDesc('created_at')->get();
        $invoices = \App\Models\Invoice::query()->where('sales_order_id', $salesOrder->id)->orderByDesc('created_at')->get();
    }

    $hasDelivery = $deliveries->count() > 0;
    $hasInvoice = $invoices->count() > 0;
@endphp

<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Chi tiết báo giá: {{ $orderCode }}</h1>
            <div class="text-muted">Khách hàng: {{ $quote->invoice_company_name ?: $quote->receiver_name }}</div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.quotes.edit', $quote) }}" class="btn btn-primary">Chỉnh sửa</a>

            @if($salesOrder)
                @if($hasDelivery)
                    <a href="{{ route('admin.sales-orders.show', $salesOrder) }}" class="btn btn-success">Đã xuất kho</a>
                @else
                    <a href="{{ route('admin.sales-orders.deliveries.create', $salesOrder) }}" class="btn btn-outline-success">Tạo phiếu xuất kho</a>
                @endif

                @if($hasInvoice)
                    <a href="{{ route('admin.sales-orders.show', $salesOrder) }}" class="btn btn-success">Đã phát hành hóa đơn</a>
                @else
                    <a href="{{ route('admin.sales-orders.invoices.create', $salesOrder) }}" class="btn btn-outline-success">Phát hành hóa đơn</a>
                @endif
            @endif

            <a href="{{ route('admin.quotes.index') }}" class="btn btn-outline-secondary">Quay lại</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-bold">Thông tin khách hàng</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6"><div class="text-muted small">Tên công ty</div><div class="fw-semibold">{{ $quote->invoice_company_name ?: '---' }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">Mã số thuế</div><div class="fw-semibold">{{ $quote->customer_tax_code ?: '---' }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">Người liên hệ</div><div class="fw-semibold">{{ $quote->customer_contact_person ?: '---' }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">SĐT liên hệ</div><div class="fw-semibold">{{ $quote->customer_phone ?: '---' }}</div></div>
                        <div class="col-md-6"><div class="text-muted small">Email</div><div class="fw-semibold">{{ $quote->customer_email ?: '---' }}</div></div>
                        <div class="col-12"><div class="text-muted small">Địa chỉ hóa đơn</div><div class="fw-semibold">{{ $quote->invoice_address ?: '---' }}</div></div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">Danh sách sản phẩm</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-3">Sản phẩm</th>
                                    <th style="width:120px;">Đơn vị</th>
                                    <th style="width:100px;">SL</th>
                                    <th style="width:160px;">Đơn giá</th>
                                    <th style="width:170px;">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                    @php $lineTotal = (float) $item->price * (int) $item->quantity; @endphp
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-semibold">{{ $item->product->name ?? ('Sản phẩm #' . $item->product_id) }}</div>
                                        </td>
                                        <td>{{ $item->unit ?: '---' }}</td>
                                        <td>{{ (int) $item->quantity }}</td>
                                        <td>{{ number_format((float) $item->price, 0, ',', '.') }}đ</td>
                                        <td class="fw-semibold">{{ number_format($lineTotal, 0, ',', '.') }}đ</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">Không có sản phẩm.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="position: sticky; top: 16px;">
                <div class="card-header bg-white fw-bold">Thông tin báo giá</div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Mã báo giá</span><span class="fw-semibold">{{ $orderCode }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Ngày tạo</span><span class="fw-semibold">{{ optional($quote->created_at)->format('d/m/Y H:i') }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Trạng thái</span><span class="badge bg-{{ $status['class'] }}">{{ $status['label'] }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Staff code</span><span class="fw-semibold">{{ $quote->staff_code ?: '---' }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Sales</span><span class="fw-semibold">{{ $quote->sales_name ?: '---' }}</span></div>
                    <hr>
                    <div class="d-flex justify-content-between mb-1"><span>Tạm tính</span><strong>{{ number_format($subTotal, 0, ',', '.') }}đ</strong></div>
                    <div class="d-flex justify-content-between mb-1"><span>Chiết khấu ({{ rtrim(rtrim(number_format($discount, 2, '.', ''), '0'), '.') }}%)</span><strong>{{ number_format($subTotal - $afterDiscount, 0, ',', '.') }}đ</strong></div>
                    <div class="d-flex justify-content-between mb-1"><span>VAT ({{ rtrim(rtrim(number_format($vat, 2, '.', ''), '0'), '.') }}%)</span><strong>{{ number_format($vatAmount, 0, ',', '.') }}đ</strong></div>
                    <div class="d-flex justify-content-between pt-2 border-top"><span class="fw-semibold">Tổng cộng</span><strong class="text-danger">{{ number_format($total, 0, ',', '.') }}đ</strong></div>
                    <hr>
                    <div class="text-muted small mb-1">Ghi chú</div>
                    <div class="fw-semibold">{{ $quote->note ?: '---' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
