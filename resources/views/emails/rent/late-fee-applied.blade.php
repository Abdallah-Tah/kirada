@component('mail::message')
# Late Fee Applied

Hello,

A late fee has been added to your rent invoice because payment was not received by the due date.

| | |
|---|---|
| **Invoice #** | {{ $invoice->invoice_number }} |
| **Original due date** | {{ $due }} |
| **Late fee added** | {{ $fee }} |
| **Total now due** | {{ $total }} |

@component('mail::button', ['url' => route('rent-invoices.index'), 'color' => 'error'])
View Invoice
@endcomponent

Please settle the outstanding balance as soon as possible to avoid additional fees.

Thanks,
**Kirada**
@endcomponent
