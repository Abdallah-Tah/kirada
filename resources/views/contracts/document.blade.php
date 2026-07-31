<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $contract->title }} — {{ $contract->reference }}</title>
    <style>
        :root { --navy: #071a3a; --blue: #0c4a8a; --cyan: #22d3ee; --teal: #0f766e; --ink: #0F172A; --muted: #64748b; --line: #dbeafe; --green: #15803d; }
        * { box-sizing: border-box; }
        body {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
            color: var(--ink); margin: 0; background: #f8fafc; line-height: 1.6;
        }
        .sheet { max-width: 820px; min-height: 100vh; margin: 0 auto; background: #fff; padding: 0 64px 56px; overflow-wrap: anywhere; word-break: break-word; box-shadow: 0 24px 70px rgba(15, 23, 42, .08); }
        .top-band { height: 11px; margin: 0 -64px 24px; background: var(--navy); border-bottom: 3px solid #14b8a6; }
        @media (max-width: 767px) {
            .sheet { padding: 0 16px calc(80px + env(safe-area-inset-bottom, 0px)); max-width: 100%; overflow-x: hidden; }
            .top-band { margin: 0 -16px 20px; }
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
        .brand { display: flex; align-items: center; justify-content: space-between; margin-bottom: 26px; }
        .brand-logo-card { display: inline-flex; align-items: center; background: #fff; }
        .brand-logo-card img { height: 54px; width: auto; object-fit: contain; display: block; }
        .brand .ref { text-align: right; font-size: 12px; color: var(--muted); }
        .brand .ref strong { display: block; color: var(--navy); font-size: 15px; }
        .contract-title { color: var(--navy); font-size: 22px; text-align: center; margin: 0 0 4px; letter-spacing: -0.01em; }
        .contract-subtitle { text-align: center; color: var(--muted); font-size: 13px; margin: 0 0 28px; }
        h2 { font-size: 15px; margin: 24px 0 6px; color: var(--navy); }
        p { margin: 0 0 10px; font-size: 14px; text-align: justify; }
        .contract-closing { margin-top: 24px; font-style: italic; }
        .status-banner { margin: 0 0 24px; padding: 10px 16px; border-radius: 10px; font-size: 13px; font-weight: 600; }
        .status-banner.completed { background: #ecfdf5; color: #047857; border: 1px solid #86efac; }
        .status-banner.pending { background: #ecfeff; color: var(--teal); border: 1px solid #5eead4; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 48px; }
        .sig-block { border: 1px solid var(--line); border-radius: 12px; padding: 16px; }
        .sig-block .role { font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted); }
        .sig-block .name { font-weight: 700; margin: 2px 0 10px; }
        .sig-img { height: 72px; display: flex; align-items: center; }
        .sig-img img { max-height: 72px; max-width: 100%; }
        .sig-pending { height: 72px; display: flex; align-items: center; color: #cbd5e1; font-style: italic; font-size: 13px; }
        .sig-meta { font-size: 11px; color: var(--muted); margin-top: 8px; border-top: 1px dashed var(--line); padding-top: 8px; }
        .certificate { margin-top: 56px; border-top: 3px solid #0ea5e9; padding-top: 24px; page-break-before: always; }
        .certificate h2 { font-size: 16px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 12px; }
        th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid var(--line); vertical-align: top; }
        th { background: var(--navy); color: #fff; font-weight: 700; text-transform: uppercase; font-size: 10px; letter-spacing: 0.04em; }
        .hash { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; word-break: break-all; color: var(--muted); }
        .foot { margin-top: 40px; font-size: 11px; color: var(--muted); text-align: center; }
        @media print {
            @page { size: A4 portrait; margin: 12mm 14mm 18mm; }
            body { background: #fff; }
            .sheet { min-height: auto; padding: 0; max-width: none; box-shadow: none; }
            .top-band { margin: 0 0 18px; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="top-band"></div>
        <div class="brand">
            <div class="brand-logo-card">
                <img src="{{ asset('brand/kirada-logo-transparent.png') }}?v=kirada-approved-20260730" alt="Kirada">
            </div>
            <div class="ref">
                <strong>{{ $contract->reference }}</strong>
                {{ \Illuminate\Support\Carbon::parse($contract->created_at)->format('d/m/Y') }}
            </div>
        </div>

        @if($contract->isCompleted())
            <div class="status-banner completed">
                {{ __('pdf.contract.signed_banner', [
                    'date' => optional($contract->completed_at)->format('d/m/Y \à H:i'),
                ]) }}
            </div>
        @else
            <div class="status-banner pending">
                {{ __('pdf.contract.pending_banner', [
                    'signed' => $contract->signedCount(),
                    'total' => $contract->signatures->count(),
                ]) }}
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
                            {{ __('pdf.contract.signed_on', ['date' => optional($sig->signed_at)->format('d/m/Y \à H:i')]) }}@if($sig->signed_ip) — IP {{ $sig->signed_ip }}@endif
                        </div>
                    @else
                        <div class="sig-pending">{{ __('pdf.contract.awaiting_signature') }}</div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="certificate">
            <h2>{{ __('pdf.contract.certificate_title') }}</h2>
            <p style="font-size:12px;color:#64748b;">
                {{ __('pdf.contract.certificate_description', ['reference' => $contract->reference]) }}
            </p>
            <table>
                <thead>
                    <tr>
                        <th>{{ __('pdf.contract.party') }}</th>
                        <th>{{ __('pdf.labels.status') }}</th>
                        <th>{{ __('pdf.contract.timestamp') }}</th>
                        <th>Adresse IP</th>
                        <th>{{ __('pdf.contract.fingerprint') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contract->signatures as $sig)
                        <tr>
                            <td>{{ $sig->name }}<br><span style="color:#94a3b8;">{{ $sig->email ?: '—' }}</span>@if($sig->typed_name)<br><span style="color:#94a3b8;">Signé&nbsp;: {{ $sig->typed_name }}</span>@endif</td>
                            <td>{{ $sig->isSigned() ? __('pdf.status.confirmed') : __('pdf.status.pending') }}</td>
                            <td>{{ $sig->signed_at ? $sig->signed_at->format('d/m/Y H:i:s') : '—' }}</td>
                            <td>{{ $sig->signed_ip ?: '—' }}</td>
                            <td class="hash">{{ $sig->signature_hash ? substr($sig->signature_hash, 0, 32).'…' : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="foot">
                {{ __('pdf.footer.generated', [
                    'date' => \Illuminate\Support\Carbon::now()->format('d/m/Y'),
                    'time' => \Illuminate\Support\Carbon::now()->format('H:i'),
                ]) }}.
                {{ __('pdf.contract.legal_notice') }}
            </p>
        </div>
    </div>
</body>
</html>
