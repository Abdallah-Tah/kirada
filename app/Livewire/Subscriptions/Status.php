<?php

namespace App\Livewire\Subscriptions;

use App\Models\Plan;
use App\Services\SubscriptionService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Status extends Component
{
    /** Which plan slug the user has opened the payment panel for */
    public ?string $selectedPlanSlug = null;

    /** Which payment gateway the user has chosen */
    public string $selectedGateway = 'stripe';

    /** Waafi phone number input */
    public string $waafiPhone = '';

    /** Inline result from Waafi / CAC Bank initiate() */
    public ?array $inlineResult = null;

    #[Computed]
    public function summary(): array
    {
        return app(SubscriptionService::class)->getStatusSummary(auth()->user());
    }

    #[Computed]
    public function plans()
    {
        return app(SubscriptionService::class)->getAvailablePlans();
    }

    #[Computed]
    public function enabledGateways(): array
    {
        return app(SubscriptionService::class)->enabledGateways();
    }

    /** Open the payment panel for a plan */
    public function openPayment(string $slug): void
    {
        $this->selectedPlanSlug = $slug;
        $this->selectedGateway  = 'stripe';
        $this->inlineResult     = null;
        $this->waafiPhone       = '';
    }

    public function closePayment(): void
    {
        $this->selectedPlanSlug = null;
        $this->inlineResult     = null;
    }

    /** Start trial / select plan during trial (no payment needed) */
    public function selectPlan(string $slug): void
    {
        $plan    = Plan::active()->where('slug', $slug)->firstOrFail();
        $service = app(SubscriptionService::class);
        $user    = auth()->user();
        $summary = $service->getStatusSummary($user);

        if (in_array($summary['state'], ['none', 'trialing'], true)) {
            $service->startTrial($user, $plan);
            session()->flash('status', __('Your free trial is now set to the :plan plan.', ['plan' => $plan->name]));
        } else {
            $service->activateSubscription($user, $plan, 'manual');
            session()->flash('status', __('Your subscription is now active on the :plan plan.', ['plan' => $plan->name]));
        }

        $this->selectedPlanSlug = null;
        unset($this->summary);
    }

    /**
     * Initiate checkout for inline gateways (Waafi / CAC Bank).
     * Stripe is handled via a regular form POST → redirect.
     */
    public function initiateInlinePayment(): void
    {
        if (! $this->selectedPlanSlug) {
            return;
        }

        $plan    = Plan::active()->where('slug', $this->selectedPlanSlug)->firstOrFail();
        $service = app(SubscriptionService::class);
        $user    = auth()->user();

        $options = [];
        if ($this->selectedGateway === 'waafi') {
            $this->validate(['waafiPhone' => 'required|string|regex:/^2526[1-9][0-9]{6}$/']);
            $options['phone'] = $this->waafiPhone;
        }

        try {
            $result = $service->initiateCheckout($user, $plan, $this->selectedGateway, $options);
        } catch (\Throwable $e) {
            $this->addError('payment', $e->getMessage());
            return;
        }

        if ($result['type'] === 'redirect') {
            $this->redirectAway($result['url']);
            return;
        }

        // Waafi approved synchronously or CAC Bank reference
        $this->inlineResult = $result['data'];

        if ($this->selectedGateway === 'waafi' && ($result['data']['state'] ?? null) === 'approved') {
            // Activate the subscription immediately — Waafi confirmed synchronously
            $service->activateSubscription($user, $plan, 'waafi');
            unset($this->summary);
            session()->flash('status', __('Waafi payment approved! Your :plan subscription is now active.', ['plan' => $plan->name]));
            $this->selectedPlanSlug = null;
        }
    }

    public function render()
    {
        return view('livewire.subscriptions.status')
            ->layout('layouts.app')
            ->title(__('Subscription'));
    }
}
