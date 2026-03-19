@extends('layouts.user')

@section('title', 'Lịch sử đơn hàng')

@section('content')
<div class="container py-4">
    <h2 class="fw-bold mb-4" style="color:#007BFF;">Lịch sử đơn hàng</h2>
    <div class="card shadow-sm">
        <div class="card-body">
            @if($orders->isEmpty())
                <div class="alert alert-info">Bạn chưa có đơn hàng nào.</div>
            @else
            <div class="table-responsive">
                <table class="table align-middle" id="orders-table">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Ngày đặt</th>
                            <th>Trạng thái</th>
                            <th>Tổng tiền</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr id="order-row-{{ $order->id }}" @if(session('new_order_id') == $order->id) style="background:#fffbe9; border:2px solid #00B894;" @endif>
                            <td><strong>{{ $order->order_code ?? ("VK" . str_pad($order->id, 6, '0', STR_PAD_LEFT)) }}</strong></td>
                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            @php
                                $statusKey = (string) ($order->status ?? '');
                                $statusLabel = match($statusKey) {
                                    'pending' => 'Chờ xử lý',
                                    'processing' => 'Chờ xử lý',
                                    'completed' => 'Hoàn thành',
                                    'cancelled' => 'Đã hủy',
                                    default => $statusKey,
                                };
                                $statusBadge = match($statusKey) {
                                    'pending', 'processing' => 'warning',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                    default => 'secondary',
                                };
                            @endphp
                            <td><span class="badge bg-{{ $statusBadge }}">{{ $statusLabel }}</span></td>
                            <td>
                                @php $total = $order->items->sum(function($i){ return $i->price * $i->quantity; }); @endphp
                                <span class="fw-bold text-success">{{ number_format($total, 0, ',', '.') }}đ</span>
                            </td>
                            <td><a href="{{ route('orders.show', $order) }}" class="btn btn-sm btn-outline-primary">Xem chi tiết</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>
@if(session('new_order_id'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    var row = document.getElementById('order-row-{{ session('new_order_id') }}');
    if(row) {
        row.scrollIntoView({behavior:'smooth', block:'center'});
        row.classList.add('table-success');
        setTimeout(()=>row.classList.remove('table-success'), 3000);
    }
});
</script>
@endif
@endsection 