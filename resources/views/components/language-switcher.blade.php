@php
    $currentLocale = app()->getLocale();
    $languages = [
        'en' => ['label' => 'English', 'code' => 'EN'],
        'fr' => ['label' => 'Français', 'code' => 'FR'],
        'ar' => ['label' => 'العربية', 'code' => 'AR'],
        'so' => ['label' => 'Soomaali', 'code' => 'SO'],
        'am' => ['label' => 'አማርኛ', 'code' => 'AM'],
    ];
    $current = $languages[$currentLocale] ?? $languages['en'];
@endphp

<flux:dropdown position="bottom" align="end">
    <flux:button variant="ghost" class="gap-2 text-kirada-navy hover:text-kirada-ocean">
        <span class="rounded-md bg-kirada-soft px-1.5 py-0.5 text-xs font-semibold text-kirada-ocean">{{ $current['code'] }}</span>
        <span class="hidden sm:inline text-sm">{{ $current['label'] }}</span>
        <flux:icon.chevron-down class="size-4" />
    </flux:button>

    <flux:menu>
        @foreach($languages as $code => $lang)
            <flux:menu.item
                :href="route('language.switch', ['locale' => $code])"
                class="{{ $code === $currentLocale ? 'font-bold text-kirada-green' : '' }}"
            >
                <span class="mr-2 rounded-md bg-kirada-soft px-1.5 py-0.5 text-xs font-semibold text-kirada-ocean">{{ $lang['code'] }}</span>
                {{ $lang['label'] }}
                @if($code === $currentLocale)
                    <flux:icon.check class="size-4 ml-auto text-kirada-green" />
                @endif
            </flux:menu.item>
        @endforeach
    </flux:menu>
</flux:dropdown>
