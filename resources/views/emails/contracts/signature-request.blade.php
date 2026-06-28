<x-mail::message>
# {{ __('Your signature is requested') }}

{{ __('Hello :name,', ['name' => $signerName]) }}

{{ __('You have been asked to sign the following document as **:role**:', ['role' => $roleLabel]) }}

**{{ $contractTitle }}**
{{ __('Reference') }}: {{ $reference }}

<x-mail::button :url="$url" color="primary">
{{ __('Review & sign the contract') }}
</x-mail::button>

{{ __('You will be able to read the full contract and draw your signature. Your electronic signature is legally binding once submitted.') }}

{{ __('If you did not expect this request, you can safely ignore this email.') }}

{{ __('Thanks,') }}<br>
{{ config('app.name') }}

<x-slot:subcopy>
{{ __("If you're having trouble with the button above, copy and paste the URL below into your web browser:") }}
<span style="word-break: break-all;">{{ $url }}</span>
</x-slot:subcopy>
</x-mail::message>
