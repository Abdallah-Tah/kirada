<?php

namespace App\Notifications;

use App\Models\RentPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TenantPaymentSubmitted extends Notification
{
    use Queueable;

    public function __construct(public RentPayment $payment) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $payment = $this->payment;
        $invoice = $payment->rentInvoice;
        $tenant = $payment->tenant;
        $amount = $payment->formatted_amount;

        return (new MailMessage)
            ->subject("Payment reported — {$amount} on invoice {$invoice?->invoice_number}")
            ->greeting('Payment awaiting confirmation')
            ->line("{$tenant?->full_name} reported a payment of {$amount} on invoice {$invoice?->invoice_number}.")
            ->line('Method: '.str_replace('_', ' ', ucfirst($payment->method))
                .($payment->reference_number ? " — Ref: {$payment->reference_number}" : ''))
            ->action('Review payment', route('rent-payments.index'))
            ->line('Confirm or reject it from the Rent Payments page.');
    }
}
