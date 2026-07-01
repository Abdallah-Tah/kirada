@component('mail::message')
# Rent Invoice — {{ $month }}

Hello,

A new rent invoice has been generated for **{{ $month }}**.

| | |
|---|---|
| **Invoice #** | {{ $invoice->invoice_number }} |
| **Amount due** | {{ $amount }} |
| **Due date** | {{ $due }} |

@component('mail::button', ['url' => route('rent-invoices.index'), 'color' => 'primary'])
View Invoice
@endcomponent

To pay, upload your payment proof through the tenant portal.

Thanks,
**Kirada**
@endcomponent
