import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Underline from '@tiptap/extension-underline';
import TextAlign from '@tiptap/extension-text-align';

/**
 * Kirada motion runtime.
 *
 * A tiny, dependency-free scroll-reveal that follows Apple's "motion with
 * purpose" principle: content gently rises into place as it enters the
 * viewport, then never animates again. Motion-sensitive users (and browsers
 * without IntersectionObserver) get the content instantly with no movement.
 */

// Belt-and-suspenders: the inline <head> script sets this first (before CSS
// paints); re-assert it here in case a page renders without that partial.
document.documentElement.classList.add('kirada-motion');

const prefersReducedMotion = () =>
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function revealAll(elements) {
    elements.forEach((el) => el.classList.add('is-visible'));
}

function initScrollReveal() {
    const elements = document.querySelectorAll('.kirada-reveal:not(.is-visible)');

    if (elements.length === 0) {
        return;
    }

    if (prefersReducedMotion() || !('IntersectionObserver' in window)) {
        revealAll(elements);
        return;
    }

    const observer = new IntersectionObserver(
        (entries, obs) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    obs.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -60px 0px' }
    );

    elements.forEach((el) => observer.observe(el));
}

// Run on first paint and after every Livewire `wire:navigate` page swap so
// the SPA-style navigation keeps its entrance motion.
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initScrollReveal);
} else {
    initScrollReveal();
}

document.addEventListener('livewire:navigated', initScrollReveal);

const googleMapsApiKey = window.KIRADA_GOOGLE_MAPS_API_KEY || import.meta.env.VITE_GOOGLE_MAPS_API_KEY;
let googleMapsPromise;

function loadGoogleMaps() {
    if (!googleMapsApiKey) {
        console.warn('[Kirada] Google address autocomplete disabled: missing VITE_GOOGLE_MAPS_API_KEY.');
        return Promise.resolve(false);
    }

    if (window.google?.maps?.places) {
        return Promise.resolve(true);
    }

    if (googleMapsPromise) {
        return googleMapsPromise;
    }

    googleMapsPromise = new Promise((resolve, reject) => {
        const script = document.createElement('script');
        const params = new URLSearchParams({
            key: googleMapsApiKey,
            libraries: 'places',
            loading: 'async',
        });

        script.src = `https://maps.googleapis.com/maps/api/js?${params.toString()}`;
        script.async = true;
        script.defer = true;
        script.addEventListener('load', () => resolve(true), { once: true });
        script.addEventListener('error', reject, { once: true });
        document.head.appendChild(script);
    });

    return googleMapsPromise;
}

function addressPart(place, type, format = 'long_name') {
    return place.address_components?.find((component) => component.types.includes(type))?.[format] ?? '';
}

function normalizePlace(place) {
    const streetNumber = addressPart(place, 'street_number');
    const route = addressPart(place, 'route');
    const addressLine = [streetNumber, route].filter(Boolean).join(' ') || place.formatted_address || '';
    const city = addressPart(place, 'locality')
        || addressPart(place, 'postal_town')
        || addressPart(place, 'administrative_area_level_2');

    return {
        address_line_1: addressLine,
        city,
        region: addressPart(place, 'administrative_area_level_1'),
        postal_code: addressPart(place, 'postal_code'),
        country_code: addressPart(place, 'country', 'short_name'),
        latitude: place.geometry?.location?.lat(),
        longitude: place.geometry?.location?.lng(),
    };
}

