<div>
    <div class="kirada-page-header kirada-reveal">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Kirada subscription') }}</flux:heading>
                <flux:subheading>{{ __('Manage the card subscription for your landlord workspace. Rent payments are handled separately.') }}</flux:subheading>
            </div>
            @if(auth()->user()->hasStripeId())
                <flux:button :href="route('subscription.portal')" icon="credit-card">
                    {{ __('Manage billing') }}
                </flux:button>
            @endif
        </div>
    </div>

    @php $summary = $this->summary; @endphp

    @if (session('status'))
        <div class="mt-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-800 dark:border-green-900/50 dark:bg-green-950/30 dark:text-green-200">
            {{ session('status') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-800 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-200">
            {{ session('error') }}
        </div>
    @endif

    @if(request('checkout') === 'success')
        <div class="mt-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-900/50 dark:bg-green-950/30 dark:text-green-200">
            <p class="font-semibold">{{ __('Card checkout completed') }}</p>
            <p class="mt-1">{{ __('Stripe is confirming the subscription. This page updates after the signed webhook is received.') }}</p>
        </div>
    @elseif(request('checkout') === 'cancel')
        <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-200">
            {{ __('Checkout was cancelled. No subscription change was made.') }}
        </div>
    @endif

    <div class="mt-6 grid gap-5 lg:grid-cols-[minmax(0,1.5fr)_minmax(18rem,.7fr)]">
        <section class="kirada-form-card">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    @if($summary['state'] === 'active')
                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-green-100 text-green-700 dark:bg-green-950/50 dark:text-green-300">
                            <flux:icon.check-circle class="size-6" />
                        </span>
                    @elseif($summary['state'] === 'trialing')
                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-sky-100 text-kirada-ocean dark:bg-sky-950/50">
                            <flux:icon.clock class="size-6" />
                        </span>
                    @else
                        <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">
                            <flux:icon.exclamation-triangle class="size-6" />
                        </span>
                    @endif
                    <div>
                        <h2 class="text-lg font-semibold text-slate-950 dark:text-white">
                            @switch($summary['state'])
                                @case('active') {{ __('Active subscription') }} @break
                                @case('trialing') {{ __('Free trial active') }} @break
                                @case('trial_expired') {{ __('Trial expired') }} @break
                                @case('past_due') {{ __('Card payment needs attention') }} @break
                                @default {{ __('Choose your Kirada plan') }}
                            @endswitch
                        </h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            @if($summary['state'] === 'trialing')
                                {{ __('Your trial ends :date with :days days remaining.', ['date' => $summary['trial_ends_at']?->format('M j, Y'), 'days' => $summary['days_left']]) }}
                            @elseif($summary['state'] === 'active')
                                {{ __('Your :plan workspace is billed securely by Stripe.', ['plan' => $summary['plan']?->name ?? __('Kirada')]) }}
                            @elseif($summary['state'] === 'past_due')
                                {{ __('Open the billing portal to update the card or resolve the failed invoice.') }}
                            @else
                                {{ __('Select a plan and complete secure card checkout to activate the landlord workspace.') }}
                            @endif
                        </p>
                    </div>
                </div>
                @if($summary['plan'])
                    <flux:badge color="{{ $summary['state'] === 'active' ? 'green' : 'blue' }}">{{ $summary['plan']->name }}</flux:badge>
                @endif
            </div>

            @if(in_array($summary['state'], ['active', 'past_due'], true) && auth()->user()->hasStripeId())
                <div class="mt-5 flex flex-wrap gap-3 border-t border-slate-200 pt-5 dark:border-slate-700">
                    <flux:button :href="route('subscription.portal')" variant="primary" icon="arrow-top-right-on-square">
                        {{ __('Open Stripe billing portal') }}
                    </flux:button>
                    <p class="self-center text-xs text-slate-500">{{ __('Update your card, view invoices, change plan, or cancel renewal securely in Stripe.') }}</p>
                </div>
            @endif
        </section>

        <aside class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-900/60">
            <div class="flex items-center gap-2">
                <flux:icon.shield-check class="size-5 text-kirada-green" />
                <h2 class="font-semibold">{{ __('Two separate payment flows') }}</h2>
            </div>
            <dl class="mt-4 space-y-4 text-sm">
                <div>
                    <dt class="font-semibold text-slate-900 dark:text-white">{{ __('Kirada subscription') }}</dt>
                    <dd class="mt-1 text-slate-500">{{ __('The landlord pays Kirada by card through Stripe Cashier.') }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-slate-900 dark:text-white">{{ __('Tenant rent') }}</dt>
                    <dd class="mt-1 text-slate-500">{{ __('The tenant pays the landlord directly using the landlord’s Waafi, D-Money, CAC Bank, cash, or other account, then uploads proof.') }}</dd>
                </div>
            </dl>
        </aside>
    </div>

    @if(in_array($summary['state'], ['trialing', 'trial_expired', 'none', 'past_due', 'cancelled', 'expired'], true))
        <section class="mt-8">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-slate-950 dark:text-white">{{ __('Plans for every portfolio') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('Start with a 30-day trial. A card is required only when you subscribe after the trial.') }}</p>
            </div>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($this->plans as $plan)
                    @php $isCurrentPlan = $summary['plan']?->id === $plan->id; @endphp
                    <article class="kirada-stat-card flex flex-col gap-4 {{ $isCurrentPlan ? 'ring-2 ring-kirada-ocean' : '' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-semibold text-slate-950 dark:text-white">{{ $plan->name }}</h3>
                                <p class="mt-1 text-sm text-slate-500">{{ $plan->description }}</p>
                            </div>
                            @if($isCurrentPlan)
                                <flux:badge color="blue">{{ __('Current') }}</flux:badge>
                            @endif
                        </div>
                        <div>
                            <p class="text-3xl font-bold tracking-tight text-slate-950 dark:text-white">{{ $plan->formattedPrice }}</p>
                            <p class="text-xs text-slate-500">{{ __('per month') }} · {{ $plan->limitsLabel }}</p>
                        </div>
                        <div class="mt-auto">
                            @if(in_array($summary['state'], ['none', 'trialing'], true))
                                <flux:button
                                    wire:click="selectPlan('{{ $plan->slug }}')"
                                    data-confirm="{{ __('Use this plan during your free trial? Your portfolio limits will update immediately.') }}"
                                    data-confirm-title="{{ __('Confirm plan selection') }}"
                                    data-confirm-button="{{ __('Select plan') }}"
                                    data-confirm-variant="warning"
                                    variant="{{ $isCurrentPlan ? 'filled' : 'primary' }}"
                                    class="w-full"
                                >
                                    {{ $isCurrentPlan ? __('Selected for trial') : __('Use during free trial') }}
                                </flux:button>
                            @else
                                <flux:button wire:click="openPayment('{{ $plan->slug }}')" variant="primary" class="w-full" icon="credit-card">
                                    {{ __('Subscribe by card') }}
                                </flux:button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if($selectedPlanSlug)
        @php $selectedPlan = $this->plans->firstWhere('slug', $selectedPlanSlug); @endphp
        <div class="fixed inset-0 z-50 grid place-items-center bg-slate-950/55 p-4 backdrop-blur-sm" wire:click.self="closePayment">
            <section class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
                <div class="border-b border-slate-200 p-6 dark:border-slate-700">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-kirada-ocean">{{ __('Secure card subscription') }}</p>
                            <h2 class="mt-2 text-xl font-semibold">{{ $selectedPlan?->name }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ $selectedPlan?->formattedPrice }} / {{ __('month') }}</p>
                        </div>
                        <flux:button wire:click="closePayment" variant="ghost" icon="x-mark" square />
                    </div>
                </div>
                <div class="space-y-5 p-6">
                    <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-sm text-slate-700 dark:border-sky-900/60 dark:bg-sky-950/30 dark:text-slate-300">
                        <div class="flex gap-3">
                            <flux:icon.lock-closed class="size-5 shrink-0 text-kirada-ocean" />
                            <p>{{ __('You will continue to Stripe Checkout. Kirada never stores your full card number. Stripe handles card validation, invoices, renewals, and payment authentication.') }}</p>
                        </div>
                    </div>
                    <form
                        method="POST"
                        action="{{ route('subscription.checkout', $selectedPlanSlug) }}"
                        data-confirm="{{ __('Continue to Stripe Checkout? You can review the final price before authorizing payment.') }}"
                        data-confirm-title="{{ __('Secure subscription') }}"
                        data-confirm-button="{{ __('Continue') }}"
                        data-confirm-variant="primary"
                    >
                        @csrf
                        <flux:button type="submit" variant="primary" class="w-full" icon="arrow-top-right-on-square">
                            {{ __('Continue to secure Stripe checkout') }}
                        </flux:button>
                    </form>
                    <flux:button wire:click="closePayment" variant="ghost" class="w-full">{{ __('Not now') }}</flux:button>
                </div>
            </section>
        </div>
    @endif
</div>
