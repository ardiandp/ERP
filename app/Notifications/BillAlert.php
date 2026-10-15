<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BillAlert extends Notification
{
    use Queueable;

    public function __construct(
        public string $state,
        public string $billNumber,
        public string $dueDate,
        public int $billId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $label = $this->state === 'OVERDUE' ? 'sudah jatuh tempo' : 'akan jatuh tempo';

        return [
            'type' => 'bill_'.strtolower($this->state),
            'message' => "Tagihan {$this->billNumber} {$label} pada {$this->dueDate}.",
            'url' => route('bills.show', $this->billId),
            'level' => $this->state === 'OVERDUE' ? 'danger' : 'info',
            'key' => 'bill_'.strtolower($this->state).'_'.$this->billNumber,
        ];
    }
}
