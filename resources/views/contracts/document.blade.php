<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $contract->title }} — {{ $contract->reference }}</title>
    <style>
        :root { --ink: #0F172A; --muted: #64748b; --line: #e2e8f0; --ocean: #0EA5E9; --green: #10B981; }
        * { box-sizing: border-box; }
        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
            color: var(--ink); margin: 0; background: #f8fafc; line-height: 1.6;
        }
        .sheet { max-width: 820px; margin: 0 auto; background: #fff; padding: 56px 64px; overflow-wrap: anywhere; word-break: break-word; }
        @media (max-width: 767px) {
            .sheet { padding: 24px 16px; max-width: 100%; overflow-x: hidden; }
            .brand { flex-direction: column; gap: 12px; align-items: flex-start; }
            .brand .ref { text-align: left; }
            .contract-title { font-size: 18px; }
            .contract-subtitle { font-size: 12px; margin-bottom: 20px; }
            h2 { font-size: 14px; }
            p { font-size: 13px; text-align: left; }
            .signatures { grid-template-columns: 1fr; gap: 16px; margin-top: 32px; }
            .sig-block { padding: 12px; }
            .certificate { margin-top: 32px; padding-top: 16px; }
            table { font-size: 11px; display: block; overflow-x: auto; -webkit-overflow-scrolling: touch; }
            th, td { padding: 6px 8px; }
            .foot { font-size: 10px; }
        }
        .brand { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid var(--ocean); padding-bottom: 16px; margin-bottom: 32px; }
        .brand-logo-card { display: inline-flex; align-items: center; background: #fff; border-radius: 10px; padding: 6px 12px; box-shadow: 0 1px 4px rgba(15,23,42,0.10); border: 1px solid rgba(226,232,240,0.9); }
        .brand-logo-card img { height: 28px; width: auto; object-fit: contain; display: block; }
        .brand .ref { text-align: right; font-size: 12px; color: var(--muted); }
        .contract-title { font-size: 22px; text-align: center; margin: 0 0 4px; letter-spacing: -0.01em; }
        .contract-subtitle { text-align: center; color: var(--muted); font-size: 13px; margin: 0 0 28px; }
        h2 { font-size: 15px; margin: 24px 0 6px; color: var(--ink); }
        p { margin: 0 0 10px; font-size: 14px; text-align: justify; }
        .contract-closing { margin-top: 24px; font-style: italic; }
        .status-banner { margin: 0 0 24px; padding: 10px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; }
        .status-banner.completed { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .status-banner.pending { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 48px; }
        .sig-block { border: 1px solid var(--line); border-radius: 12px; padding: 16px; }
        .sig-block .role { font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted); }
        .sig-block .name { font-weight: 700; margin: 2px 0 10px; }
        .sig-img { height: 72px; display: flex; align-items: center; }
        .sig-img img { max-height: 72px; max-width: 100%; }
        .sig-pending { height: 72px; display: flex; align-items: center; color: #cbd5e1; font-style: italic; font-size: 13px; }
        .sig-meta { font-size: 11px; color: var(--muted); margin-top: 8px; border-top: 1px dashed var(--line); padding-top: 8px; }
        .certificate { margin-top: 56px; border-top: 2px solid var(--line); padding-top: 24px; page-break-before: always; }
        .certificate h2 { font-size: 16px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 12px; }
        th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid var(--line); vertical-align: top; }
        th { color: var(--muted); font-weight: 600; text-transform: uppercase; font-size: 10px; letter-spacing: 0.04em; }
        .hash { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; word-break: break-all; color: var(--muted); }
        .foot { margin-top: 40px; font-size: 11px; color: var(--muted); text-align: center; }
        @media print {
            body { background: #fff; }
            .sheet { padding: 0; max-width: none; }
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="brand">
            <div class="brand-logo-card">
                <img src="{{ asset('brand/kirada-logo.jpg') }}?v=kirada-approved-20260627" alt="Kirada">
            </div>
            <div class="ref">
                Réf. {{ $contract->reference }}<br>
                {{ \Illuminate\Support\Carbon::parse($contract->created_at)->format('d/m/Y') }}
            </div>
        </div>

        @if($contract->isCompleted())
            <div class="status-banner completed">
                ✓ Document signé électroniquement par toutes les parties — Signé le
                {{ optional($contract->completed_at)->format('d/m/Y \à H:i') }}
            </div>
        @else
            <div class="status-banner pending">
                ⏳ Document en cours de signature — {{ $contract->signedCount() }} / {{ $contract->signatures->count() }} signature(s) recueillie(s)
            </div>
        @endif

        {!! $contract->body_html !!}

        <div class="signatures">
            @foreach($contract->signatures as $sig)
                <div class="sig-block">
                    <div class="role">{{ $sig->role_label }}</div>
                    <div class="name">{{ $sig->name }}</div>
                    @if($sig->isSigned() && $sig->signature_data)
                        <div class="sig-img"><img src="{{ $sig->signature_data }}" alt="Signature"></div>
                        <div class="sig-meta">
                            Signé le {{ optional($sig->signed_at)->format('d/m/Y \à H:i') }}@if($sig->signed_ip) — IP {{ $sig->signed_ip }}@endif
                        </div>
                    @else
                        <div class="sig-pending">En attente de signature…</div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="certificate">
            <h2>Certificat de signature électronique</h2>
            <p style="font-size:12px;color:#64748b;">
                Ce certificat atteste des signatures électroniques apposées sur le document
                {{ $contract->reference }} et constitue la preuve de leur intégrité.
            </p>
            <table>
                <thead>
                    <tr>
                        <th>Partie</th>
                        <th>Statut</th>
                        <th>Horodatage</th>
                        <th>Adresse IP</th>
                        <th>Empreinte (SHA-256)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contract->signatures as $sig)
                        <tr>
                            <td>{{ $sig->name }}<br><span style="color:#94a3b8;">{{ $sig->email ?: '—' }}</span>@if($sig->typed_name)<br><span style="color:#94a3b8;">Signé&nbsp;: {{ $sig->typed_name }}</span>@endif</td>
                            <td>{{ $sig->isSigned() ? 'Signé' : ucfirst($sig->status) }}</td>
                            <td>{{ $sig->signed_at ? $sig->signed_at->format('d/m/Y H:i:s') : '—' }}</td>
                            <td>{{ $sig->signed_ip ?: '—' }}</td>
                            <td class="hash">{{ $sig->signature_hash ? substr($sig->signature_hash, 0, 32).'…' : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="foot">
                Généré par Kirada — {{ \Illuminate\Support\Carbon::now()->format('d/m/Y H:i') }}.
                Les signatures électroniques recueillies ont valeur probante conformément au droit applicable.
            </p>
        </div>
    </div>
</body>
</html>
