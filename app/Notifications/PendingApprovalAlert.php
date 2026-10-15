<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PendingApprovalAlert extends Notification
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
            'type' => 'pending_approval',
            'message' => $this->count.' dokumen menunggu persetujuan.',
            'url' => route('dashboard'),
            'level' => 'warning',
            'key' => 'pending_approval',
        ];
    }
}
