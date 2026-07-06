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
        $request = $this->maintenanceRequest->loadMissing(['property', 'unit', 'reporter']);

        return (new MailMessage)
            ->subject(__('New maintenance request: :title', ['title' => $request->title]))
            ->markdown('emails.maintenance.request-created', [
                'maintenanceRequest' => $request,
                'actionUrl' => route('maintenance-requests.show', $request),
                'actionText' => __('View request'),
            ]);
    }
}