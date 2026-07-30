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

    /** Open the payment panel for a plan */
    public function openPayment(string $slug): void
    {
        $this->selectedPlanSlug = $slug;
    }

    public function closePayment(): void
    {
        $this->selectedPlanSlug = null;
    }

    /** Start trial / select plan during trial (no payment needed) */
    public function selectPlan(string $slug): void
    {
        $plan = Plan::active()->where('slug', $slug)->firstOrFail();
        $service = app(SubscriptionService::class);
        $user = auth()->user();
        $summary = $service->getStatusSummary($user);

        if (in_array($summary['state'], ['none', 'trialing'], true)) {
            $service->startTrial($user, $plan);
            session()->flash('status', __('Your free trial is now set to the :plan plan.', ['plan' => $plan->name]));
        } else {
            $this->openPayment($plan->slug);

            return;
        }

        $this->selectedPlanSlug = null;
        unset($this->summary);
    }

    public function render()
    {
        return view('livewire.subscriptions.status')
            ->layout('layouts.app')
            ->title(__('Subscription'));
    }
}