function initGoogleAddressAutocomplete() {
    const inputs = document.querySelectorAll('[data-google-address]:not([data-google-address-ready])');

    if (inputs.length === 0) {
        return;
    }

    loadGoogleMaps()
        .then((loaded) => {
            if (!loaded) {
                return;
            }

            inputs.forEach((input) => {
                if (input.dataset.googleAddressReady) {
                    return;
                }

                input.dataset.googleAddressReady = 'true';
                const livewireMethod = input.dataset.googleAddressMethod || 'applyGoogleAddress';
                const nextSelector = input.dataset.googleAddressNext;

                const autocomplete = new window.google.maps.places.Autocomplete(input, {
                    fields: ['address_components', 'formatted_address', 'geometry'],
                    types: ['address'],
                });

                autocomplete.addListener('place_changed', () => {
                    const place = autocomplete.getPlace();
                    const componentId = input.closest('[wire\\:id]')?.getAttribute('wire:id');
                    const component = componentId ? window.Livewire?.find(componentId) : null;

                    if (component && place) {
                        component.call(livewireMethod, normalizePlace(place));
                    }

                    if (nextSelector) {
                        const nextInput = input.closest('form')?.querySelector(nextSelector);

                        if (nextInput instanceof HTMLElement) {
                            window.requestAnimationFrame(() => nextInput.focus());
                        }
                    }
                });
            });
        })
        .catch((error) => console.warn('[Kirada] Google address autocomplete failed to load.', error));
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGoogleAddressAutocomplete);
} else {
    initGoogleAddressAutocomplete();
}

document.addEventListener('livewire:navigated', initGoogleAddressAutocomplete);

let pendingConfirmedAction = null;
let confirmationReturnFocus = null;
let confirmationAddedBodyLock = false;
const confirmedForms = new WeakSet();

const confirmationVariants = {
    danger: {
        icon: ['bg-red-50', 'text-red-600', 'dark:bg-red-950/60', 'dark:text-red-300'],
        button: ['bg-red-600', 'hover:bg-red-700', 'focus:ring-red-500'],
    },
    primary: {
        icon: ['bg-sky-50', 'text-sky-700', 'dark:bg-sky-950/60', 'dark:text-sky-300'],
        button: ['bg-kirada-ocean', 'hover:bg-kirada-navy', 'focus:ring-kirada-ocean'],
    },
    warning: {
        icon: ['bg-amber-50', 'text-amber-600', 'dark:bg-amber-950/60', 'dark:text-amber-300'],
        button: ['bg-amber-600', 'hover:bg-amber-700', 'focus:ring-amber-500'],
    },
};

const confirmationVariantClasses = {
    icon: Object.values(confirmationVariants).flatMap((variant) => variant.icon),
    button: Object.values(confirmationVariants).flatMap((variant) => variant.button),
};

function splitWireArguments(argsString) {
    const args = [];
    let current = '';
    let quote = null;
    let escaped = false;

    for (const char of argsString) {
        if (escaped) {
            current += char;
            escaped = false;
            continue;
        }

        if (char === '\\') {
            current += char;
            escaped = true;
            continue;
        }

        if (quote) {
            current += char;

            if (char === quote) {
                quote = null;
            }

            continue;
        }

        if (char === '\'' || char === '"') {
            current += char;
            quote = char;
            continue;
        }

        if (char === ',') {
            args.push(current.trim());
            current = '';
            continue;
        }

        current += char;
    }

    if (current.trim() !== '') {
        args.push(current.trim());
    }

    return args;
}

