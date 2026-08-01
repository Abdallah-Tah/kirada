import { Passkeys } from '@laravel/passkeys';

// Keep passkey failures JSON-shaped even when Laravel returns a validation or
// authentication error. The browser client can then show the real message
// instead of trying to parse an HTML error page.
Passkeys.configure({
    fetch: {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
    },
});

window.Passkeys = Passkeys;
window.dispatchEvent(new CustomEvent('passkeys:ready'));
