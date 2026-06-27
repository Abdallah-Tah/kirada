/**
 * Kirada motion runtime.
 *
 * A tiny, dependency-free scroll-reveal that follows Apple's "motion with
 * purpose" principle: content gently rises into place as it enters the
 * viewport, then never animates again. Motion-sensitive users (and browsers
 * without IntersectionObserver) get the content instantly with no movement.
 */

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
