<x-mail::message>
# {{ __('Payment received') }}

{{ __('Your payment has been confirmed. Your official Kirada receipt is attached to this email.') }}

**{{ __('Receipt') }}:** {{ $payment->payment_number }}  
**{{ __('Invoice') }}:** {{ $payment->rentInvoice?->invoice_number }}  
**{{ __('Amount') }}:** {{ $amount }}  
**{{ __('Payment method') }}:** {{ __(str_replace('_', ' ', ucfirst($payment->method))) }}  
**{{ __('Payment date') }}:** {{ $payment->payment_date?->format('d/m/Y') }}

{{ __('Keep the attached PDF for your records.') }}

{{ __('Thank you,') }}  
{{ config('app.name') }}
</x-mail::message>
