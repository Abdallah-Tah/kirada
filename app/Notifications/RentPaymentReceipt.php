<?php

namespace App\Notifications;

use App\Models\RentPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RentPaymentReceipt extends Notification
{
    use Queueable;

    public function __construct(public RentPayment $payment, public string $pdf) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $payment = $this->payment;

        return (new MailMessage)
            ->subject(__('Payment receipt :number — :amount', [
                'number' => $payment->payment_number,
                'amount' => $payment->formatted_amount,
            ]))
            ->markdown('emails.rent.payment-receipt', [
                'payment' => $payment,
                'amount' => $payment->formatted_amount,
            ])
            ->attachData(
                $this->pdf,
                $payment->payment_number.'.pdf',
                ['mime' => 'application/pdf'],
            );
    }
}
