<?php

namespace App\Notifications;

use App\Models\RentInvoice;
use App\Notifications\Concerns\NotifiesTenantPhone;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RentInvoiceGenerated extends Notification
{
    use NotifiesTenantPhone, Queueable;

    public function __construct(public RentInvoice $invoice) {}

    public function toMail(object $notifiable): MailMessage
    {
        $invoice = $this->invoice;
        $amount = Money::format($invoice->amount, $invoice->displayCurrency());
        $due = $invoice->due_date->format('d/m/Y');
        $month = $invoice->invoice_month->format('F Y');

        return (new MailMessage)
            ->subject("Rent invoice for {$month} — {$amount}")
            ->markdown('emails.rent.invoice-generated', [
                'invoice' => $invoice,
                'amount' => $amount,
                'due' => $due,
                'month' => $month,
            ]);
    }

    protected function tenantPhone(): ?string
    {
        return $this->invoice->tenant?->phone;
    }

    protected function phoneMessage(): string
    {
        $invoice = $this->invoice;
        $amount = Money::format($invoice->amount, $invoice->displayCurrency());

        return __('Kirada: rent invoice :number — :amount due :due. Payment reference: :reference', [
            'number' => $invoice->invoice_number,
            'amount' => $amount,
            'due' => $invoice->due_date->format('d/m/Y'),
            'reference' => $invoice->payment_reference ?? $invoice->invoice_number,
        ]);
    }
}
