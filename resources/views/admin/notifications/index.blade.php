@extends('layouts.admin')

@section('title', 'Thông báo')

@section('content')
@php
    $unreadCount = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;
@endphp

<div class="content-card">
    <div class="p-4 border-bottom d-flex align-items-center justify-content-between">
        <div>
            <h2 class="mb-1" style="font-weight: 800;">Thông báo</h2>
            <div class="text-muted">{{ $unreadCount ? ('Chưa đọc: ' . $unreadCount) : 'Tất cả đã đọc' }}</div>
        </div>
        <form method="POST" action="{{ route('admin.notifications.read_all') }}" class="m-0">
            @csrf
            <button type="submit" class="btn btn-outline-secondary" style="border-radius: 12px;" {{ $unreadCount ? '' : 'disabled' }}>
                <i class="bi bi-check2-all me-1"></i> Đánh dấu đã đọc
            </button>
        </form>
    </div>

    <div class="p-3">
        @if($notifications->count() === 0)
            <div class="p-4 text-center text-muted">Chưa có thông báo nào.</div>
        @else
            <div class="list-group" style="border-radius: 12px; overflow: hidden;">
                @foreach($notifications as $n)
                    @php
                        $isUnread = $n->read_at === null;
                        $title = data_get($n->data, 'title', 'Thông báo');
                        $message = data_get($n->data, 'message', '');
                        $orderId = data_get($n->data, 'order_id');
                        $orderId = is_int($orderId) ? $orderId : (is_string($orderId) && ctype_digit($orderId) ? (int) $orderId : null);
                        $orderMissing = $orderId !== null
                            && isset($existingOrderIds)
                            && is_array($existingOrderIds)
                            && !isset($existingOrderIds[$orderId]);
                        if ($orderMissing) {
                            $message = 'Đơn hàng đã bị xóa hoặc không tồn tại.';
                        }
                        $created = optional($n->created_at)->diffForHumans();
                    @endphp
                    <a href="{{ route('admin.notifications.read', ['id' => $n->id]) }}"
                       class="list-group-item list-group-item-action py-3"
                       style="border: 0; border-bottom: 1px solid rgba(15,23,42,0.08); {{ $isUnread ? 'background: rgba(251,191,36,0.15);' : '' }}">
                        <div class="d-flex align-items-start justify-content-between gap-3">
                            <div>
                                <div class="fw-bold">{{ $title }}</div>
                                @if($message)
                                    <div class="text-muted">{{ $message }}</div>
                                @endif
                                <div class="text-muted" style="font-size: 0.9rem;">{{ $created }}</div>
                            </div>
                            <div class="text-muted"><i class="bi bi-chevron-right"></i></div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-3">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
