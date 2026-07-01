@component('mail::message')
@if(str_starts_with($reminderKey, 'overdue'))
# ⚠ Overdue Rent Notice
@else
# Rent Payment Reminder
@endif

Hello,

@if(str_starts_with($reminderKey, 'before_due'))
This is a reminder that your rent payment is **due on {{ $due }}**.
@elseif($reminderKey === 'overdue_1')
Your rent was **due on {{ $due }}** and has not yet been received.
@else
Your rent was **due on {{ $due }}** and remains outstanding. Please arrange payment as soon as possible to avoid further late fees.
@endif

| | |
|---|---|
| **Invoice #** | {{ $invoice->invoice_number }} |
| **Total due** | {{ $amount }} |
| **Due date** | {{ $due }} |

@component('mail::button', ['url' => route('rent-invoices.index'), 'color' => 'primary'])
View & Pay
@endcomponent

If you have already submitted payment, please disregard this message.

Thanks,
**Kirada**
@endcomponent
