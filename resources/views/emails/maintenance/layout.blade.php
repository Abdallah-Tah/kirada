<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? '' }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #f1f5f9; font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; color: #0f172a; -webkit-font-smoothing: antialiased; }
        .wrapper { max-width: 600px; margin: 0 auto; padding: 24px; }

        .card { background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 4px 12px rgba(15,23,42,0.08); }

        /* Header with white logo card on blue gradient */
        .header { background: linear-gradient(135deg, #0EA5E9 0%, #0284C7 100%); padding: 24px 32px; display: flex; align-items: center; justify-content: center; }
        .logo-card { background: #fff; border-radius: 10px; padding: 8px 16px; box-shadow: 0 2px 6px rgba(0,0,0,0.12); display: inline-block; }
        .logo-card img { height: 28px; display: block; }

        .body { padding: 28px 32px; }

        .subject { font-size: 20px; font-weight: 700; margin: 0 0 6px; color: #0f172a; letter-spacing: -0.01em; }
        .meta { font-size: 13px; color: #64748b; margin: 0 0 24px; }

        .field-table { width: 100%; border-collapse: collapse; margin: 0 0 20px; }
        .field-table td { padding: 7px 0; vertical-align: top; font-size: 14px; border-bottom: 1px solid #f1f5f9; }
        .field-table tr:last-child td { border-bottom: none; }
        .field-table .label { color: #94a3b8; white-space: nowrap; width: 110px; font-weight: 600; font-size: 12px; text-transform: uppercase; letter-spacing: 0.03em; }
        .field-table .value { color: #0f172a; font-weight: 500; }

        .priority-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; }
        .priority-low { background: #f0fdf4; color: #16a34a; }
        .priority-medium { background: #fffbeb; color: #d97706; }
        .priority-high { background: #fef2f2; color: #dc2626; }
        .priority-urgent { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; }
        .status-open { background: #eff6ff; color: #2563eb; }
        .status-in_progress { background: #fffbeb; color: #d97706; }
        .status-resolved { background: #f0fdf4; color: #16a34a; }
        .status-closed { background: #f1f5f9; color: #475569; }
        .status-cancelled { background: #fef2f2; color: #dc2626; }

        .description { background: #f8fafc; border-left: 3px solid #0EA5E9; padding: 14px 16px; border-radius: 0 8px 8px 0; margin: 0 0 20px; font-size: 14px; line-height: 1.6; color: #334155; }
        .comment-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; margin: 0 0 20px; font-size: 14px; line-height: 1.6; color: #334155; }
        .comment-author { font-size: 12px; color: #64748b; margin-bottom: 8px; font-weight: 600; }

        .context-msg { font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 20px; padding: 12px 16px; background: #f8fafc; border-radius: 8px; }

        .cta { text-align: center; margin: 24px 0 8px; }
        .cta a { display: inline-block; background: #0EA5E9; color: #fff !important; text-decoration: none; padding: 13px 32px; border-radius: 10px; font-weight: 600; font-size: 14px; }

        .footer { padding: 20px 32px; background: #f8fafc; border-top: 1px solid #e2e8f0; }
        .footer-brand { font-size: 13px; font-weight: 700; color: #475569; margin: 0 0 4px; }
        .footer-text { font-size: 12px; color: #94a3b8; line-height: 1.6; }
        .footer a { color: #64748b; text-decoration: none; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <div class="logo-card">
                    <img src="{{ asset('brand/kirada-logo.jpg') }}" alt="Kirada" height="28">
                </div>
            </div>
            <div class="body">
                @yield('email-content')

                <div class="cta">
                    <a href="{{ $actionUrl }}">{{ $actionText }}</a>
                </div>
            </div>
            <div class="footer">
                <p class="footer-brand">Kirada</p>
                <p class="footer-text">
                    {{ __('Smart rent management platform') }}<br>
                    <a href="{{ config('app.url') }}">{{ config('app.url') }}</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>