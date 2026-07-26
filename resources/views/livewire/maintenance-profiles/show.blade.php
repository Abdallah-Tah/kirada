<div>
    <div class="kirada-page-header kirada-reveal">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <flux:heading size="xl">{{ $profile->business_name }}</flux:heading>
                    @if($profile->isVerified())<flux:badge color="blue" icon="shield-check">{{ __('Verified') }}</flux:badge>@endif
                </div>
                <flux:subheading>{{ $profile->headline ?: $profile->user->name }}</flux:subheading>
            </div>
            <flux:button :href="route('maintenance-directory.index')" wire:navigate variant="ghost" icon="arrow-left">{{ __('Back to directory') }}</flux:button>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="space-y-6">
            <section class="kirada-card">
                <h2 class="text-lg font-semibold">{{ __('About this provider') }}</h2>
                <p class="mt-3 whitespace-pre-wrap text-sm leading-6 text-zinc-600 dark:text-zinc-300">{{ $profile->bio }}</p>
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach($profile->trades ?? [] as $trade)<flux:badge>{{ __('trades.'.$trade) }}</flux:badge>@endforeach
                </div>
            </section>

            <section class="kirada-card">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">{{ __('Verified job reviews') }}</h2>
                    <span class="font-semibold text-amber-600">★ {{ number_format((float) ($profile->reviews_avg_rating ?? 0), 1) }} · {{ $profile->reviews_count }}</span>
                </div>
                <p class="mt-1 text-xs text-zinc-500">{{ __('Only landlords with a completed Kirada work order can leave a review.') }}</p>
                <div class="mt-5 space-y-4">
                    @forelse($reviews as $review)
                        <article class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                            <div class="flex justify-between gap-3">
                                <div><p class="font-medium">{{ $review->title ?: __('Completed maintenance job') }}</p><p class="text-xs text-zinc-500">{{ $review->landlord->name }} · {{ $review->created_at->format('M j, Y') }}</p></div>
                                <span class="text-amber-600">★ {{ $review->rating }}/5</span>
                            </div>
                            @if($review->comment)<p class="mt-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $review->comment }}</p>@endif
                            <div class="mt-3 flex flex-wrap gap-3 text-xs text-zinc-500">
                                <span>{{ __('Quality') }} {{ $review->quality_rating }}/5</span>
                                <span>{{ __('Communication') }} {{ $review->communication_rating }}/5</span>
                                <span>{{ __('Professionalism') }} {{ $review->professionalism_rating }}/5</span>
                            </div>
                        </article>
                    @empty
                        <p class="py-8 text-center text-sm text-zinc-500">{{ __('No verified job reviews yet.') }}</p>
                    @endforelse
                </div>
                <div class="mt-4">{{ $reviews->links() }}</div>
            </section>
        </div>

        <aside class="kirada-card h-fit">
            <div class="grid grid-cols-2 gap-4 text-center">
                <div><p class="text-2xl font-semibold">{{ $profile->user->completed_jobs_count }}</p><p class="text-xs text-zinc-500">{{ __('Completed jobs') }}</p></div>
                <div><p class="text-2xl font-semibold">{{ $profile->years_experience ?? '—' }}</p><p class="text-xs text-zinc-500">{{ __('Years experience') }}</p></div>
            </div>
            <div class="mt-5 space-y-3 text-sm">
                <p><span class="text-zinc-500">{{ __('Availability') }}:</span> {{ __(str($profile->availability_status)->replace('_', ' ')->title()->toString()) }}</p>
                <p><span class="text-zinc-500">{{ __('Service areas') }}:</span> {{ implode(', ', $profile->service_areas ?? []) ?: __('Not provided') }}</p>
                <p><span class="text-zinc-500">{{ __('Languages') }}:</span> {{ implode(', ', $profile->languages ?? []) ?: __('Not provided') }}</p>
                @if($profile->hourly_rate)<p><span class="text-zinc-500">{{ __('Hourly rate') }}:</span> {{ $profile->currency?->code }} {{ number_format($profile->hourly_rate) }}</p>@endif
                @if($profile->website)<a class="text-sky-700 hover:underline dark:text-sky-300" href="{{ $profile->website }}" target="_blank" rel="noopener noreferrer">{{ __('Provider website') }}</a>@endif
            </div>
        </aside>
    </div>
</div>
