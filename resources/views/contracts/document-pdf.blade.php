<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $contract->title }} — {{ $contract->reference }}</title>
    @include('pdf.partials.styles')
    <style>
        .watermark {
            position: fixed;
            top: 47%;
            left: 9%;
            width: 82%;
            color: rgba(14, 165, 233, 0.055);
            font-size: 52px;
            font-weight: bold;
            text-align: center;
            transform: rotate(-42deg);
            white-space: nowrap;
            z-index: 0;
        }
        .contract-page { position: relative; z-index: 1; }
        .contract-content { color: #1e293b; font-size: 10.5px; line-height: 1.55; }
        .contract-content h1 { color: #071a3a; font-size: 18px; text-align: center; }
        .contract-content h2 { margin: 15px 0 5px; color: #071a3a; font-size: 12px; }
        .contract-content p { margin: 0 0 7px; text-align: justify; }
        .contract-closing { margin-top: 16px; font-style: italic; }
        .signature-name { margin: 3px 0 7px; color: #071a3a; font-weight: bold; }
        .signature-image img { max-width: 180px; max-height: 56px; }
        .signature-meta { margin-top: 6px; padding-top: 5px; border-top: 1px dashed #cbd5e1; color: #64748b; font-size: 8px; }
        .signature-pending { color: #94a3b8; font-size: 10px; font-style: italic; }
        .certificate { margin-top: 20px; padding-top: 14px; border-top: 3px solid #0ea5e9; page-break-before: always; }
        .certificate-table { width: 100%; margin-top: 8px; border-collapse: collapse; table-layout: fixed; font-size: 7.5px; }
        .certificate-table thead { display: table-header-group; }
        .certificate-table tr { page-break-inside: avoid; }
        .certificate-table th, .certificate-table td { padding: 5px; border-bottom: 1px solid #dbeafe; text-align: left; vertical-align: top; overflow-wrap: break-word; }
        .certificate-table th { background: #071a3a; color: #ffffff; font-size: 6.8px; text-transform: uppercase; }
        .certificate-hash { color: #64748b; }
    </style>
</head>
<body>
    <div class="watermark">{{ $contract->reference }}</div>
    <div class="contract-page">

    @include('pdf.partials.header', [
        'pdfReference' => $contract->reference,
        'pdfDocumentDate' => $contract->created_at?->format('d/m/Y'),
    ])

    @if ($contract->isCompleted())
        <div class="pdf-highlight pdf-highlight-success">
            {{ __('pdf.contract.signed_banner', [
                'date' => $contract->completed_at?->format('d/m/Y \à H:i') ?: '—',
            ]) }}
        </div>
    @else
        <div class="pdf-highlight">
            {{ __('pdf.contract.pending_banner', [
                'signed' => $contract->signedCount(),
                'total' => $contract->signatures->count(),
            ]) }}
        </div>
    @endif

    <div class="contract-content">
        {!! $contract->body_html !!}
    </div>

    <table class="pdf-signature-table">
        @foreach ($contract->signatures->chunk(2) as $pair)
            <tr>
                @foreach ($pair as $signature)
                    <td class="pdf-signature-cell">
                        <div class="pdf-small-label">{{ $signature->role_label }}</div>
                        <div class="signature-name">{{ $signature->name }}</div>
                        @if ($signature->isSigned() && $signature->signature_data)
                            <div class="signature-image">
                                <img src="{{ $signature->signature_data }}" alt="{{ __('Signature') }}">
                            </div>
                            <div class="signature-meta">
                                {{ __('pdf.contract.signed_on', [
                                    'date' => $signature->signed_at?->format('d/m/Y \à H:i') ?: '—',
                                ]) }}
                                @if ($signature->signed_ip)
                                    — IP {{ $signature->signed_ip }}
                                @endif
                            </div>
                        @else
                            <div class="signature-pending">{{ __('pdf.contract.awaiting_signature') }}</div>
                        @endif
                    </td>
                @endforeach
                @if ($pair->count() === 1)
                    <td class="pdf-signature-cell" style="border:0;"></td>
                @endif
            </tr>
        @endforeach
    </table>

    <section class="certificate">
        <h2 class="pdf-section-title">{{ __('pdf.contract.certificate_title') }}</h2>
        <p class="pdf-muted">
            {{ __('pdf.contract.certificate_description', ['reference' => $contract->reference]) }}
        </p>
        <table class="certificate-table">
            <thead>
                <tr>
                    <th style="width:24%;">{{ __('pdf.contract.party') }}</th>
                    <th style="width:11%;">{{ __('pdf.labels.status') }}</th>
                    <th style="width:18%;">{{ __('pdf.contract.timestamp') }}</th>
                    <th style="width:14%;">IP</th>
                    <th style="width:33%;">{{ __('pdf.contract.fingerprint') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($contract->signatures as $signature)
                    <tr>
                        <td>
                            {{ $signature->name }}
                            @if ($signature->email)
                                <br><span class="pdf-muted">{{ $signature->email }}</span>
                            @endif
                            @if ($signature->typed_name)
                                <br><span class="pdf-muted">{{ __('Signature') }} : {{ $signature->typed_name }}</span>
                            @endif
                        </td>
                        <td>{{ $signature->isSigned() ? __('pdf.status.confirmed') : __('pdf.status.pending') }}</td>
                        <td>{{ $signature->signed_at?->format('d/m/Y H:i:s') ?: '—' }}</td>
                        <td>{{ $signature->signed_ip ?: '—' }}</td>
                        <td class="certificate-hash">
                            {{ $signature->signature_hash ? substr($signature->signature_hash, 0, 24).'…' : '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="pdf-last-page-footer">{{ __('pdf.contract.legal_notice') }}</p>
    </section>
    </div>
</body>
</html>
