<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('messages.Subscription') }}</flux:heading>
    <flux:subheading>{{ __('Manage your Kirada subscription') }}</flux:subheading>
    </div>

    @php $summary = $this->summary; @endphp

    @if (session('status'))
        <div class="mt-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-700 dark:border-green-900/50 dark:bg-green-950/30 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif

    {{-- Status Card --}}
    <div class="kirada-form-card mt-6">
        @if($summary['state'] === 'none')
            <div class="flex items-center gap-3">
                <flux:icon.exclamation-triangle class="text-zinc-400" />
                <div>
                    <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('No Subscription') }}</h3>
                    <p class="text-sm text-zinc-500 mt-1">{{ __('You don\'t have a subscription yet. Start your 30-day free trial to explore Kirada.') }}</p>
                </div>
            </div>
        @elseif($summary['state'] === 'trialing')
            <div class="flex items-center gap-3">
                <flux:icon.clock class="text-green-500" />
                <div>
                    <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Trial Active') }}</h3>
                    <p class="text-sm text-zinc-500 mt-1">
                        {{ __('Your free trial ends on') }} {{ $summary['trial_ends_at']?->format('M j, Y') }}.
                        {{ $summary['days_left'] }} {{ __('days remaining') }}.
                    </p>
                </div>
            </div>
        @elseif($summary['state'] === 'trial_expired')
            <div class="flex items-center gap-3">
                <flux:icon.x-circle class="text-red-500" />
                <div>
                    <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Trial Expired') }}</h3>
                    <p class="text-sm text-zinc-500 mt-1">
                        {{ __('Your free trial ended on') }} {{ $summary['trial_ends_at']?->format('M j, Y') }}.
                        {{ __('Choose a plan below to continue using Kirada.') }}
                    </p>
                </div>
            </div>
        @elseif($summary['state'] === 'active')
            <div class="flex items-center gap-3">
                <flux:icon.check-circle class="text-green-500" />
                <div>
                    <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Active Subscription') }}</h3>
                    <p class="text-sm text-zinc-500 mt-1">
                        {{ __('Plan:') }} {{ $summary['plan']?->name ?? '—' }}
                        @if($summary['subscription']?->ends_at)
                            · {{ __('Renews on') }} {{ $summary['subscription']->ends_at->format('M j, Y') }}
                        @endif
                    </p>
                </div>
            </div>
        @else
            <div class="flex items-center gap-3">
                <flux:icon.exclamation-triangle class="text-zinc-400" />
                <div>
                    <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __(ucfirst($summary['state'])) }}</h3>
                    <p class="text-sm text-zinc-500 mt-1">{{ __('Contact support for assistance.') }}</p>
                </div>
            </div>
        @endif
    </div>

    {{-- Plans --}}
    @if(in_array($summary['state'], ['trialing', 'trial_expired', 'none']))
        <div class="mt-8">
            <h3 class="font-semibold text-zinc-900 dark:text-white mb-4">{{ __('Available Plans') }}</h3>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($this->plans as $plan)
                    @php $isCurrentPlan = $summary['plan']?->id === $plan->id; @endphp
                    <div class="kirada-stat-card grid gap-3 overflow-hidden">
                        <h4 class="font-semibold text-lg text-zinc-900 dark:text-white">{{ $plan->name }}</h4>
                        <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $plan->formattedPrice }}</p>
                        <p class="text-xs text-zinc-400">{{ __('per month') }}</p>
                        <p class="text-sm text-zinc-500">{{ $plan->limitsLabel }}</p>
                        @if($plan->description)
                            <p class="text-xs text-zinc-400">{{ $plan->description }}</p>
                        @endif
                        <flux:button
                            wire:click="selectPlan('{{ $plan->slug }}')"
                            data-confirm="{{ __('Select this subscription plan?') }}"
                            variant="{{ $isCurrentPlan ? 'ghost' : 'primary' }}"
                            :disabled="$isCurrentPlan"
                            class="w-full"
                        >
                            {{ $isCurrentPlan ? __('Current Plan') : ($summary['state'] === 'none' ? __('Start 30-day trial') : ($summary['state'] === 'trialing' ? __('Select Plan') : __('Activate Plan'))) }}
                        </flux:button>
                    </div>
                @endforeach
            </div>
            <p class="mt-4 text-xs text-zinc-400">
                {{ __('Payment gateway integration is still manual for now. Plan selection is recorded immediately.') }}
            </p>
        </div>
    @endif
</div>
