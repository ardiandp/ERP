<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification
{
    use Queueable;

    public function __construct(public int $count) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'low_stock',
            'message' => $this->count.' produk memiliki stok menipis.',
            'url' => route('inventory.stock'),
            'level' => 'warning',
            'key' => 'low_stock',
        ];
    }
}
