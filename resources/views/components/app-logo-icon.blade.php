<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" {{ $attributes }}>
    <defs>
        <linearGradient id="kirada-grad" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#0EA5E9"/>
            <stop offset="60%" stop-color="#10B981"/>
            <stop offset="100%" stop-color="#67D3E6"/>
        </linearGradient>
    </defs>

    <!-- House roof (angled line forming the roof) -->
    <path d="M6 22 L24 6 L42 22" fill="none" stroke="url(#kirada-grad)" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>

    <!-- Key bow (circle on the left = house base + key handle) -->
    <circle cx="14" cy="34" r="6" fill="none" stroke="url(#kirada-grad)" stroke-width="3"/>

    <!-- Key stem (house floor = key blade) -->
    <path d="M20 34 L40 34" fill="none" stroke="url(#kirada-grad)" stroke-width="3" stroke-linecap="round"/>
    <!-- Key teeth -->
    <path d="M36 34 L36 38 M40 34 L40 38" fill="none" stroke="url(#kirada-grad)" stroke-width="3" stroke-linecap="round"/>

    <!-- House left wall -->
    <path d="M10 22 L10 30" fill="none" stroke="url(#kirada-grad)" stroke-width="3" stroke-linecap="round"/>
    <!-- House right wall -->
    <path d="M38 22 L38 30" fill="none" stroke="url(#kirada-grad)" stroke-width="3" stroke-linecap="round"/>

    <!-- Window (4 panes) -->
    <rect x="22" y="25" width="3.5" height="3.5" fill="url(#kirada-grad)" rx="0.5"/>
    <rect x="27" y="25" width="3.5" height="3.5" fill="url(#kirada-grad)" rx="0.5"/>
    <rect x="22" y="29.5" width="3.5" height="3.5" fill="url(#kirada-grad)" rx="0.5"/>
    <rect x="27" y="29.5" width="3.5" height="3.5" fill="url(#kirada-grad)" rx="0.5"/>

    <!-- Red star accent (left of window) -->
    <path d="M17 27 L17.8 28.8 L19.8 29 L18.3 30.3 L18.8 32.3 L17 31.3 L15.2 32.3 L15.7 30.3 L14.2 29 L16.2 28.8 Z" fill="#EF4444"/>
</svg>