<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('Rent Invoice') }} — {{ $invoice->invoice_number }}</title>
    <style>
        /* dompdf-friendly: table layouts, no grid/flex. DejaVu Sans handles UTF-8. */
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #0f172a; font-size: 11px; line-height: 1.5; margin: 0; }
        .brand-table { width: 100%; border-bottom: 2px solid #0EA5E9; padding-bottom: 8px; margin-bottom: 18px; }
        .brand-table td { vertical-align: bottom; }
        .wordmark { font-size: 18px; font-weight: bold; color: #0f172a; }
        .wordmark span { color: #0EA5E9; }
        .ref { text-align: right; font-size: 10px; color: #64748b; }
        h1 { font-size: 16px; text-align: center; margin: 0 0 2px; }
        .subtitle { text-align: center; color: #64748b; font-size: 10px; margin: 0 0 16px; }
        .banner { padding: 7px 12px; border-radius: 6px; font-size: 11px; font-weight: bold; margin-bottom: 16px; }
        .banner-paid { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .banner-due { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        table.details { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.details th, table.details td { text-align: left; padding: 6px 8px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        table.details th { color: #64748b; font-size: 9px; text-transform: uppercase; letter-spacing: 0.04em; width: 35%; }
        table.lines { width: 100%; border-collapse: collapse; margin-top: 16px; }
        table.lines th, table.lines td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        table.lines th { color: #64748b; font-size: 9px; text-transform: uppercase; letter-spacing: 0.04em; }
        table.lines td.num, table.lines th.num { text-align: right; }
        table.lines tr.total td { font-weight: bold; border-top: 2px solid #0f172a; border-bottom: none; }
        .payref { margin-top: 18px; border: 1px solid #bae6fd; background: #f0f9ff; border-radius: 8px; padding: 12px; }
        .payref .label { font-size: 9px; text-transform: uppercase; color: #0369a1; letter-spacing: 0.04em; }
        .payref .value { font-size: 16px; font-weight: bold; letter-spacing: 0.08em; margin-top: 2px; }
        .payref .hint { font-size: 9px; color: #64748b; margin-top: 4px; }
        .foot { margin-top: 28px; font-size: 9px; color: #64748b; text-align: center; }
    </style>
</head>
<body>
    <table class="brand-table">
        <tr>
            <td class="wordmark">Kir<span>ada</span></td>
            <td class="ref">{{ $invoice->invoice_number }}<br>{{ $invoice->created_at?->format('d/m/Y') }}</td>
        </tr>
    </table>

    <h1>{{ __('Rent Invoice') }}</h1>
    <p class="subtitle">{{ $invoice->invoice_month?->format('F Y') }} — {{ __('Due') }} {{ $invoice->due_date?->format('d/m/Y') }}</p>

    @if($invoice->isPaid())
        <div class="banner banner-paid">{{ __('Paid') }}</div>
    @else
        <div class="banner banner-due">{{ __('Amount due') }}: {{ $invoice->formatted_total_due }} — {{ __('Due') }} {{ $invoice->due_date?->format('d/m/Y') }}</div>
    @endif

    <table class="details">
        <tr><th>{{ __('Tenant') }}</th><td>{{ $invoice->tenant?->full_name }}</td></tr>
        <tr><th>{{ __('Property') }}</th><td>{{ $invoice->property?->name }}@if($invoice->unit) — {{ __('Unit') }} {{ $invoice->unit->unit_number }}@endif</td></tr>
        <tr><th>{{ __('Landlord') }}</th><td>{{ $invoice->landlord?->name }}</td></tr>
        <tr><th>{{ __('Status') }}</th><td>{{ __(str_replace('_', ' ', ucfirst($invoice->status))) }}</td></tr>
    </table>

    <table class="lines">
        <thead>
            <tr>
                <th>{{ __('Description') }}</th>
                <th class="num">{{ __('Amount') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ __('Monthly rent') }} — {{ $invoice->invoice_month?->format('F Y') }}</td>
                <td class="num">{{ $invoice->formatted_amount }}</td>
            </tr>
            @foreach($invoice->lineItems as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="num">{{ \App\Support\Money::format($item->amount, $invoice->displayCurrency()) }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td>{{ __('Total Due') }}</td>
                <td class="num">{{ $invoice->formatted_total_due }}</td>
            </tr>
        </tbody>
    </table>

    @if($invoice->payment_reference)
        <div class="payref">
            <div class="label">{{ __('Payment Reference') }}</div>
            <div class="value">{{ $invoice->payment_reference }}</div>
            <div class="hint">{{ __('Quote this reference when paying via Waafi, D-Money or CAC Pay.') }}</div>
        </div>
    @endif

    @if($invoice->notes)
        <p style="margin-top: 14px; color: #64748b;">{{ $invoice->notes }}</p>
    @endif

    <div class="foot">
        {{ __('Generated by Kirada on :date', ['date' => now()->format('d/m/Y H:i')]) }}
    </div>
</body>
</html>
