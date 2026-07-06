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
        public ?string $previousStatus = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $request = $this->maintenanceRequest->loadMissing(['property', 'unit']);

        return (new MailMessage)
            ->subject(__('Maintenance request: :status — :title', [
                'status' => __(ucfirst(str_replace('_', ' ', $this->status))),
                'title' => $request->title,
            ]))
            ->markdown('emails.maintenance.status-changed', [
                'maintenanceRequest' => $request,
                'previousStatus' => $this->previousStatus ?? $request->status,
                'newStatus' => $this->status,
                'actionUrl' => route('maintenance-requests.show', $request),
                'actionText' => __('View request'),
            ]);
    }
}