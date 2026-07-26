<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />

{{-- Appearance. Must run before the first paint, or the stored theme arrives
     one frame late and the page flashes light before going dark. --}}
<script>
window.Flux = {
    _media: window.matchMedia('(prefers-color-scheme: dark)'),
    applyAppearance: function (appearance) {
        var dark = appearance === 'dark' || (appearance === 'system' && this._media.matches);
        document.documentElement.classList.toggle('dark', dark);
    },
    get appearance() {
        return window.localStorage.getItem('flux.appearance') || 'system';
    },
    set appearance(value) {
        var next = ['light', 'dark', 'system'].includes(value) ? value : 'system';
        window.localStorage.setItem('flux.appearance', next);
        this.applyAppearance(next);
        // Alpine can't observe a plain window property, so the toggle listens
        // for this instead of polling.
        window.dispatchEvent(new CustomEvent('flux-appearance-changed', { detail: next }));
    },
};

window.Flux.applyAppearance(window.Flux.appearance);
window.Flux._media.addEventListener('change', function () {
    if (window.Flux.appearance === 'system') {
        window.Flux.applyAppearance('system');
    }
});
</script>

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

{{-- PWA --}}
<link rel="manifest" href="/manifest.json?v=kirada-approved-20260627">
<meta name="theme-color" content="#0EA5E9">
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
            .then((reg) => console.log('[PWA] Service Worker registered:', reg.scope))
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
