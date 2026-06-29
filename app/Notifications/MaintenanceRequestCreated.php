<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceRequestCreated extends Notification
{
    use Queueable;

    public function __construct(public MaintenanceRequest $maintenanceRequest) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('New maintenance request: :title', ['title' => $this->maintenanceRequest->title]))
            ->line(__('A new maintenance issue was reported.'))
            ->line(__('Property: :property', ['property' => $this->maintenanceRequest->property?->name ?? __('Unknown')]))
            ->line(__('Priority: :priority', ['priority' => __(ucfirst($this->maintenanceRequest->priority))]))
            ->action(__('View request'), route('maintenance-requests.show', $this->maintenanceRequest));
    }
}
