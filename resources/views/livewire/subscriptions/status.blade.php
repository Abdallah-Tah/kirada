<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('messages.Subscription') }}</flux:heading>
        <flux:subheading>{{ __('Manage your Kirada subscription') }}</flux:subheading>
    </div>

    @php $summary = $this->summary; @endphp

    {{-- Flash messages --}}
    @if (session('status'))
        <div class="mt-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-700 dark:border-green-900/50 dark:bg-green-950/30 dark:text-green-300">
            {{ session('status') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300">
            {{ session('error') }}
        </div>
    @endif

    {{-- Stripe return feedback --}}
    @if(request('checkout') === 'success')
        <div class="mt-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-medium text-green-700">
            {{ __('Payment completed! Your subscription will be activated shortly via webhook.') }}
        </div>
    @elseif(request('checkout') === 'cancel')
        <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm font-medium text-amber-700">
            {{ __('Checkout was cancelled. Your subscription was not changed.') }}
        </div>
    @endif

    {{-- ── Status Card ─────────────────────────────────────────────────── --}}
    <div class="kirada-form-card mt-6">
        @if($summary['state'] === 'none')
            <div class="flex items-center gap-3">
                <flux:icon.exclamation-triangle class="text-zinc-400" />
                <div>
                    <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('No Subscription') }}</h3>
                    <p class="text-sm text-zinc-500 mt-1">{{ __("You don't have a subscription yet. Start your 30-day free trial below.") }}</p>
                </div>
            </div>
        @elseif($summary['state'] === 'trialing')
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <flux:icon.clock class="text-green-500 shrink-0" />
                    <div>
                        <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Trial Active') }}</h3>
                        <p class="text-sm text-zinc-500 mt-1">
                            {{ __('Your free trial ends on') }} <strong>{{ $summary['trial_ends_at']?->format('M j, Y') }}</strong>
                            — {{ $summary['days_left'] }} {{ __('days remaining') }}.
                        </p>
                    </div>
                </div>
                @if($summary['plan'])
                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">{{ $summary['plan']->name }}</span>
                @endif
            </div>
        @elseif($summary['state'] === 'trial_expired')
            <div class="flex items-center gap-3">
                <flux:icon.x-circle class="text-red-500 shrink-0" />
                <div>
                    <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Trial Expired') }}</h3>
                    <p class="text-sm text-zinc-500 mt-1">
                        {{ __('Your trial ended on') }} {{ $summary['trial_ends_at']?->format('M j, Y') }}.
                        {{ __('Choose a plan and payment method below to continue.') }}
                    </p>
                </div>
            </div>
        @elseif($summary['state'] === 'active')
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <flux:icon.check-circle class="text-green-500 shrink-0" />
                    <div>
                        <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Active Subscription') }}</h3>
                        <p class="text-sm text-zinc-500 mt-1">
                            {{ __('Plan:') }} <strong>{{ $summary['plan']?->name ?? '—' }}</strong>
                            @if($summary['subscription']?->ends_at)
                                &middot; {{ __('Renews on') }} {{ $summary['subscription']->ends_at->format('M j, Y') }}
                            @endif
                            @if($summary['subscription']?->gateway)
                                &middot; {{ __('via') }} {{ ucfirst($summary['subscription']->gateway) }}
                            @endif
                        </p>
                    </div>
                </div>
                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">{{ __('Active') }}</span>
            </div>
        @elseif($summary['state'] === 'past_due')
            <div class="flex items-center gap-3">
                <flux:icon.exclamation-triangle class="text-amber-500 shrink-0" />
                <div>
                    <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Payment Overdue') }}</h3>
                    <p class="text-sm text-zinc-500 mt-1">{{ __('Your last payment failed. Please update your payment method to restore access.') }}</p>
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

    {{-- ── Plan Grid ────────────────────────────────────────────────────── --}}
    @if(in_array($summary['state'], ['trialing', 'trial_expired', 'none', 'past_due', 'cancelled', 'expired']))
        <div class="mt-8">
            <h3 class="font-semibold text-zinc-900 dark:text-white mb-4">{{ __('Available Plans') }}</h3>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($this->plans as $plan)
                    @php $isCurrentPlan = $summary['plan']?->id === $plan->id; @endphp
                    <div class="kirada-stat-card grid gap-3 overflow-hidden {{ $isCurrentPlan ? 'ring-2 ring-kirada-ocean' : '' }}">
                        <div class="flex items-start justify-between">
                            <h4 class="font-semibold text-lg text-zinc-900 dark:text-white">{{ $plan->name }}</h4>
                            @if($isCurrentPlan)
                                <span class="rounded-full bg-kirada-ocean/10 px-2 py-0.5 text-xs font-semibold text-kirada-ocean">{{ __('Current') }}</span>
                            @endif
                        </div>
                        @if($plan->monthly_price > 0)
                            <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $plan->formattedPrice }}</p>
                            <p class="text-xs text-zinc-400 -mt-2">{{ __('per month') }}</p>
                        @else
                            <p class="text-lg font-semibold text-zinc-500">{{ __('Custom pricing') }}</p>
                        @endif
                        <p class="text-sm text-zinc-500">{{ $plan->limitsLabel }}</p>
                        @if($plan->description)
                            <p class="text-xs text-zinc-400">{{ $plan->description }}</p>
                        @endif

                        @if(in_array($summary['state'], ['none', 'trialing']))
                            {{-- Trial: just select the plan, no payment yet --}}
                            <flux:button
                                wire:click="selectPlan('{{ $plan->slug }}')"
                                variant="{{ $isCurrentPlan ? 'ghost' : 'primary' }}"
                                :disabled="$isCurrentPlan"
                                class="w-full mt-auto"
                            >
                                {{ $isCurrentPlan ? __('Current Plan') : ($summary['state'] === 'none' ? __('Start 30-day trial') : __('Select Plan')) }}
                            </flux:button>
                        @elseif($plan->monthly_price <= 0)
                            <a href="mailto:hello@kirada.app?subject=Enterprise+Plan" class="kirada-btn-secondary text-center w-full mt-auto block">
                                {{ __('Contact Us') }}
                            </a>
                        @else
                            {{-- Paid state: open payment panel --}}
                            <flux:button
                                wire:click="openPayment('{{ $plan->slug }}')"
                                variant="{{ $isCurrentPlan ? 'ghost' : 'primary' }}"
                                class="w-full mt-auto"
                            >
                                {{ $isCurrentPlan ? __('Renew Plan') : __('Subscribe') }}
                            </flux:button>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Payment Panel (modal-style slide-in) ────────────────────────── --}}
    @if($selectedPlanSlug)
        @php $selectedPlan = $this->plans->firstWhere('slug', $selectedPlanSlug); @endphp

        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="closePayment">
            <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl dark:bg-zinc-900" @click.stop>

                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-zinc-100 px-6 py-4 dark:border-zinc-800">
                    <div>
                        <h3 class="font-semibold text-zinc-900 dark:text-white">
                            {{ __('Subscribe to :plan', ['plan' => $selectedPlan?->name]) }}
                        </h3>
                        <p class="text-sm text-zinc-500 mt-0.5">{{ $selectedPlan?->formattedPrice }} / {{ __('month') }}</p>
                    </div>
                    <button wire:click="closePayment" class="rounded-lg p-1 text-zinc-400 hover:text-zinc-600 transition">
                        <flux:icon.x-mark class="size-5" />
                    </button>
                </div>

                <div class="p-6 space-y-5">

                    @error('payment')
                        <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $message }}</div>
                    @enderror

                    @if($inlineResult)
                        {{-- CAC Bank transfer instructions --}}
                        @if(($inlineResult['method'] ?? null) === 'bank_transfer')
                            <div class="rounded-xl border border-kirada-ocean/20 bg-kirada-ocean/5 p-4 space-y-3">
                                <div class="flex items-center gap-2 font-semibold text-kirada-navy dark:text-white">
                                    <flux:icon.building-library class="size-5 text-kirada-ocean" />
                                    {{ __('Bank Transfer Instructions') }}
                                </div>
                                <dl class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <dt class="text-zinc-500">{{ __('Amount') }}</dt>
                                        <dd class="font-semibold">{{ number_format($inlineResult['amount'], 0) }} {{ $inlineResult['currency'] }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-zinc-500">{{ __('Account Name') }}</dt>
                                        <dd class="font-mono text-xs">{{ $inlineResult['account_name'] }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-zinc-500">{{ __('Account No.') }}</dt>
                                        <dd class="font-mono text-xs">{{ $inlineResult['account_number'] }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-zinc-500">IBAN</dt>
                                        <dd class="font-mono text-xs">{{ $inlineResult['iban'] }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-zinc-500">SWIFT</dt>
                                        <dd class="font-mono text-xs">{{ $inlineResult['swift'] }}</dd>
                                    </div>
                                    <div class="rounded-lg bg-amber-50 border border-amber-200 px-3 py-2 mt-2">
                                        <dt class="text-xs text-amber-700 font-semibold">{{ __('Payment Reference') }}</dt>
                                        <dd class="font-mono font-bold text-amber-900 text-sm mt-0.5">{{ $inlineResult['reference'] }}</dd>
                                        <p class="text-xs text-amber-700 mt-1">{{ __('Include this reference in your transfer.') }}</p>
                                    </div>
                                </dl>
                                <p class="text-xs text-zinc-500">{{ $inlineResult['instructions'] }}</p>
                            </div>
                            <flux:button wire:click="closePayment" variant="ghost" class="w-full">
                                {{ __('Close — I\'ll transfer now') }}
                            </flux:button>
                        @endif
                    @else
                        {{-- Gateway selection --}}
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400 mb-2">{{ __('Payment method') }}</p>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach($this->enabledGateways as $gw)
                                    <button
                                        wire:click="$set('selectedGateway', '{{ $gw }}')"
                                        class="rounded-xl border-2 px-3 py-2.5 text-sm font-medium transition
                                            {{ $selectedGateway === $gw
                                                ? 'border-kirada-ocean bg-kirada-ocean/5 text-kirada-ocean'
                                                : 'border-zinc-200 text-zinc-600 hover:border-zinc-300 dark:border-zinc-700 dark:text-zinc-400' }}"
                                    >
                                        @if($gw === 'stripe')
                                            <span class="block text-center">💳</span>
                                            <span>{{ __('Card') }}</span>
                                        @elseif($gw === 'waafi')
                                            <span class="block text-center">📱</span>
                                            <span>WaafiPay</span>
                                        @elseif($gw === 'cacbank')
                                            <span class="block text-center">🏦</span>
                                            <span>CAC Bank</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- Stripe: standard form POST to avoid XHR redirect issues --}}
                        @if($selectedGateway === 'stripe')
                            <div class="rounded-xl border border-zinc-100 bg-zinc-50 p-4 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-800/50 dark:text-zinc-400">
                                <div class="flex items-start gap-2">
                                    <flux:icon.lock-closed class="size-4 shrink-0 mt-0.5 text-zinc-400" />
                                    <p>{{ __('You\'ll be redirected to Stripe\'s secure checkout. Visa, Mastercard, and more accepted.') }}</p>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('subscription.checkout', [$selectedPlanSlug, 'stripe']) }}">
                                @csrf
                                <flux:button type="submit" variant="primary" class="w-full">
                                    {{ __('Pay with Card → Stripe Checkout') }}
                                </flux:button>
                            </form>
                        @endif

                        {{-- Waafi: phone number form --}}
                        @if($selectedGateway === 'waafi')
                            <div class="space-y-3">
                                <div class="rounded-xl border border-zinc-100 bg-zinc-50 p-4 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-800/50 dark:text-zinc-400">
                                    <div class="flex items-start gap-2">
                                        <flux:icon.device-phone-mobile class="size-4 shrink-0 mt-0.5 text-zinc-400" />
                                        <p>{{ __('Enter your WaafiPay phone number. You\'ll receive a payment prompt on your device.') }}</p>
                                    </div>
                                </div>
                                <flux:input
                                    wire:model="waafiPhone"
                                    label="{{ __('Waafi Phone Number') }}"
                                    placeholder="252612345678"
                                    type="tel"
                                />
                                @error('waafiPhone')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <flux:button
                                    wire:click="initiateInlinePayment"
                                    wire:loading.attr="disabled"
                                    variant="primary"
                                    class="w-full"
                                >
                                    <span wire:loading.remove>{{ __('Pay :amount via WaafiPay', ['amount' => $selectedPlan?->formattedPrice]) }}</span>
                                    <span wire:loading>{{ __('Processing…') }}</span>
                                </flux:button>
                            </div>
                        @endif

                        {{-- CAC Bank: show instructions immediately --}}
                        @if($selectedGateway === 'cacbank')
                            <div class="rounded-xl border border-zinc-100 bg-zinc-50 p-4 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-800/50 dark:text-zinc-400">
                                <div class="flex items-start gap-2">
                                    <flux:icon.building-library class="size-4 shrink-0 mt-0.5 text-zinc-400" />
                                    <p>{{ __('A unique bank reference will be generated. Transfer the amount to CAC Bank and your subscription activates within 1 business day.') }}</p>
                                </div>
                            </div>
                            <flux:button
                                wire:click="initiateInlinePayment"
                                wire:loading.attr="disabled"
                                variant="primary"
                                class="w-full"
                            >
                                <span wire:loading.remove>{{ __('Get Bank Transfer Details') }}</span>
                                <span wire:loading>{{ __('Generating…') }}</span>
                            </flux:button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
