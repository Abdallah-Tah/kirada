<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ __('pdf.receipt.title') }} — {{ $payment->payment_number }}</title>
    @include('pdf.partials.styles')
</head>
<body>
    @php
        $currency = $payment->displayCurrency();
        $period = $payment->rentInvoice?->invoice_month?->locale('fr')->translatedFormat('F Y');
    @endphp

    @include('pdf.partials.header', [
        'pdfReference' => $payment->payment_number,
    ])

    <h1 class="pdf-title">{{ __('pdf.receipt.title') }}</h1>
    <p class="pdf-subtitle">
        @if ($payment->rentInvoice?->invoice_number)
            {{ __('pdf.labels.invoice') }} {{ $payment->rentInvoice->invoice_number }}
        @endif
        @if ($period)
            — {{ $period }}
        @endif
    </p>

    <div class="pdf-highlight pdf-highlight-success">
        {{ __('pdf.receipt.confirmed') }}
        @if ($payment->confirmed_at)
            — {{ $payment->confirmed_at->format('d/m/Y H:i') }}
        @endif
    </div>

    <div class="pdf-payment-reference" style="text-align:center;">
        <div class="pdf-payment-reference-label">{{ __('pdf.receipt.amount_paid') }}</div>
        <div class="pdf-payment-reference-value">
            {{ \App\Support\PdfMoney::format($payment->amount, $currency) }}
        </div>
    </div>

    <div class="pdf-card">
        <table class="pdf-details">
            <tr>
                <th>{{ __('pdf.labels.tenant') }}</th>
                <td>{{ $payment->tenant?->full_name ?: '—' }}</td>
            </tr>
            <tr>
                <th>{{ __('pdf.labels.property') }}</th>
                <td>
                    {{ $payment->property?->name ?: '—' }}
                    @if ($payment->unit?->unit_number)
                        — {{ __('Unit') }} {{ $payment->unit->unit_number }}
                    @endif
                </td>
            </tr>
            <tr>
                <th>{{ __('pdf.labels.landlord') }}</th>
                <td>{{ $payment->landlord?->name ?: '—' }}</td>
            </tr>
            <tr>
                <th>{{ __('pdf.labels.payment_date') }}</th>
                <td>{{ $payment->payment_date?->format('d/m/Y') ?: '—' }}</td>
            </tr>
            <tr>
                <th>{{ __('pdf.labels.method') }}</th>
                <td>
                    {{ \Illuminate\Support\Facades\Lang::has("pdf.payment_methods.{$payment->method}")
                        ? __("pdf.payment_methods.{$payment->method}")
                        : ucfirst(str_replace('_', ' ', $payment->method)) }}
                </td>
            </tr>
            @if ($payment->reference_number)
                <tr>
                    <th>{{ __('pdf.labels.transaction_reference') }}</th>
                    <td>{{ $payment->reference_number }}</td>
                </tr>
            @endif
            @if ($payment->rentInvoice?->payment_reference)
                <tr>
                    <th>{{ __('pdf.payment_reference.label') }}</th>
                    <td>{{ $payment->rentInvoice->payment_reference }}</td>
                </tr>
            @endif
            @if ($payment->confirmer)
                <tr>
                    <th>{{ __('pdf.labels.confirmed_by') }}</th>
                    <td>{{ $payment->confirmer->name }}</td>
                </tr>
            @endif
        </table>
    </div>

    @if ($payment->notes)
        <div class="pdf-note">
            <span class="pdf-small-label">{{ __('pdf.labels.notes') }}</span><br>
            {{ $payment->notes }}
        </div>
    @endif

    @if ($pdfSupportEmail)
        <div class="pdf-last-page-footer">{{ $pdfSupportEmail }}</div>
    @endif
</body>
</html>
