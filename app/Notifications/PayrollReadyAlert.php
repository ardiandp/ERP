<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PayrollReadyAlert extends Notification
{
    use Queueable;

    public function __construct(public string $periodName, public int $periodId) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'payroll_ready',
            'message' => "Payroll periode {$this->periodName} siap diproses.",
            'url' => route('payroll-periods.show', $this->periodId),
            'level' => 'info',
            'key' => 'payroll_ready_'.$this->periodId,
        ];
    }
}
