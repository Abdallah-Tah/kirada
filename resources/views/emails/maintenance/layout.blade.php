<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? $emailSubject ?? '' }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; background: #f1f5f9; font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; color: #0f172a; }
        .wrapper { max-width: 600px; margin: 0 auto; padding: 24px; }
        .card { background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,0.08); }
        .header { background: linear-gradient(135deg, #0EA5E9 0%, #0284C7 100%); padding: 28px 32px; }
        .header img { height: 30px; }
        .body { padding: 28px 32px; }
        .subject { font-size: 20px; font-weight: 700; margin: 0 0 4px; }
        .meta { font-size: 13px; color: #64748b; margin: 0 0 20px; }
        .field-table { width: 100%; border-collapse: collapse; margin: 0 0 20px; }
        .field-table td { padding: 6px 0; vertical-align: top; font-size: 14px; }
        .field-table .label { color: #64748b; white-space: nowrap; width: 120px; font-weight: 600; }
        .field-table .value { color: #0f172a; font-weight: 500; }
        .priority-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .priority-low { background: #f0fdf4; color: #16a34a; }
        .priority-medium { background: #fffbeb; color: #d97706; }
        .priority-high { background: #fef2f2; color: #dc2626; }
        .priority-urgent { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .status-open { background: #eff6ff; color: #2563eb; }
        .status-in_progress { background: #fffbeb; color: #d97706; }
        .status-resolved { background: #f0fdf4; color: #16a34a; }
        .status-closed { background: #f1f5f9; color: #475569; }
        .status-cancelled { background: #fef2f2; color: #dc2626; }
        .description { background: #f8fafc; border-left: 3px solid #0EA5E9; padding: 14px 16px; border-radius: 0 8px 8px 0; margin: 0 0 20px; font-size: 14px; line-height: 1.6; }
        .comment-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; margin: 0 0 20px; font-size: 14px; line-height: 1.6; }
        .comment-author { font-size: 12px; color: #64748b; margin-bottom: 6px; font-weight: 600; }
        .cta { text-align: center; margin: 24px 0 8px; }
        .cta a { display: inline-block; background: #0EA5E9; color: #fff !important; text-decoration: none; padding: 12px 28px; border-radius: 10px; font-weight: 600; font-size: 14px; }
        .footer { padding: 20px 32px; background: #f8fafc; border-top: 1px solid #e2e8f0; }
        .footer p { margin: 0; font-size: 12px; color: #94a3b8; text-align: center; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <img src="{{ asset('brand/kirada-logo.png') }}" alt="Kirada">
            </div>
            <div class="body">
                @yield('email-content')

                <div class="cta">
                    <a href="{{ $actionUrl }}">{{ $actionText }}</a>
                </div>
            </div>
            <div class="footer">
                <p>Kirada — Plateforme de gestion immobilière<br>
                {{ config('app.url') }}</p>
            </div>
        </div>
    </div>
</body>
</html>