<?php

namespace App\Notifications;

use App\Models\RentInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RentInvoiceGenerated extends Notification
{
    use Queueable;

    public function __construct(public RentInvoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $invoice = $this->invoice;
        $amount  = number_format($invoice->amount, 0) . ' DJF';
        $due     = $invoice->due_date->format('d/m/Y');
        $month   = $invoice->invoice_month->format('F Y');

        return (new MailMessage)
            ->subject("Rent invoice for {$month} — {$amount}")
            ->markdown('emails.rent.invoice-generated', [
                'invoice' => $invoice,
                'amount'  => $amount,
                'due'     => $due,
                'month'   => $month,
            ]);
    }
}
