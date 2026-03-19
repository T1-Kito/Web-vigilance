@extends('layouts.user')

@section('title', 'Thông báo')

@section('content')
@php
    $unreadCount = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;
    $statusLabel = function (?string $status) {
        return match ($status) {
            'pending' => 'Chờ duyệt',
            'processing' => 'Đang xử lý',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            default => $status ?: 'Không xác định',
        };
    };
    $statusClass = function (?string $status) {
        return match ($status) {
            'pending' => 'text-warning',
            'processing' => 'text-primary',
            'completed' => 'text-success',
            'cancelled' => 'text-danger',
            default => 'text-muted',
        };
    };
@endphp

<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="fw-bold mb-0" style="color: var(--brand-secondary);">Thông báo</h4>
    @auth
        <form method="POST" action="{{ route('notifications.read_all') }}" class="m-0">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm" style="border-radius: 999px;" {{ $unreadCount ? '' : 'disabled' }}>
                Đánh dấu đã đọc
            </button>
        </form>
    @else
        <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm" style="border-radius: 999px;">
            Đăng nhập
        </a>
    @endauth
</div>

@auth
    @if($notifications->count() === 0)
        <div class="p-4 text-center text-muted" style="border: 1px dashed rgba(15,23,42,0.18); border-radius: 16px;">
            Chưa có thông báo nào.
        </div>
    @else
        <div class="list-group" style="border-radius: 16px; overflow: hidden;">
            @foreach($notifications as $n)
                @php
                    $isUnread = $n->read_at === null;
                    $title = data_get($n->data, 'title', 'Thông báo');
                    $message = data_get($n->data, 'message', '');
                    $created = optional($n->created_at)->diffForHumans();
                @endphp
                <a href="{{ route('notifications.read', ['id' => $n->id]) }}"
                   class="list-group-item list-group-item-action py-3"
                   style="border: 0; border-bottom: 1px solid rgba(15,23,42,0.08); {{ $isUnread ? 'background: rgba(43,47,142,0.06);' : '' }}">
                    <div class="d-flex align-items-start gap-2">
                        <div class="mt-1" style="width: 10px;">
                            @if($isUnread)
                                <span style="display:inline-block; width:8px; height:8px; border-radius:999px; background: var(--brand-primary);"></span>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold" style="color:#0f172a;">{{ $title }}</div>
                            @if($message)
                                <div class="text-muted" style="font-size: 0.92rem;">{{ $message }}</div>
                            @endif
                            <div class="text-muted" style="font-size: 0.8rem;">{{ $created }}</div>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-3">
            {{ $notifications->links() }}
        </div>
    @endif
@else
    @if($guestOrders->count() === 0)
        <div class="p-4 text-center text-muted" style="border: 1px dashed rgba(15,23,42,0.18); border-radius: 16px;">
            Bạn chưa có đơn hàng nào trên thiết bị này.
        </div>
    @else
        <div class="list-group" style="border-radius: 16px; overflow: hidden;">
            @foreach($guestOrders as $order)
                @php
                    $code = (string) ($order->order_code ?? '');
                    $status = (string) ($order->status ?? '');
                    $created = optional($order->created_at)->diffForHumans();
                @endphp
                <a href="{{ route('orders.quote', ['orderCode' => $code]) }}"
                   class="list-group-item list-group-item-action py-3"
                   style="border: 0; border-bottom: 1px solid rgba(15,23,42,0.08);">
                    <div class="d-flex align-items-start justify-content-between gap-2">
                        <div class="flex-grow-1">
                            <div class="fw-bold" style="color:#0f172a;">Đơn hàng {{ $code }}</div>
                            <div class="text-muted" style="font-size: 0.92rem;">Trạng thái: <span class="fw-semibold {{ $statusClass($status) }}">{{ $statusLabel($status) }}</span></div>
                            <div class="text-muted" style="font-size: 0.8rem;">{{ $created }}</div>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endauth
@endsection
