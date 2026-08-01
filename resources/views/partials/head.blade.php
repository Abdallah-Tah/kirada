<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
<meta name="csrf-token" content="{{ csrf_token() }}" />

{{-- Apply the stored appearance before first paint. Flux then promotes this
     bootstrap object to its reactive Alpine store when its scripts load. --}}
@fluxAppearance

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

{{-- PWA --}}
<link rel="manifest" href="/manifest.json?v=kirada-approved-20260627">
<meta name="theme-color" content="#0B84F3">
<meta name="application-name" content="Kirada">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="Kirada">
<meta name="mobile-web-app-capable" content="yes">

{{-- Icons --}}
<link rel="icon" href="/icons/favicon-32.png?v=kirada-approved-20260627" sizes="32x32" type="image/png">
<link rel="shortcut icon" href="/favicon.ico?v=kirada-approved-20260627">
<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png?v=kirada-approved-20260627">
<link rel="icon" href="/icons/icon-192.png?v=kirada-approved-20260627" sizes="192x192" type="image/png">

{{-- Service Worker Registration --}}
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js')
            .catch((err) => console.warn('[PWA] Service Worker registration failed:', err));
    });
}
</script>

@fonts

{{-- Mark JS as available before CSS paints, so scroll-reveal's hidden state
     only applies when JS can play it back (no blank page if JS is disabled). --}}
<script>document.documentElement.classList.add('kirada-motion');</script>
<script>
window.KIRADA_GOOGLE_MAPS_API_KEY = @js(env('VITE_GOOGLE_MAPS_API_KEY'));
</script>

@vite(['resources/css/app.css', 'resources/js/app.js'])
<style>
    :root.dark { color-scheme: dark; }
    :root { color-scheme: light; }
    [x-cloak] { display: none !important; }
</style>
