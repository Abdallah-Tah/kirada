<?php

namespace App\Notifications;

use App\Models\MaintenanceComment;
use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceCommentAdded extends Notification
{
    use Queueable;

    public function __construct(
        public MaintenanceRequest $maintenanceRequest,
        public MaintenanceComment $comment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('New comment on maintenance request'))
            ->line(__('A new comment was added to: :title', ['title' => $this->maintenanceRequest->title]))
            ->line(str($this->comment->comment)->limit(180)->toString())
            ->action(__('View request'), route('maintenance-requests.show', $this->maintenanceRequest));
    }
}
