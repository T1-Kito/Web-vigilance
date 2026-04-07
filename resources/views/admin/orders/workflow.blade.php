@extends('layouts.admin')

@section('title', 'Quy trình chứng từ đơn hàng')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Quy trình chứng từ: {{ $order->order_code }}</h1>
            <div class="text-muted">Mô hình quản lý theo chuẩn nghiệp vụ: Đơn hàng → Xuất kho → Hóa đơn → Thu tiền.</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-secondary">Về chi tiết đơn</a>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-light border">Danh sách đơn</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                @foreach($steps as $step)
                    <div class="col-md-3">
                        <div class="p-3 rounded-3 border {{ $step['done'] ? 'border-success bg-success-subtle' : 'border-secondary-subtle bg-light' }}">
                            <div class="small text-muted mb-1">Bước {{ $loop->iteration }}</div>
                            <div class="fw-semibold">{{ $step['label'] }}</div>
                            <div class="small {{ $step['done'] ? 'text-success' : 'text-muted' }} mt-1">
                                {{ $step['done'] ? 'Đã phát sinh chứng từ' : 'Chưa thực hiện' }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                    <span>Phiếu xuất kho</span>
                    <a href="{{ route('admin.deliveries.create-from-order', $order) }}" class="btn btn-sm btn-primary">Tạo phiếu xuất</a>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Tổng SL đơn hàng</span>
                        <strong>{{ $totalOrdered }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Tổng SL đã xuất</span>
                        <strong class="text-primary">{{ $totalDelivered }}</strong>
                    </div>
                    <hr>

                    @forelse($deliveries as $delivery)
                        <div class="border rounded-3 p-3 mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="fw-semibold">{{ $delivery->delivery_code }}</div>
                                <span class="badge bg-{{ $delivery->status === 'confirmed' ? 'success' : ($delivery->status === 'cancelled' ? 'danger' : 'secondary') }}">
                                    {{ $delivery->status === 'confirmed' ? 'Đã xuất' : ($delivery->status === 'cancelled' ? 'Đã hủy' : 'Nháp') }}
                                </span>
                            </div>
                            <div class="small text-muted mb-2">{{ optional($delivery->delivered_at)->format('d/m/Y H:i') }}</div>
                            <a href="{{ route('admin.deliveries.show', $delivery) }}" class="btn btn-sm btn-outline-primary">Chi tiết phiếu</a>
                        </div>
                    @empty
                        <div class="text-muted">Chưa có phiếu xuất kho.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                    <span>Hóa đơn</span>
                    <a href="{{ route('admin.invoices.create-from-order', $order) }}" class="btn btn-sm btn-primary">Phát hành hóa đơn</a>
                </div>
                <div class="card-body">
                    @forelse($invoices as $invoice)
                        <div class="border rounded-3 p-3 mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="fw-semibold">{{ $invoice->invoice_code }}</div>
                                <span class="badge bg-{{ $invoice->status === 'issued' ? 'success' : ($invoice->status === 'cancelled' ? 'danger' : 'secondary') }}">
                                    {{ $invoice->status === 'issued' ? 'Đã phát hành' : ($invoice->status === 'cancelled' ? 'Đã hủy' : 'Nháp') }}
                                </span>
                            </div>
                            <div class="small text-muted mb-2">{{ optional($invoice->issued_at)->format('d/m/Y H:i') }}</div>
                            <div class="small mb-2">Tổng tiền: <strong class="text-danger">{{ number_format((float) $invoice->total_amount, 0, ',', '.') }}đ</strong></div>
                            <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary">Chi tiết hóa đơn</a>
                        </div>
                    @empty
                        <div class="text-muted">Chưa có hóa đơn.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
