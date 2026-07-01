<?php

namespace App\Notifications;

use App\Models\RentInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LateFeeApplied extends Notification
{
    use Queueable;

    public function __construct(
        public RentInvoice $invoice,
        public float $feeAmount,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $invoice  = $this->invoice;
        $fee      = number_format($this->feeAmount, 0) . ' DJF';
        $total    = number_format($invoice->totalDue(), 0) . ' DJF';
        $due      = $invoice->due_date->format('d/m/Y');

        return (new MailMessage)
            ->subject("Late fee of {$fee} applied to your rent invoice")
            ->markdown('emails.rent.late-fee-applied', [
                'invoice' => $invoice,
                'fee'     => $fee,
                'total'   => $total,
                'due'     => $due,
            ]);
    }
}
