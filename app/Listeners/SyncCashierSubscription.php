<?php

namespace App\Listeners;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Carbon;
use Laravel\Cashier\Events\WebhookHandled;

class SyncCashierSubscription
{
    public function handle(WebhookHandled $event): void
    {
        if (! str_starts_with($event->payload['type'] ?? '', 'customer.subscription.')) {
            return;
        }

        $data = $event->payload['data']['object'] ?? [];
        $stripeId = $data['id'] ?? null;

        if (! $stripeId) {
            return;
        }

        $subscription = Subscription::query()->where('stripe_id', $stripeId)->first();

        if (! $subscription) {
            return;
        }

        $stripePrice = $data['items']['data'][0]['price']['id'] ?? $subscription->stripe_price;
        $planId = isset($data['metadata']['kirada_plan_id'])
            ? (int) $data['metadata']['kirada_plan_id']
            : Plan::query()->where('stripe_price_id', $stripePrice)->value('id');
        $stripeStatus = $data['status'] ?? $subscription->stripe_status;

        $subscription->forceFill([
            'plan_id' => $planId ?: $subscription->plan_id,
            'status' => match ($stripeStatus) {
                'active', 'trialing' => $stripeStatus,
                'past_due', 'unpaid', 'incomplete' => 'past_due',
                'canceled' => 'cancelled',
                default => 'expired',
            },
            'starts_at' => $subscription->starts_at ?: now(),
            'ends_at' => isset($data['cancel_at']) && $data['cancel_at']
                ? Carbon::createFromTimestamp($data['cancel_at'])
                : $subscription->ends_at,
            'payment_method' => 'card',
            'gateway' => 'stripe',
            'gateway_subscription_id' => $stripeId,
            'gateway_status' => $stripeStatus,
        ])->save();
    }
}
