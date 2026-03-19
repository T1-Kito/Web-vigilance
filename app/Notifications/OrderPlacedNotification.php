<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order,
        public bool $isForAdmin = false,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $orderCode = (string) ($this->order->order_code ?? '');

        return [
            'kind' => 'order_placed',
            'order_id' => $this->order->id,
            'order_code' => $orderCode,
            'status' => (string) ($this->order->status ?? ''),
            'title' => $this->isForAdmin
                ? 'Có đơn hàng mới'
                : 'Đặt hàng thành công',
            'message' => $this->isForAdmin
                ? ('Khách vừa đặt đơn ' . $orderCode . '. Vui lòng kiểm tra và duyệt đơn.')
                : ('Bạn đã đặt hàng thành công. Mã đơn: ' . $orderCode . '. Đơn đang chờ duyệt.'),
            'url' => $this->isForAdmin
                ? route('admin.orders.show', $this->order)
                : route('orders.quote', ['orderCode' => $orderCode]),
        ];
    }
}
