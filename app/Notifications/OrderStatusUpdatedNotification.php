<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    protected function statusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Chờ duyệt',
            'processing' => 'Đang xử lý',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            default => $status,
        };
    }

    public function toArray(object $notifiable): array
    {
        $orderCode = (string) ($this->order->order_code ?? '');

        return [
            'kind' => 'order_status_updated',
            'order_id' => $this->order->id,
            'order_code' => $orderCode,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'title' => 'Cập nhật đơn hàng',
            'message' => 'Đơn ' . $orderCode . ' đã chuyển từ "' . $this->statusLabel($this->oldStatus) . '" sang "' . $this->statusLabel($this->newStatus) . '".',
            'url' => route('orders.quote', ['orderCode' => $orderCode]),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $orderCode = (string) ($this->order->order_code ?? '');
        $url = route('orders.quote', ['orderCode' => $orderCode]);

        return (new MailMessage)
            ->subject('Cập nhật đơn hàng ' . $orderCode)
            ->line('Đơn ' . $orderCode . ' đã chuyển từ "' . $this->statusLabel($this->oldStatus) . '" sang "' . $this->statusLabel($this->newStatus) . '".')
            ->action('Xem đơn hàng', $url)
            ->line('Cảm ơn bạn đã mua hàng.');
    }
}
