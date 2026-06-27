@php
    $currentLocale = app()->getLocale();
    $languages = [
        'en' => ['label' => 'English', 'flag' => '🇬🇧'],
        'fr' => ['label' => 'Français', 'flag' => '🇫🇷'],
        'ar' => ['label' => 'العربية', 'flag' => '🇩🇯'],
        'so' => ['label' => 'Soomaali', 'flag' => '🇸🇴'],
        'am' => ['label' => 'አማርኛ', 'flag' => '🇪🇹'],
    ];
    $current = $languages[$currentLocale] ?? $languages['en'];
@endphp

<flux:dropdown position="bottom" align="end">
    <flux:button variant="ghost" class="gap-2">
        <span class="text-base">{{ $current['flag'] }}</span>
        <span class="hidden sm:inline text-sm">{{ $current['label'] }}</span>
        <flux:icon.chevron-down class="size-4" />
    </flux:button>

    <flux:menu>
        @foreach($languages as $code => $lang)
            <flux:menu.item
                :href="route('language.switch', ['locale' => $code])"
                wire:navigate
                class="{{ $code === $currentLocale ? 'font-bold text-teal-600' : '' }}"
            >
                <span class="text-base mr-2">{{ $lang['flag'] }}</span>
                {{ $lang['label'] }}
                @if($code === $currentLocale)
                    <flux:icon.check class="size-4 ml-auto text-teal-500" />
                @endif
            </flux:menu.item>
        @endforeach
    </flux:menu>
</flux:dropdown>