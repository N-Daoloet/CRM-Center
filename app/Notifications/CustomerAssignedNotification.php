<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CustomerAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public int $assignmentId,
        public string $customerName
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'assignment_id' => $this->assignmentId,
            'customer_name' => $this->customerName,
        ];
    }
}