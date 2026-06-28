<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $contract->title }} — {{ $contract->reference }}</title>
    <style>
        /* dompdf-friendly: table layouts, no grid/flex. DejaVu Sans handles UTF-8. */
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #0f172a; font-size: 11px; line-height: 1.5; margin: 0; }
        .brand-table { width: 100%; border-bottom: 2px solid #0EA5E9; padding-bottom: 8px; margin-bottom: 18px; }
        .brand-table td { vertical-align: bottom; }
        .wordmark { font-size: 18px; font-weight: bold; color: #0f172a; }
        .wordmark span { color: #0EA5E9; }
        .ref { text-align: right; font-size: 10px; color: #64748b; }
        .contract-title { font-size: 16px; text-align: center; margin: 0 0 2px; font-weight: bold; }
        .contract-subtitle { text-align: center; color: #64748b; font-size: 10px; margin: 0 0 16px; }
        h1.contract-title { font-size: 16px; }
        h2 { font-size: 12px; margin: 14px 0 4px; }
        p { margin: 0 0 7px; text-align: justify; }
        .contract-closing { margin-top: 16px; font-style: italic; }
        .banner { padding: 7px 12px; border-radius: 6px; font-size: 11px; font-weight: bold; margin-bottom: 16px; }
        .banner-done { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .banner-wait { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .sig-table { width: 100%; margin-top: 28px; border-collapse: separate; border-spacing: 12px; }
        .sig-cell { width: 50%; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; vertical-align: top; }
        .sig-role { font-size: 9px; text-transform: uppercase; color: #64748b; letter-spacing: 0.04em; }
        .sig-name { font-weight: bold; margin: 2px 0 6px; }
        .sig-img img { max-height: 56px; }
        .sig-pending { color: #cbd5e1; font-style: italic; font-size: 11px; }
        .sig-meta { font-size: 9px; color: #64748b; margin-top: 6px; border-top: 1px dashed #e2e8f0; padding-top: 5px; }
        .certificate { margin-top: 24px; border-top: 2px solid #e2e8f0; padding-top: 16px; page-break-before: always; }
        table.cert { width: 100%; border-collapse: collapse; font-size: 9px; margin-top: 8px; }
        table.cert th, table.cert td { text-align: left; padding: 5px 6px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        table.cert th { color: #64748b; font-size: 8px; text-transform: uppercase; }
        .hash { color: #64748b; }
        .foot { margin-top: 24px; font-size: 9px; color: #64748b; text-align: center; }
    </style>
</head>
<body>
    <table class="brand-table">
        <tr>
            <td class="wordmark">Kir<span>ada</span></td>
            <td class="ref">Réf. {{ $contract->reference }}<br>{{ \Illuminate\Support\Carbon::parse($contract->created_at)->format('d/m/Y') }}</td>
        </tr>
    </table>

    @if($contract->isCompleted())
        <div class="banner banner-done">
            Document signé électroniquement par toutes les parties — Signé le {{ optional($contract->completed_at)->format('d/m/Y \à H:i') }}
        </div>
    @else
        <div class="banner banner-wait">
            Document en cours de signature — {{ $contract->signedCount() }} / {{ $contract->signatures->count() }} signature(s) recueillie(s)
        </div>
    @endif

    {!! $contract->body_html !!}

    <table class="sig-table">
        @foreach($contract->signatures->chunk(2) as $pair)
            <tr>
                @foreach($pair as $sig)
                    <td class="sig-cell">
                        <div class="sig-role">{{ $sig->role_label }}</div>
                        <div class="sig-name">{{ $sig->name }}</div>
                        @if($sig->isSigned() && $sig->signature_data)
                            <div class="sig-img"><img src="{{ $sig->signature_data }}" alt="Signature"></div>
                            <div class="sig-meta">Signé le {{ optional($sig->signed_at)->format('d/m/Y \à H:i') }}@if($sig->signed_ip) — IP {{ $sig->signed_ip }}@endif</div>
                        @else
                            <div class="sig-pending">En attente de signature…</div>
                        @endif
                    </td>
                @endforeach
                @if($pair->count() === 1)<td class="sig-cell" style="border:none;"></td>@endif
            </tr>
        @endforeach
    </table>

    <div class="certificate">
        <h2>Certificat de signature électronique</h2>
        <p style="font-size:9px;color:#64748b;">
            Ce certificat atteste des signatures électroniques apposées sur le document {{ $contract->reference }} et constitue la preuve de leur intégrité.
        </p>
        <table class="cert">
            <thead>
                <tr>
                    <th>Partie</th><th>Statut</th><th>Horodatage</th><th>IP</th><th>Empreinte (SHA-256)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contract->signatures as $sig)
                    <tr>
                        <td>{{ $sig->name }}<br><span style="color:#94a3b8;">{{ $sig->email ?: '—' }}</span></td>
                        <td>{{ $sig->isSigned() ? 'Signé' : ucfirst($sig->status) }}</td>
                        <td>{{ $sig->signed_at ? $sig->signed_at->format('d/m/Y H:i:s') : '—' }}</td>
                        <td>{{ $sig->signed_ip ?: '—' }}</td>
                        <td class="hash">{{ $sig->signature_hash ? substr($sig->signature_hash, 0, 24).'…' : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="foot">
            Généré par Kirada — {{ \Illuminate\Support\Carbon::now()->format('d/m/Y H:i') }}.
            Les signatures électroniques recueillies ont valeur probante conformément au droit applicable.
        </p>
    </div>
</body>
</html>
