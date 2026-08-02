<x-mail::message>
# {{ __('Your contract is fully signed') }}

{{ __('Hello :name,', ['name' => $signerName]) }}

{{ __('All parties have now signed. Your countersigned copy is attached to this email as a PDF.') }}

**{{ $contractTitle }}**
{{ __('Reference') }}: {{ $reference }}
@if ($completedAt)
{{ __('Completed on :date', ['date' => $completedAt->format('d M Y')]) }}
@endif

<x-mail::table>
| {{ __('Party') }} | {{ __('Signed on') }} |
| :--- | :--- |
@foreach ($signers as $signer)
| {{ $signer->name }} | {{ $signer->signed_at?->format('d M Y') ?: '—' }} |
@endforeach
</x-mail::table>

{{ __('Keep this email for your records. The same document is always available in Kirada.') }}

{{ __('Thanks,') }}<br>
{{ config('app.name') }}
</x-mail::message>
