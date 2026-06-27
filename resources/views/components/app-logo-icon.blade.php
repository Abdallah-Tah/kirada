<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" {{ $attributes }}>
    <!-- Kirada logo: house with key notch = rent management -->
    <defs>
        <linearGradient id="kirada-grad" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#0d9488"/>
            <stop offset="100%" stop-color="#0284c7"/>
        </linearGradient>
    </defs>
    <!-- House roof -->
    <path d="M24 4 L6 18 L6 42 L42 42 L42 18 Z" fill="url(#kirada-grad)"/>
    <!-- House body outline -->
    <path d="M24 6 L8 19 L8 40 L40 40 L40 19 Z" fill="#ffffff" fill-opacity="0.12"/>
    <!-- Key circle (rent symbol) -->
    <circle cx="24" cy="28" r="7" fill="none" stroke="#ffffff" stroke-width="2.5"/>
    <!-- Key stem -->
    <path d="M24 28 L24 36 M22 36 L26 36 M22 33 L25 33" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" fill="none"/>
    <!-- Door base line -->
    <path d="M8 40 L40 40" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" fill="none" opacity="0.6"/>
</svg>