<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public MaintenanceRequest $maintenanceRequest,
        public string $status,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Maintenance request status updated'))
            ->line(__('Status changed to: :status', ['status' => __(str_replace('_', ' ', ucfirst($this->status)))]))
            ->line($this->maintenanceRequest->title)
            ->action(__('View request'), route('maintenance-requests.show', $this->maintenanceRequest));
    }
}
