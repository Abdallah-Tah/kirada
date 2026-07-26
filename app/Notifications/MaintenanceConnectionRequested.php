<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceConnectionRequested extends Notification
{
    use Queueable;

    public function __construct(
        public User $landlord,
        public ?string $message = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__(':landlord wants to add you to their maintenance team', [
                'landlord' => $this->landlord->name,
            ]))
            ->markdown('emails.maintenance.connection-requested', [
                'landlord' => $this->landlord,
                'note' => $this->message,
                'actionUrl' => route('maintenance-network.inbox'),
                'actionText' => __('Review request'),
            ]);
    }
}
