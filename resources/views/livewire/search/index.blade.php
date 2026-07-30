<div>
    <div class="kirada-dashboard-hero kirada-reveal">
        <div class="kirada-dashboard-hero-content">
            <span class="kirada-dashboard-eyebrow">{{ __('Kirada workspace') }}</span>
            <h1>{{ __('Global Search') }}</h1>
            <p>{{ __('Find the records you are allowed to access across your entire workspace.') }}</p>

            <form method="GET" action="{{ route('search.index') }}" class="kirada-global-search-form">
                <flux:icon.magnifying-glass class="size-5 shrink-0" />
                <input
                    name="q"
                    value="{{ $query }}"
                    placeholder="{{ __('Search properties, units, people, invoices, and work orders') }}"
                    aria-label="{{ __('Global Search') }}"
                    autofocus
                >
                <button type="submit">{{ __('Search') }}</button>
            </form>
        </div>
    </div>

    @if(mb_strlen(trim($query)) < 2)
        <div class="kirada-empty-state mt-6">
            <flux:icon.magnifying-glass class="mx-auto size-8 text-kirada-ocean" />
            <p class="mt-3 font-semibold text-kirada-navy">{{ __('Search your Kirada workspace') }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ __('Enter at least two characters to begin.') }}</p>
        </div>
    @elseif($resultCount === 0)
        <div class="kirada-empty-state mt-6">
            <flux:icon.document-magnifying-glass class="mx-auto size-8 text-slate-400" />
            <p class="mt-3 font-semibold text-kirada-navy">{{ __('No results found') }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ __('Try a name, address, unit number, invoice reference, or work-order title.') }}</p>
        </div>
    @else
        <div class="mt-6 flex items-center justify-between">
            <p class="text-sm font-semibold text-kirada-navy">
                {{ trans_choice(':count result|:count results', $resultCount, ['count' => $resultCount]) }}
            </p>
            <p class="text-xs text-slate-500">{{ __('Results are limited by your role and portfolio access.') }}</p>
        </div>

        <div class="mt-4 grid gap-5 lg:grid-cols-2">
            @foreach($groups as $group)
                <section class="kirada-card p-0">
                    <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-slate-700/70">
                        <h2 class="font-semibold text-kirada-navy">{{ $group['label'] }}</h2>
                        <span class="kirada-pill border-kirada-ocean/20 bg-kirada-teal-soft text-kirada-ocean">
                            {{ $group['results']->count() }}
                        </span>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-700/70">
                        @foreach($group['results'] as $result)
                            <a href="{{ $result['href'] }}" wire:navigate class="kirada-search-result">
                                <span class="kirada-search-result-icon">
                                    <flux:icon.arrow-up-right class="size-4" />
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate font-semibold text-slate-900 dark:text-slate-100">{{ $result['title'] }}</span>
                                    @if($result['subtitle'])
                                        <span class="mt-0.5 block truncate text-xs text-slate-500 dark:text-slate-400">{{ $result['subtitle'] }}</span>
                                    @endif
                                </span>
                                <span class="shrink-0 text-xs font-medium text-slate-500 dark:text-slate-400">{{ $result['meta'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @endif
</div>
