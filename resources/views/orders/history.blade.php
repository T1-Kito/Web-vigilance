@extends('layouts.user')

@section('title', 'Lịch sử đơn hàng')

@section('content')
<div class="container py-4">
    <h2 class="fw-bold mb-4" style="color:#007BFF;">Lịch sử đơn hàng</h2>

    @if($orders->isEmpty())
        @if($isGuest)
            <div class="alert alert-info">
                Chưa có đơn hàng nào trên trình duyệt này.
            </div>
        @else
            <div class="alert alert-info">Bạn chưa có đơn hàng nào.</div>
        @endif
    @else
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Ngày đặt</th>
                                <th>Trạng thái</th>
                                <th class="text-end">Tổng tiền</th>
                                <th class="text-end"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                                @php
                                    $code = $order->order_code ?? ("VK" . str_pad($order->id, 6, '0', STR_PAD_LEFT));
                                    $total = $order->items->sum(function($i){ return ($i->price * $i->quantity); });
                                @endphp
                                <tr>
                                    <td><strong>{{ $code }}</strong></td>
                                    <td>{{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
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
                                    <td class="text-end"><span class="fw-bold text-success">{{ number_format($total, 0, ',', '.') }}đ</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('orders.lookup', ['order_code' => $code]) }}" class="btn btn-sm btn-outline-primary">
                                            Tra cứu
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
