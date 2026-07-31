<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>
        {{ $isInvoice ? __('pdf.maintenance.invoice_title') : __('pdf.maintenance.quote_title') }}
        — {{ $quote->reference }}
    </title>
    @include('pdf.partials.styles')
</head>
<body>
    @php
        $request = $quote->maintenanceRequest;
        $currency = $quote->currency ?? $request?->property?->currency;
        $title = $isInvoice ? __('pdf.maintenance.invoice_title') : __('pdf.maintenance.quote_title');
        $statusKey = str_replace(' ', '_', strtolower($quote->status));
        $statusLabel = __("pdf.status.{$statusKey}");
        $statusClass = match ($quote->status) {
            'paid' => 'status-green',
            'declined' => 'status-red',
            'pending' => 'status-amber',
            'approved', 'invoiced' => 'status-blue',
            default => 'status-gray',
        };
        $documentDate = $isInvoice
            ? ($quote->invoiced_at ?? $quote->updated_at)
            : $quote->created_at;
    @endphp

    @include('pdf.partials.header', [
        'pdfReference' => $quote->reference,
        'pdfDocumentDate' => $documentDate?->format('d/m/Y'),
    ])

    <h1 class="pdf-title">{{ $title }}</h1>
    <p class="pdf-subtitle">
        {{ __('pdf.maintenance.work_order') }} {{ $request?->reference ?: '—' }}
        @if ($documentDate)
            — {{ $documentDate->format('d/m/Y') }}
        @endif
    </p>

    <div class="pdf-highlight {{ $quote->isPaid() ? 'pdf-highlight-success' : '' }}">
        {{ __('pdf.maintenance.total') }} :
        {{ \App\Support\PdfMoney::format($quote->total, $currency) }}
    </div>

    <div class="pdf-card">
        <table class="pdf-details">
            <tr>
                <th>{{ __('pdf.maintenance.provider') }}</th>
                <td>{{ $quote->maintenanceUser?->name ?: '—' }}</td>
            </tr>
            <tr>
                <th>{{ __('pdf.labels.landlord') }}</th>
                <td>{{ $request?->landlord?->name ?: '—' }}</td>
            </tr>
            @if ($request?->tenant)
                <tr>
                    <th>{{ __('pdf.labels.tenant') }}</th>
                    <td>{{ $request->tenant->full_name }}</td>
                </tr>
            @endif
            <tr>
                <th>{{ __('pdf.labels.property') }}</th>
                <td>
                    {{ $request?->property?->name ?: '—' }}
                    @if ($request?->unit?->unit_number)
                        — {{ __('Unit') }} {{ $request->unit->unit_number }}
                    @endif
                </td>
            </tr>
            <tr>
                <th>{{ __('pdf.labels.description') }}</th>
                <td>{{ $request?->title ?: '—' }}</td>
            </tr>
            <tr>
                <th>{{ __('pdf.labels.status') }}</th>
                <td><span class="pdf-status {{ $statusClass }}">{{ $statusLabel }}</span></td>
            </tr>
        </table>
    </div>

    <table class="pdf-lines">
        <thead>
            <tr>
                <th>{{ __('pdf.labels.description') }}</th>
                <th class="qty">{{ __('pdf.maintenance.quantity') }}</th>
                <th class="unit-price">{{ __('pdf.maintenance.unit_price') }}</th>
                <th class="num">{{ __('pdf.labels.amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($quote->items as $item)
                <tr>
                    <td>{{ $item->description ?: '—' }}</td>
                    <td class="qty">{{ number_format((float) $item->quantity, 2, ',', ' ') }}</td>
                    <td class="unit-price">{{ \App\Support\PdfMoney::format($item->unit_price, $currency) }}</td>
                    <td class="num">{{ \App\Support\PdfMoney::format($item->amount, $currency) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="3" class="pdf-summary-label">{{ __('pdf.maintenance.subtotal') }}</td>
                <td class="num">{{ \App\Support\PdfMoney::format($quote->subtotal, $currency) }}</td>
            </tr>
            @if ((float) $quote->tax_amount > 0)
                <tr>
                    <td colspan="3" class="pdf-summary-label">
                        {{ __('pdf.maintenance.tax') }} ({{ number_format((float) $quote->tax_rate, 2, ',', ' ') }} %)
                    </td>
                    <td class="num">{{ \App\Support\PdfMoney::format($quote->tax_amount, $currency) }}</td>
                </tr>
            @endif
            <tr class="pdf-total">
                <td colspan="3">{{ __('pdf.maintenance.total') }}</td>
                <td class="num">{{ \App\Support\PdfMoney::format($quote->total, $currency) }}</td>
            </tr>
        </tbody>
    </table>

    @if ($quote->notes)
        <div class="pdf-note">
            <span class="pdf-small-label">{{ __('pdf.labels.notes') }}</span><br>
            {{ $quote->notes }}
        </div>
    @endif

    @if ($pdfSupportEmail)
        <div class="pdf-last-page-footer">{{ $pdfSupportEmail }}</div>
    @endif
</body>
</html>
