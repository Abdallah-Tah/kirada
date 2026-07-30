<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if (app()->getLocale() === 'ar') dir="rtl" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>@yield('code') · Kirada</title>
    <style>
        :root {
            color-scheme: light;
            --page: #f5f7fa;
            --surface: rgba(255, 255, 255, .94);
            --text: #122033;
            --muted: #607086;
            --line: #dce4ec;
            --navy: #071426;
            --blue: #0797c8;
            --green: #10a879;
            --shadow: 0 32px 90px rgba(15, 35, 55, .14);
        }
        @media (prefers-color-scheme: dark) {
            :root {
                color-scheme: dark;
                --page: #050b14;
                --surface: rgba(9, 20, 36, .94);
                --text: #f2f6fa;
                --muted: #a2b1c3;
                --line: #203149;
                --navy: #020711;
                --shadow: 0 32px 90px rgba(0, 0, 0, .42);
            }
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 12% 8%, rgba(7, 151, 200, .13), transparent 30rem),
                radial-gradient(circle at 88% 92%, rgba(16, 168, 121, .11), transparent 28rem),
                var(--page);
        }
        .shell { min-height: 100vh; display: grid; place-items: center; padding: 1.5rem; }
        .card {
            width: min(100%, 47rem);
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 2rem;
            background: var(--surface);
            box-shadow: var(--shadow);
            backdrop-filter: blur(18px);
        }
        .accent { height: .35rem; background: linear-gradient(90deg, var(--blue), var(--green)); }
        .content { padding: clamp(1.6rem, 5vw, 3.4rem); }
        .brand { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
        .brand img { display: block; width: auto; height: 2.5rem; border-radius: .55rem; }
        .code {
            border: 1px solid rgba(7, 151, 200, .22);
            border-radius: 999px;
            background: rgba(7, 151, 200, .08);
            padding: .45rem .8rem;
            color: var(--blue);
            font-size: .78rem;
            font-weight: 800;
            letter-spacing: .12em;
        }
        .icon {
            display: grid;
            width: 4.5rem;
            height: 4.5rem;
            margin-top: 2.7rem;
            place-items: center;
            border: 1px solid rgba(7, 151, 200, .2);
            border-radius: 1.35rem;
            background: linear-gradient(145deg, rgba(7, 151, 200, .12), rgba(16, 168, 121, .08));
            color: var(--blue);
        }
        .icon svg { width: 2rem; height: 2rem; }
        h1 { margin: 1.5rem 0 .7rem; font-size: clamp(1.8rem, 5vw, 2.65rem); line-height: 1.1; letter-spacing: -.04em; }
        p { max-width: 38rem; margin: 0; color: var(--muted); font-size: 1rem; line-height: 1.7; }
        .actions { display: flex; flex-wrap: wrap; gap: .75rem; margin-top: 2rem; }
        .button {
            display: inline-flex;
            min-height: 2.9rem;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--line);
            border-radius: .9rem;
            padding: .72rem 1.1rem;
            color: var(--text);
            font-size: .9rem;
            font-weight: 750;
            text-decoration: none;
            transition: transform .18s ease, border-color .18s ease;
        }
        .button:hover { transform: translateY(-1px); border-color: var(--blue); }
        .button.primary { border-color: transparent; background: linear-gradient(135deg, var(--blue), #087da9); color: white; }
        .help { margin-top: 2.2rem; padding-top: 1.3rem; border-top: 1px solid var(--line); font-size: .82rem; }
    </style>
</head>
<body>
    <main class="shell">
        <section class="card" aria-labelledby="error-title">
            <div class="accent"></div>
            <div class="content">
                <div class="brand">
                    <a href="{{ route('home') }}" aria-label="{{ __('Kirada home') }}">
                        <img src="{{ asset('brand/kirada-logo.jpg') }}" alt="Kirada">
                    </a>
                    <span class="code">{{ __('ERROR') }} @yield('code')</span>
                </div>

                <div class="icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 2.6 17.2A2 2 0 0 0 4.3 20h15.4a2 2 0 0 0 1.7-2.8L13.7 3.9a2 2 0 0 0-3.4 0Z"/>
                    </svg>
                </div>

                <h1 id="error-title">@yield('title')</h1>
                <p>@yield('message')</p>

                <div class="actions">
                    <a class="button primary" href="{{ route('home') }}">{{ __('Return to home') }}</a>
                </div>

                <p class="help">{{ __('If the problem continues, contact Kirada support and include the error number shown above.') }}</p>
            </div>
        </section>
    </main>
</body>
</html>
