<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ __('pdf.invoice.title') }} — {{ $invoice->invoice_number }}</title>
    @include('pdf.partials.styles')
    <style>
        @page { size: A4 portrait; margin: 0; }
        html, body { width: 210mm; margin: 0; padding: 0; background: #ffffff; }
        body { color: #15233b; font-size: 11px; line-height: 1.45; }
        .invoice-sheet { width: 210mm; background: #ffffff; }
        .invoice-hero {
            height: 20mm;
            padding: 5mm 15mm;
            background: #071a3a;
            border-top: 4mm solid #041226;
            border-bottom: 1.5mm solid #21c7bd;
        }
        .invoice-hero-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .invoice-hero-table td { padding: 0; vertical-align: middle; }
        .invoice-logo-cell { width: 63%; }
        .invoice-logo-card {
            display: inline-block;
            width: 67mm;
            padding: 3.5mm 4.5mm 3mm;
            border-radius: 3mm;
            background: #ffffff;
            border: 0.4mm solid #bdeff2;
        }
        .invoice-logo {
            display: block;
            width: 58mm;
            height: auto;
        }
        .invoice-meta-cell { width: 37%; text-align: right; }
        .invoice-meta-card {
            display: inline-block;
            min-width: 49mm;
            padding: 3.2mm 4mm;
            border: 0.35mm solid #245488;
            border-right: 1.2mm solid #22d3c5;
            border-radius: 2.2mm;
            background: #0c2852;
            color: #d7e9ff;
            text-align: left;
        }
        .invoice-meta-kicker {
            color: #67e8f9;
            font-size: 7.7px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .invoice-meta-number {
            margin-top: 1.2mm;
            color: #ffffff;
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 0.2px;
            overflow-wrap: anywhere;
        }
        .invoice-meta-date { margin-top: 1mm; color: #a9c6e8; font-size: 9.5px; }
        .invoice-body { padding: 9mm 15mm 0; }
        .invoice-title-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .invoice-title-table td { padding: 0; vertical-align: bottom; }
        .invoice-eyebrow {
            margin-bottom: 1.4mm;
            color: #07899a;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1.15px;
            text-transform: uppercase;
        }
        .invoice-title {
            margin: 0;
            color: #071a3a;
            font-size: 28px;
            line-height: 1.08;
            letter-spacing: -0.45px;
        }
        .invoice-subtitle {
            margin-top: 2mm;
            color: #64748b;
            font-size: 11px;
        }
        .invoice-title-mark {
            position: relative;
            width: 14mm;
            height: 14mm;
            margin-left: auto;
            border: 0.5mm solid #a5edf0;
            border-radius: 4mm;
            background: #ecfeff;
        }
        .invoice-title-mark-sheet {
            position: absolute;
            left: 4mm;
            top: 2.8mm;
            width: 6mm;
            height: 8mm;
            border: 0.5mm solid #07899a;
            border-radius: 0.8mm;
        }
        .invoice-title-mark-line {
            width: 3.8mm;
            margin: 1.6mm auto 0;
            border-top: 0.45mm solid #07899a;
        }
        .invoice-amount {
            width: 100%;
            margin-top: 4mm;
            border: 0.4mm solid #54dacf;
            border-left: 2mm solid #14b8a6;
            border-radius: 3mm;
            border-collapse: separate;
            background: #eafcfb;
            table-layout: fixed;
        }
        .invoice-amount td { padding: 3.3mm 5mm; vertical-align: middle; }
        .invoice-amount-label {
            color: #0f766e;
            font-size: 8.3px;
            font-weight: bold;
            letter-spacing: 0.8px;
            text-transform: uppercase;
        }
        .invoice-amount-due { margin-top: 1.1mm; color: #476477; font-size: 9.5px; }
        .invoice-amount-value {
            color: #075f60;
            font-size: 21px;
            font-weight: bold;
            text-align: right;
            white-space: nowrap;
        }
        .invoice-section-label {
            margin: 3.5mm 0 1.5mm;
            color: #071a3a;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.75px;
            text-transform: uppercase;
        }
        .invoice-info-card {
            padding: 1.4mm;
            border: 0.35mm solid #cfe3f5;
            border-radius: 3mm;
            background: #f8fbff;
            page-break-inside: avoid;
        }
        .invoice-info-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 1.6mm;
            table-layout: fixed;
        }
        .invoice-info-grid td {
            width: 50%;
            min-height: 17mm;
            padding: 2.7mm 3.5mm;
            border: 0.3mm solid #d7e7f6;
            border-left: 1.2mm solid #32bfd0;
            border-radius: 2mm;
            background: #ffffff;
            vertical-align: top;
            overflow-wrap: break-word;
        }
        .invoice-info-label {
            color: #39718f;
            font-size: 7.5px;
            font-weight: bold;
            letter-spacing: 0.75px;
            text-transform: uppercase;
        }
        .invoice-info-value {
            margin-top: 1.3mm;
            color: #14233c;
            font-size: 11.5px;
            font-weight: bold;
            line-height: 1.35;
        }
        .invoice-status {
            display: inline-block;
            padding: 2mm 4mm;
            border-radius: 5mm;
            font-size: 9.5px;
            font-weight: bold;
            letter-spacing: 0.2px;
            text-transform: uppercase;
        }
        .invoice-lines-wrap {
            overflow: hidden;
            border: 0.35mm solid #cadff2;
            border-radius: 3mm;
            page-break-inside: auto;
        }
        .invoice-lines {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .invoice-lines thead { display: table-header-group; }
        .invoice-lines tr { page-break-inside: avoid; }
        .invoice-lines th {
            padding: 3mm 4mm;
            background: #071a3a;
            color: #ffffff;
            font-size: 8px;
            letter-spacing: 0.85px;
            text-align: left;
            text-transform: uppercase;
        }
        .invoice-lines td {
            padding: 3.1mm 4mm;
            border-bottom: 0.3mm solid #dce8f4;
            color: #334155;
            font-size: 10.5px;
            vertical-align: top;
            overflow-wrap: break-word;
        }
        .invoice-lines tbody tr:nth-child(even) td { background: #f8fbff; }
        .invoice-lines .invoice-money {
            width: 32%;
            text-align: right;
            white-space: nowrap;
        }
        .invoice-lines .invoice-total td {
            padding-top: 3.2mm;
            padding-bottom: 3.2mm;
            border-top: 0.8mm solid #30bde5;
            border-bottom: 0;
            background: #eaf3ff;
            color: #071a3a;
            font-size: 13px;
            font-weight: bold;
        }
        .invoice-payment {
            width: 100%;
            margin-top: 4mm;
            border-collapse: separate;
            border-spacing: 0;
            border: 0.35mm solid #1c548d;
            border-left: 2mm solid #22d3c5;
            border-radius: 3mm;
            background: #0a3266;
            color: #ffffff;
            page-break-inside: avoid;
        }
        .invoice-payment td { padding: 4mm; vertical-align: middle; }
        .invoice-payment-icon-cell { width: 22mm; padding-right: 0 !important; }
        .invoice-payment-icon {
            position: relative;
            width: 14mm;
            height: 14mm;
            border: 0.5mm solid #67e8f9;
            border-radius: 4mm;
            background: #0e417c;
        }
        .invoice-payment-icon-wallet {
            position: absolute;
            left: 2.5mm;
            top: 4mm;
            width: 9mm;
            height: 6mm;
            border: 0.5mm solid #67e8f9;
            border-radius: 1mm;
        }
        .invoice-payment-icon-dot {
            position: absolute;
            right: 3.2mm;
            top: 6.2mm;
            width: 1.4mm;
            height: 1.4mm;
            border-radius: 50%;
            background: #67e8f9;
        }
        .invoice-payment-label {
            color: #67e8f9;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1.1px;
            text-transform: uppercase;
        }
        .invoice-payment-reference {
            margin-top: 1mm;
            color: #ffffff;
            font-size: 21px;
            font-weight: bold;
            letter-spacing: 1.1px;
            overflow-wrap: anywhere;
        }
        .invoice-payment-hint {
            margin-top: 2mm;
            color: #d7eaff;
            font-size: 9.5px;
            line-height: 1.5;
        }
        .invoice-payment-reminder {
            margin-top: 1.5mm;
            color: #8de9ee;
            font-size: 8.2px;
        }
        .invoice-note {
            margin-top: 2.5mm;
            padding: 2.4mm 4mm;
            border-left: 1.2mm solid #38bdf8;
            background: #f3f8fd;
            color: #52647a;
            font-size: 9px;
            page-break-inside: avoid;
        }
        .invoice-footer {
            width: 100%;
            margin-top: 3mm;
            padding: 3mm 0 3.5mm;
            border-top: 0.35mm solid #d5e5f3;
            border-collapse: collapse;
            table-layout: fixed;
            page-break-inside: avoid;
        }
        .invoice-footer td { padding: 0; color: #64748b; font-size: 8.5px; vertical-align: top; }
        .invoice-footer strong { display: block; margin-bottom: 0.8mm; color: #17385d; font-size: 9px; }
        .invoice-footer-contact { text-align: right; }
    </style>
</head>
<body>
    @php
        $currency = $invoice->displayCurrency();
        $period = $invoice->invoice_month?->locale('fr')->translatedFormat('F Y');
        $statusKey = str_replace(' ', '_', strtolower($invoice->status));
        $statusLabel = __("pdf.status.{$statusKey}");
        $statusClass = match ($invoice->status) {
            'paid' => 'status-green',
            'unpaid', 'overdue' => 'status-red',
            'pending', 'sent', 'partially_paid' => 'status-amber',
            'cancelled' => 'status-gray',
            default => 'status-blue',
        };
        $paymentMethods = $invoice->landlord?->payoutAccounts
            ?->where('is_active', true)
            ->map(fn ($account) => $account->method_label)
            ->filter()
            ->unique()
            ->values()
            ->implode(', ');
    @endphp

    <div class="invoice-sheet">
        <div class="invoice-hero">
            <table class="invoice-hero-table">
                <tr>
                    <td class="invoice-logo-cell">
                        @if (is_string($pdfLogoPath ?? null) && is_file($pdfLogoPath))
                            <div class="invoice-logo-card">
                                <img src="{{ $pdfLogoPath }}" class="invoice-logo" alt="Kirada">
                            </div>
                        @endif
                    </td>
                    <td class="invoice-meta-cell">
                        <div class="invoice-meta-card">
                            <div class="invoice-meta-kicker">{{ __('pdf.labels.invoice') }}</div>
                            <div class="invoice-meta-number">{{ $invoice->invoice_number }}</div>
                            @if (!empty($pdfDocumentDate))
                                <div class="invoice-meta-date">{{ __('pdf.labels.issued_on') }} {{ $pdfDocumentDate }}</div>
                            @endif
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="invoice-body">
            <table class="invoice-title-table">
                <tr>
                    <td>
                        <div class="invoice-eyebrow">{{ __('pdf.invoice.eyebrow') }}</div>
                        <h1 class="invoice-title">{{ __('pdf.invoice.title') }}</h1>
                        <div class="invoice-subtitle">
                            {{ $period ?: '—' }}
                            @if ($invoice->due_date)
                                — {{ __('Due') }} {{ $invoice->due_date->format('d/m/Y') }}
                            @endif
                        </div>
                    </td>
                    <td style="width:18mm;">
                        <div class="invoice-title-mark">
                            <div class="invoice-title-mark-sheet">
                                <div class="invoice-title-mark-line"></div>
                                <div class="invoice-title-mark-line"></div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>

            <table class="invoice-amount">
                <tr>
                    <td>
                        <div class="invoice-amount-label">
                            {{ $invoice->isPaid() ? __('pdf.status.paid') : __('pdf.invoice.amount_due') }}
                        </div>
                        @if ($invoice->due_date && ! $invoice->isPaid())
                            <div class="invoice-amount-due">
                                {{ __('pdf.invoice.due_on', ['date' => $invoice->due_date->format('d/m/Y')]) }}
                            </div>
                        @endif
                    </td>
                    <td class="invoice-amount-value">
                        {{ \App\Support\PdfMoney::format($invoice->totalDue(), $currency) }}
                    </td>
                </tr>
            </table>

            <div class="invoice-section-label">{{ __('pdf.labels.invoice') }}</div>
            <div class="invoice-info-card">
                <table class="invoice-info-grid">
                    <tr>
                        <td>
                            <div class="invoice-info-label">{{ __('pdf.labels.tenant') }}</div>
                            <div class="invoice-info-value">{{ $invoice->tenant?->full_name ?: '—' }}</div>
                        </td>
                        <td>
                            <div class="invoice-info-label">{{ __('pdf.labels.property') }}</div>
                            <div class="invoice-info-value">
                                {{ $invoice->property?->name ?: '—' }}
                                @if ($invoice->unit?->unit_number)
                                    — {{ __('Unit') }} {{ $invoice->unit->unit_number }}
                                @endif
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="invoice-info-label">{{ __('pdf.labels.landlord') }}</div>
                            <div class="invoice-info-value">{{ $invoice->landlord?->name ?: '—' }}</div>
                        </td>
                        <td>
                            <div class="invoice-info-label">{{ __('pdf.labels.status') }}</div>
                            <div class="invoice-info-value">
                                <span class="invoice-status {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="invoice-section-label">{{ __('pdf.labels.description') }}</div>
            <div class="invoice-lines-wrap">
                <table class="invoice-lines">
                    <thead>
                        <tr>
                            <th>{{ __('pdf.labels.description') }}</th>
                            <th class="invoice-money">{{ __('pdf.labels.amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ __('pdf.invoice.monthly_rent') }}@if ($period) — {{ $period }}@endif</td>
                            <td class="invoice-money">{{ \App\Support\PdfMoney::format($invoice->amount, $currency) }}</td>
                        </tr>
                        @foreach ($invoice->lineItems as $item)
                            <tr>
                                <td>{{ $item->description ?: '—' }}</td>
                                <td class="invoice-money">{{ \App\Support\PdfMoney::format($item->amount, $currency) }}</td>
                            </tr>
                        @endforeach
                        <tr class="invoice-total">
                            <td>{{ __('pdf.invoice.total_due') }}</td>
                            <td class="invoice-money">{{ \App\Support\PdfMoney::format($invoice->totalDue(), $currency) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if ($invoice->payment_reference)
                <table class="invoice-payment">
                    <tr>
                        <td class="invoice-payment-icon-cell">
                            <div class="invoice-payment-icon">
                                <div class="invoice-payment-icon-wallet"></div>
                                <div class="invoice-payment-icon-dot"></div>
                            </div>
                        </td>
                        <td>
                            <div class="invoice-payment-label">{{ __('pdf.payment_reference.label') }}</div>
                            <div class="invoice-payment-reference">{{ $invoice->payment_reference }}</div>
                            <div class="invoice-payment-hint">
                                {{ $paymentMethods
                                    ? __('pdf.payment_reference.instruction', ['methods' => $paymentMethods])
                                    : __('pdf.payment_reference.instruction_without_methods') }}
                            </div>
                            <div class="invoice-payment-reminder">{{ __('pdf.payment_reference.keep_reference') }}</div>
                        </td>
                    </tr>
                </table>
            @endif

            @if ($invoice->notes)
                <div class="invoice-note">
                    <strong>{{ __('pdf.labels.notes') }}</strong> — {{ $invoice->notes }}
                </div>
            @endif

            <table class="invoice-footer">
                <tr>
                    <td>
                        <strong>{{ __('pdf.footer.generated', [
                            'date' => ($pdfGeneratedAt ?? now())->format('d/m/Y'),
                            'time' => ($pdfGeneratedAt ?? now())->format('H:i'),
                        ]) }}</strong>
                        {{ $invoice->invoice_number }}
                    </td>
                    <td class="invoice-footer-contact">
                        @if ($pdfSupportEmail)
                            <strong>{{ __('pdf.invoice.payment_help') }}</strong>
                            {{ $pdfSupportEmail }}
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
