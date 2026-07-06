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
        $request = $this->maintenanceRequest->loadMissing(['property', 'unit']);
        $comment = $this->comment->loadMissing('user');

        return (new MailMessage)
            ->subject(__('New comment on: :title', ['title' => $request->title]))
            ->markdown('emails.maintenance.comment-added', [
                'maintenanceRequest' => $request,
                'comment' => $comment,
                'actionUrl' => route('maintenance-requests.show', $request),
                'actionText' => __('View request'),
            ]);
    }
}