function parseWireValue(value) {
    if ((value.startsWith('\'') && value.endsWith('\'')) || (value.startsWith('"') && value.endsWith('"'))) {
        return value.slice(1, -1).replace(/\\(['"\\])/g, '$1');
    }

    if (value === 'true') {
        return true;
    }

    if (value === 'false') {
        return false;
    }

    if (value === 'null') {
        return null;
    }

    if (/^-?\d+(\.\d+)?$/.test(value)) {
        return Number(value);
    }

    return value;
}

function parseWireClick(expression) {
    const match = expression.trim().match(/^([\w$]+)(?:\((.*)\))?$/);

    if (!match) {
        return null;
    }

    const argsString = match[2]?.trim();

    return {
        method: match[1],
        args: argsString ? splitWireArguments(argsString).map(parseWireValue) : [],
    };
}

function closeConfirmationModal() {
    const modal = document.getElementById('kirada-confirmation-modal');

    if (!modal) {
        return;
    }

    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.setAttribute('aria-hidden', 'true');
    pendingConfirmedAction = null;
    if (confirmationAddedBodyLock) {
        document.body.classList.remove('overflow-hidden');
    }

    if (confirmationReturnFocus?.isConnected) {
        confirmationReturnFocus.focus();
    }

    confirmationReturnFocus = null;
    confirmationAddedBodyLock = false;
}

function openConfirmationModal({ message, confirmText, title, variant = 'danger', action, trigger = null }) {
    const modal = document.getElementById('kirada-confirmation-modal');
    const messageEl = document.getElementById('kirada-confirmation-message');
    const titleEl = document.getElementById('kirada-confirmation-title');
    const iconEl = modal?.querySelector('[data-confirm-icon]');
    const continueButton = modal?.querySelector('[data-confirm-continue]');

    if (!modal || !messageEl || !titleEl || !iconEl || !continueButton) {
        return false;
    }

    const selectedVariant = confirmationVariants[variant] || confirmationVariants.warning;

    pendingConfirmedAction = action;
    confirmationReturnFocus = trigger || document.activeElement;
    titleEl.textContent = title || modal.dataset.defaultTitle;
    messageEl.textContent = message || modal.dataset.defaultMessage;
    continueButton.textContent = confirmText || modal.dataset.defaultConfirm;
    modal.dataset.variant = confirmationVariants[variant] ? variant : 'warning';
    iconEl.classList.remove(...confirmationVariantClasses.icon);
    iconEl.classList.add(...selectedVariant.icon);
    continueButton.classList.remove(...confirmationVariantClasses.button);
    continueButton.classList.add(...selectedVariant.button);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.setAttribute('aria-hidden', 'false');
    confirmationAddedBodyLock = !document.body.classList.contains('overflow-hidden');
    document.body.classList.add('overflow-hidden');
    continueButton.focus();

    return true;
}

// Capture at window level so consequential Livewire actions are intercepted
// before Livewire's delegated document listeners can execute them.
window.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-confirm]');

    if (!trigger) {
        return;
    }

    const wireClick = trigger.getAttribute('wire:click');
    const componentId = trigger.closest('[wire\\:id]')?.getAttribute('wire:id');
    const component = componentId ? window.Livewire?.find(componentId) : null;
    const parsed = wireClick ? parseWireClick(wireClick) : null;
    const link = trigger.closest('a[href]');
    let action = null;

    if (component && parsed) {
        action = () => component.call(parsed.method, ...parsed.args);
    } else if (link) {
        action = () => window.location.assign(link.href);
    } else {
        // Form submissions are handled by the submit listener below so native
        // validation still runs before the confirmation dialog is opened.
        return;
    }

    event.preventDefault();
    event.stopImmediatePropagation();

    const triggerText = trigger.textContent.trim();
    const confirmText = trigger.getAttribute('data-confirm-button')
        || (triggerText.length > 0 && triggerText.length <= 24 ? triggerText : 'Confirm');

    openConfirmationModal({
        message: trigger.getAttribute('data-confirm'),
        confirmText,
        title: trigger.getAttribute('data-confirm-title'),
        variant: trigger.getAttribute('data-confirm-variant') || 'danger',
        action,
        trigger,
    });
}, true);

window.addEventListener('submit', (event) => {
    const form = event.target.closest('form[data-confirm]');

    if (!form) {
        return;
    }

    if (confirmedForms.has(form)) {
        confirmedForms.delete(form);

        return;
    }

    event.preventDefault();
    event.stopImmediatePropagation();

    const submitter = event.submitter;
    const submitterText = submitter?.textContent?.trim() || '';
    const confirmText = form.getAttribute('data-confirm-button')
        || (submitterText.length > 0 && submitterText.length <= 32 ? submitterText : null);

    openConfirmationModal({
        message: form.getAttribute('data-confirm'),
        confirmText,
        title: form.getAttribute('data-confirm-title'),
        variant: form.getAttribute('data-confirm-variant') || 'danger',
        trigger: submitter || form,
        action: () => {
            confirmedForms.add(form);

            if (submitter) {
                form.requestSubmit(submitter);
            } else {
                form.requestSubmit();
            }
        },
    });
}, true);

