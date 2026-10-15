<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DocumentExpiryAlert extends Notification
{
    use Queueable;

    public function __construct(public string $name, public string $date, public int $accountId) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'document_expiry',
            'message' => "{$this->name} akan kedaluwarsa pada {$this->date}.",
            'url' => route('digital-accounts.index'),
            'level' => 'warning',
            'key' => 'document_expiry_'.$this->accountId,
        ];
    }
}
