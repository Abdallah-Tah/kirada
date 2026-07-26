<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceConnectionResolved extends Notification
{
    use Queueable;

    /**
     * @param  'approved'|'rejected'  $outcome
     */
    public function __construct(
        public User $provider,
        public string $outcome,
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
        $approved = $this->outcome === 'approved';

        return (new MailMessage)
            ->subject($approved
                ? __(':provider accepted your maintenance request', ['provider' => $this->provider->name])
                : __(':provider declined your maintenance request', ['provider' => $this->provider->name]))
            ->markdown('emails.maintenance.connection-resolved', [
                'provider' => $this->provider,
                'approved' => $approved,
                'actionUrl' => route('maintenance-network.index'),
                'actionText' => __('View my providers'),
            ]);
    }
}
