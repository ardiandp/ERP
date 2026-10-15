<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingReminderAlert extends Notification
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
            'type' => 'booking_reminder',
            'message' => $this->count.' booking dijadwalkan hari ini.',
            'url' => route('bookings.index'),
            'level' => 'info',
            'key' => 'booking_reminder',
        ];
    }
}
