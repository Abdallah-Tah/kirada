# Fix plan — workflow timeline review findings

Review of commits `6572967` + `f9e88b7` (workflow timeline redesign in
`resources/views/welcome.blade.php` + deletion of old isometric CSS from
`resources/css/app.css`). 8 verified findings, ranked. Implement in the order
below; steps 1–6 are correctness/UX fixes, steps 7–8 are structural cleanups
that partially subsume the earlier point fixes — read the whole plan first,
because doing step 7/8 properly makes some of the small fixes land in the moved
code rather than the inline block.

## Context for the implementer

- Section: `<section id="workflow" ... x-data="kiradaWorkflow()">` at
  `resources/views/welcome.blade.php:399`. Inline `<style>` starts ~line 642,
  inline `<script>` defining `kiradaWorkflow()` ~line 770.
- The project already has a scroll-reveal system in `resources/js/app.js`:
  `initScrollReveal()` (line 26), classes `.kirada-reveal` / `.is-visible`,
  gated behind the JS-added `.kirada-motion` html class (line 17), with
  `prefers-reduced-motion` handling and a `livewire:navigated` re-init hook.
- Design tokens: `--color-kirada-ocean: #0EA5E9`, `--color-kirada-navy`,
  `--color-kirada-green` defined in `resources/css/app.css` `@theme`; the rest
  of `welcome.blade.php` uses `text-kirada-ocean` etc.
- Nav links use `wire:navigate` (Livewire SPA navigation), so inline scripts
  re-evaluate on revisit and Alpine re-inits `x-data`.
- Build: Vite; `@vite(['resources/css/app.css', ...])` in the head partial.

## Fixes (ranked)

### 1. No-JS / Alpine-failure leaves the section blank — `welcome.blade.php:694`
`.kirada-wf-card, .kirada-wf-card-v { opacity:0; transform:translateY(20px); }`
is unconditional. If Alpine never inits, nothing restores visibility.
**Fix:** gate the hidden initial state behind `.kirada-motion` (the existing
convention from app.js:17), e.g.
`.kirada-motion .kirada-wf-card, .kirada-motion .kirada-wf-card-v { opacity:0; ... }`.
Same for the `[data-line]`/`[data-comet]` initial hiding done in JS `prime()` —
without JS the static gradient line should just show. Keep the
reduced-motion override.

