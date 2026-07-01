<?php

namespace App\Notifications;

use App\Models\RentInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RentReminderDue extends Notification
{
    use Queueable;

    public function __construct(
        public RentInvoice $invoice,
        public string $reminderKey,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $invoice = $this->invoice;
        $amount  = number_format($invoice->totalDue(), 0) . ' DJF';
        $due     = $invoice->due_date->format('d/m/Y');
        $subject = $this->buildSubject($amount, $due);

        return (new MailMessage)
            ->subject($subject)
            ->markdown('emails.rent.reminder', [
                'invoice'     => $invoice,
                'reminderKey' => $this->reminderKey,
                'amount'      => $amount,
                'due'         => $due,
                'subject'     => $subject,
            ]);
    }

    private function buildSubject(string $amount, string $due): string
    {
        return match(true) {
            str_starts_with($this->reminderKey, 'before_due') => "Reminder: rent of {$amount} due on {$due}",
            str_starts_with($this->reminderKey, 'overdue')    => "Overdue notice: rent of {$amount} was due on {$due}",
            default => "Rent reminder — {$amount} due {$due}",
        };
    }
}