document.addEventListener('click', (event) => {
    if (event.target.closest('[data-confirm-cancel]')) {
        closeConfirmationModal();
    }

    if (event.target.closest('[data-confirm-continue]') && pendingConfirmedAction) {
        const action = pendingConfirmedAction;

        closeConfirmationModal();
        action();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeConfirmationModal();
    }
});

// ── Rich paragraph editor (Tiptap) ────────────────────────────────────────────
// Full WYSIWYG editor for contract paragraphs. wire:ignore on the host element
// prevents Livewire from clobbering the ProseMirror DOM; changes sync back via
// $wire.set(). The editor outputs full HTML (with <p> wrapper); buildBody() in
// Show.php detects block-level output and uses it as-is.

document.addEventListener('alpine:init', () => {
    // ── Workflow timeline ─────────────────────────────────────────────────────
    Alpine.data('kiradaWorkflow', () => ({
        _cometAnims: [],
        _exitObserver: null,

        init() {
            const root = this.$el;
            const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            let played = false;

            if (reduce) {
                root.querySelectorAll('[data-node]').forEach(n => {
                    n.style.opacity = 1; n.style.transform = 'none';
                });
                root.querySelectorAll('[data-line]').forEach(p => { p.style.strokeDashoffset = 0; });
                return;
            }

            // Prep the visible line/comet paths for their entrance animation.
            const prime = () => {
                root.querySelectorAll('[data-line],[data-comet]').forEach(p => {
                    p.getAnimations?.().forEach(a => a.cancel());
                });
                root.querySelectorAll('[data-line]').forEach(p => {
                    const svg = p.closest('svg');
                    if (!svg || getComputedStyle(svg).display === 'none') return;
                    const L = p.getTotalLength?.() || 0;
                    p.style.strokeDasharray = L;
                    p.style.strokeDashoffset = L;
                });
                root.querySelectorAll('[data-comet]').forEach(p => {
                    const svg = p.closest('svg');
                    if (!svg || getComputedStyle(svg).display === 'none') return;
                    const L = p.getTotalLength?.() || 0;
                    p.dataset.len = L;
                    p.style.strokeDasharray = '20 ' + L;
                    p.style.strokeDashoffset = L;
                    p.style.opacity = 0;
                });
            };

            const play = () => {
                const isMobile = !window.matchMedia('(min-width:768px)').matches;
                const lineDur = 1400;

                // Draw the visible timeline line.
                root.querySelectorAll('[data-line]').forEach(p => {
                    const svg = p.closest('svg');
                    if (!svg || getComputedStyle(svg).display === 'none') return;
                    const L = p.getTotalLength?.() || 0;
                    p.animate(
                        [{ strokeDashoffset: L }, { strokeDashoffset: 0 }],
                        { duration: lineDur, easing: 'cubic-bezier(0.65,0,0.35,1)', fill: 'forwards' }
                    ).onfinish = () => { p.style.strokeDashoffset = 0; };
                });

                // Stagger cards in.
                const nodes = root.querySelectorAll('[data-node]');
                const stepDelay = lineDur / nodes.length;
                nodes.forEach((n, i) => {
                    const offset = isMobile ? 'translateX(-20px)' : 'translateY(20px)';
                    n.animate(
                        [{ opacity: 0, transform: offset }, { opacity: 1, transform: 'none' }],
                        { duration: 600, delay: stepDelay * i + 80, easing: 'cubic-bezier(0.4,0,0.2,1)', fill: 'forwards' }
                    ).onfinish = () => { n.style.opacity = 1; n.style.transform = 'none'; };
                });

                // Comet loop — starts after the line draw; pauses off-screen.
                setTimeout(() => {
                    this._cometAnims = [];
                    root.querySelectorAll('[data-comet]').forEach(p => {
                        const svg = p.closest('svg');
                        if (!svg || getComputedStyle(svg).display === 'none') return;
                        const L = parseFloat(p.dataset.len) || 0;
                        if (!L) return;
                        p.style.opacity = 1;
                        this._cometAnims.push(p.animate(
                            [{ strokeDashoffset: L }, { strokeDashoffset: 0 }],
                            { duration: 3000, iterations: Infinity, easing: 'linear' }
                        ));
                    });
                    if (this._cometAnims.length) {
                        this._exitObserver = new IntersectionObserver((entries) => {
                            entries.forEach(e =>
                                this._cometAnims.forEach(a => e.isIntersecting ? a.play() : a.pause())
                            );
                        }, { threshold: 0.05 });
                        this._exitObserver.observe(root);
                    }
                }, lineDur + 200);
            };

            prime();

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting && !played) {
                        played = true;
                        observer.disconnect();
                        play();
                    }
                });
            }, { threshold: 0.15 });
            observer.observe(root);
        },

        destroy() {
            if (this._exitObserver) this._exitObserver.disconnect();
            this._cometAnims.forEach(a => a.cancel());
        },
    }));

    // ── Rich text editor ──────────────────────────────────────────────────────
    Alpine.data('richEditor', (initial, wirePath) => {
        let editor = null;

        return {
            // ── Toolbar active-state flags ────────────────────────
            fBold: false, fItalic: false, fUnderline: false, fStrike: false,
            fLeft: true, fCenter: false, fRight: false, fJustify: false,
            fBullet: false, fOrdered: false,
            canUndo: false, canRedo: false,

            // ── Lifecycle ─────────────────────────────────────────
            init() {
                const self = this;

                editor = new Editor({
                    element: this.$refs.editorEl,
                    extensions: [
                        StarterKit.configure({
                            heading: false,
                            codeBlock: false,
                            horizontalRule: false,
                            code: false,
                            blockquote: false,
                        }),
                        Underline,
                        TextAlign.configure({ types: ['paragraph', 'listItem'] }),
                    ],
                    content: initial ?? '',
                    editorProps: {
                        attributes: { class: 'kirada-rich-editor' },
                    },
                    onUpdate({ editor: ed }) {
                        self.$wire.set(wirePath, ed.getHTML());
                        self._sync(ed);
                    },
                    onSelectionUpdate({ editor: ed }) { self._sync(ed); },
                    onFocus({ editor: ed })           { self._sync(ed); },
                });

                this._sync(editor);
            },

            destroy() { editor?.destroy(); editor = null; },

            // ── Internal state sync ───────────────────────────────
            _sync(ed) {
                this.fBold    = ed.isActive('bold');
                this.fItalic  = ed.isActive('italic');
                this.fUnderline = ed.isActive('underline');
                this.fStrike  = ed.isActive('strike');
                this.fCenter  = ed.isActive({ textAlign: 'center' });
                this.fRight   = ed.isActive({ textAlign: 'right' });
                this.fJustify = ed.isActive({ textAlign: 'justify' });
                this.fLeft    = !this.fCenter && !this.fRight && !this.fJustify;
                this.fBullet  = ed.isActive('bulletList');
                this.fOrdered = ed.isActive('orderedList');
                this.canUndo  = ed.can().undo();
                this.canRedo  = ed.can().redo();
            },

            // ── Toolbar commands ──────────────────────────────────
            _run(fn) {
                if (!editor) return;
                fn(editor.chain().focus());
                this._sync(editor);
            },

            toggleBold()      { this._run(c => c.toggleBold().run()); },
            toggleItalic()    { this._run(c => c.toggleItalic().run()); },
            toggleUnderline() { this._run(c => c.toggleUnderline().run()); },
            toggleStrike()    { this._run(c => c.toggleStrike().run()); },
            alignLeft()       { this._run(c => c.setTextAlign('left').run()); },
            alignCenter()     { this._run(c => c.setTextAlign('center').run()); },
            alignRight()      { this._run(c => c.setTextAlign('right').run()); },
            alignJustify()    { this._run(c => c.setTextAlign('justify').run()); },
            toggleBullet()    { this._run(c => c.toggleBulletList().run()); },
            toggleOrdered()   { this._run(c => c.toggleOrderedList().run()); },
            undo()            { this._run(c => c.undo().run()); },
            redo()            { this._run(c => c.redo().run()); },
            clearFormat()     { this._run(c => c.clearNodes().unsetAllMarks().run()); },
        };
    });
});
