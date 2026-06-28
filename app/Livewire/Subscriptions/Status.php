<?php

namespace App\Livewire\Subscriptions;

use App\Models\Plan;
use App\Services\SubscriptionService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Status extends Component
{
    #[Computed]
    public function summary()
    {
        return app(SubscriptionService::class)->getStatusSummary(auth()->user());
    }

    #[Computed]
    public function plans()
    {
        return app(SubscriptionService::class)->getAvailablePlans();
    }

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
            $service->activateSubscription($user, $plan);
            session()->flash('status', __('Your subscription is now active on the :plan plan.', ['plan' => $plan->name]));
        }

        unset($this->summary);
    }

    public function render()
    {
        return view('livewire.subscriptions.status')
            ->layout('layouts.app')
            ->title(__('Subscription'));
    }
}