### 2. Mobile gradient broken — `welcome.blade.php:422` + `:675`
`<linearGradient id="wfGrad" gradientUnits="userSpaceOnUse" x1=0 x2=1160>` is
defined only inside the desktop SVG (display:none on mobile), but CSS
`.wf-progress { stroke:url(#wfGrad) }` applies to the mobile SVG too. On
mobile the vertical path at x=24 samples ~2% of a horizontal gradient
(near-solid #2563EB in Chrome) and Firefox may fail to resolve the paint
server from a display:none subtree at all.
**Fix:** give the mobile SVG its own `<defs>` with a vertical gradient
(`id="wfGradV"`, `x1=0 y1=20 x2=0 y2=900` userSpaceOnUse, same stops) and a
scoped rule `.kirada-wf-line-svg-v .wf-progress { stroke:url(#wfGradV); }`.

### 3. Comet animates forever off-screen — `welcome.blade.php:847`
`p.animate([...], { iterations:Infinity })` on a `drop-shadow`-filtered SVG
stroke; observer disconnects on first entry, animation reference discarded.
**Fix:** store the returned `Animation` objects; add a second
IntersectionObserver (or reuse one with an else-branch instead of
disconnecting) that `pause()`s comets when the section leaves the viewport and
`play()`s on re-entry. Alternatively cap iterations (e.g. 3–4 loops then fade
the comet out) — simpler and acceptable.

### 4. Resize listener / global timer leak — `welcome.blade.php:875`
`window.addEventListener('resize', ...)` in `init()` with no cleanup, plus
global `window._kwfTimer`; every wire:navigate revisit stacks a listener
holding detached DOM.
**Fix:** store the handler on the component (`this._onResize = ...`), add an
Alpine `destroy() { window.removeEventListener('resize', this._onResize); }`,
and make the debounce timer a local/instance variable, not `window._kwfTimer`.
(If the script moves to app.js per step 7, module scope handles the timer.)

### 5. Screen readers hear every step twice — `welcome.blade.php:420` + `:444`
Neither `.kirada-wf-desktop` nor `.kirada-wf-mobile` has `aria-hidden`, so AT
reads the visible cards (h3+p ×7) AND the sr-only `<ol>` (×7). The old design
had `aria-hidden="true"` on the visual stage.
**Fix (pick one):** either add `aria-hidden="true"` to both card containers
and keep the sr-only list, or — better — delete the sr-only list and make the
cards themselves the accessible content (wrap the card row in an `<ol>`
with `<li>` cards). The second option also fixes heading-level noise (7 `<h3>`s
per viewport is heavy; consider demoting to `<p class="font-semibold">`).

### 6. Dead old-design code in app.js — `resources/js/app.js:358–395`
`initWorkflowStage()`, `workflowStageObserver`, and their
DOMContentLoaded/`livewire:navigated` registrations target the deleted
`.kirada-iso-stage`.
**Fix:** delete the function, the module variable, and the two listener
registrations (lines ~358–395). Grep `kirada-iso` afterward to confirm zero
remaining references in the repo.

### 7. Inline `<style>`/`<script>` bypass the asset pipeline — `welcome.blade.php:642`, `:770`
~250 lines CSS + ~115 lines JS shipped in every HTML response; the old version
lived in app.css. Also hardcodes `#2563EB` (10+ times) — not a kirada token.
**Fix:**
- Move the CSS to `resources/css/app.css` under a labelled
  `Kirada Workflow — timeline v2` block (where the old block was removed).
- Replace hardcoded hex with the design tokens: use
  `var(--color-kirada-ocean)` for the accent (or add a
  `--color-kirada-blue: #2563EB` token to `@theme` if the darker blue is an
  intentional design choice — ask the user only if the visual diff matters;
  default: add the token so the current look is preserved but named).
- Move `kiradaWorkflow()` into `resources/js/app.js` and register it via
  `Alpine.data('kiradaWorkflow', ...)` inside an `alpine:init` listener
  (check how Flux/Livewire exposes Alpine — `document.addEventListener('alpine:init', ...)`
  works with Livewire v3 bundled Alpine). This removes the CSP/global-name
  fragility and the script-re-eval issue on wire:navigate.
- Where reasonable, reuse `initScrollReveal()`'s `.kirada-reveal`/`.is-visible`
  classes for the card entrances instead of bespoke WAAPI node animations —
  the line-draw + comet can stay custom, the card stagger doesn't need to be.

### 8. Duplicated desktop/mobile card trees — `welcome.blade.php:427` + `:451`
Two full `@foreach` loops render the same 7 cards with `-v` suffixed classes;
`.kirada-wf-focal` and `.kirada-wf-focal-v` are byte-identical CSS rules.
**Fix:** render ONE loop of cards; keep the two positioning SVGs if needed
(they're cheap), but switch card layout via media queries on a single class
set (`flex-direction`, `text-align`, icon sizing at `max-width:767px`).
Delete the `-v` class hierarchy, merge the focal rules, drop the unused
`data-option` attributes, and simplify the JS `getActive()`/`currentOpt`
machinery accordingly (with one card tree, `prime`/`play` no longer need the
active-container switch — only the line SVGs differ per breakpoint).

## Verification

- `npm run build` passes; no `kirada-iso` or `initWorkflowStage` references
  remain (`grep -rn "kirada-iso\|initWorkflowStage" resources/`).
- Manual: load `/` at desktop and mobile widths — line draws, cards stagger in,
  comet loops while visible and pauses off-screen; mobile progress line shows
  the vertical gradient.
- Disable JS (DevTools → Command Menu → "Disable JavaScript") — all 7 cards
  visible.
- Resize across 768px repeatedly — no console errors, no stacked animations.
- prefers-reduced-motion emulation — everything visible, no motion.
- Screen-reader spot check: each step announced once.

## Notes

- Do not commit until the user reviews; work on a branch if committing.
- Verified findings only; one candidate (getTotalLength-on-hidden-SVG breaking
  animations after breakpoint resizes) was investigated and REFUTED — every
  `play()` path re-primes on a visible container first. Don't "fix" that.
