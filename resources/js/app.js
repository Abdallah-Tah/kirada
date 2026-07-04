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
    pendingConfirmedAction = null;
}

function openConfirmationModal({ message, confirmText, action }) {
    const modal = document.getElementById('kirada-confirmation-modal');
    const messageEl = document.getElementById('kirada-confirmation-message');
    const continueButton = modal?.querySelector('[data-confirm-continue]');

    if (!modal || !messageEl || !continueButton) {
        return false;
    }

    pendingConfirmedAction = action;
    messageEl.textContent = message || 'Are you sure you want to continue?';
    continueButton.textContent = confirmText || 'Confirm';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    continueButton.focus();

    return true;
}

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-confirm]');

    if (!trigger) {
        return;
    }

    const wireClick = trigger.getAttribute('wire:click');
    const componentId = trigger.closest('[wire\\:id]')?.getAttribute('wire:id');
    const component = componentId ? window.Livewire?.find(componentId) : null;
    const parsed = wireClick ? parseWireClick(wireClick) : null;

    if (!component || !parsed) {
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
        action: () => component.call(parsed.method, ...parsed.args),
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

// ── Workflow Isometric Pipeline Animation ───────────────────────────────────
// Chatsheet-style: input icons scatter in from the left, dotted connector
// lines draw from each icon to the central 3D hub, step cards ascend on the
// right along a blue-tinted platform, data particles float above.
// Gated behind prefers-reduced-motion: no-preference.

function initWorkflowOrbit() {
    const stage = document.querySelector('.kirada-iso-stage');
    if (!stage) return;

    const inputs = stage.querySelectorAll('.kirada-iso-input-icon');
    const connector = stage.querySelector('.kirada-iso-connectors');
    const path = stage.querySelector('.kirada-iso-path');
    const steps = stage.querySelectorAll('.kirada-iso-step');
    const particles = stage.querySelectorAll('.kirada-iso-particle');
    const isMobile = window.matchMedia('(max-width: 767px)').matches;

    // Draw dotted connector lines from each input icon to the hub center
    if (!isMobile && connector && connector.tagName.toLowerCase() === 'svg') {
        const svgNs = 'http://www.w3.org/2000/svg';
        const stageW = stage.clientWidth;
        const stageH = stage.clientHeight;
        const hubX = stageW * 0.50; // hub at center
        const hubY = stageH * 0.50;
        const inputsContainer = stage.querySelector('.kirada-iso-inputs');
        const inputsRect = inputsContainer ? inputsContainer.getBoundingClientRect() : null;
        const stageRect = stage.getBoundingClientRect();

        inputs.forEach((icon) => {
            const iconRect = icon.getBoundingClientRect();
            const iconCx = iconRect.left - stageRect.left + iconRect.width / 2;
            const iconCy = iconRect.top - stageRect.top + iconRect.height / 2;

            // Convert to SVG viewBox coordinates
            const vbX = (iconCx / stageRect.width) * 1100;
            const vbY = (iconCy / stageRect.height) * 520;
            const line = document.createElementNS(svgNs, 'line');
            line.setAttribute('x1', vbX);
            line.setAttribute('y1', vbY);
            line.setAttribute('x2', 550);
            line.setAttribute('y2', 260);
            connector.appendChild(line);
        });
    }

    // Static fallback: show everything immediately
    if (prefersReducedMotion() || !('IntersectionObserver' in window)) {
        inputs.forEach((el) => el.classList.add('is-visible'));
        if (connector) connector.classList.add('is-visible');
        if (path) path.classList.add('is-visible');
        steps.forEach((el) => el.classList.add('is-visible'));
        particles.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    // Scroll-triggered staggered entrance
    const observer = new IntersectionObserver(
        (entries, obs) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                obs.unobserve(entry.target);

                // 1. Input icons scatter in from left (staggered)
                inputs.forEach((icon) => {
                    const delay = parseFloat(icon.style.transitionDelay) * 1000 || 0;
                    setTimeout(() => icon.classList.add('is-visible'), delay);
                });

                // 2. Connector lines fade in
                setTimeout(() => {
                    if (connector) connector.classList.add('is-visible');
                }, 400);

                // 3. Blue platform fades in
                setTimeout(() => {
                    if (path) path.classList.add('is-visible');
                }, 200);

                // 4. Step cards ascend (staggered)
                steps.forEach((step) => {
                    const delay = parseFloat(step.style.transitionDelay) * 1000 || 0;
                    setTimeout(() => step.classList.add('is-visible'), delay);
                });

                // 5. Data particles appear
                particles.forEach((particle, i) => {
                    setTimeout(() => particle.classList.add('is-visible'),
                        800 + i * 300);
                });
            });
        },
        { threshold: 0.20 }
    );

    observer.observe(stage);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initWorkflowOrbit);
} else {
    initWorkflowOrbit();
}

document.addEventListener('livewire:navigated', initWorkflowOrbit);

// ── Rich paragraph editor (Tiptap) ────────────────────────────────────────────
// Full WYSIWYG editor for contract paragraphs. wire:ignore on the host element
// prevents Livewire from clobbering the ProseMirror DOM; changes sync back via
// $wire.set(). The editor outputs full HTML (with <p> wrapper); buildBody() in
// Show.php detects block-level output and uses it as-is.

document.addEventListener('alpine:init', () => {
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
