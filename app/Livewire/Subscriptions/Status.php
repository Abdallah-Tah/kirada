<?php

namespace App\Livewire\Subscriptions;

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

    public function render()
    {
        return view('livewire.subscriptions.status')
            ->layout('layouts.app')
            ->title(__('Subscription'));
    }
}