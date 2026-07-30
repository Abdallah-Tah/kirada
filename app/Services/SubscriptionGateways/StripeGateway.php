<?php

namespace App\Services\SubscriptionGateways;

use App\Contracts\SubscriptionBillingGateway;
use App\Models\Plan;
use App\Models\User;

class StripeGateway implements SubscriptionBillingGateway
{
    /**
     * Create a Stripe Checkout Session for a recurring subscription.
     * Returns type='redirect' with the Checkout URL.
     */
    public function initiate(User $user, Plan $plan, array $options = []): array
    {
        if (! $plan->stripe_price_id) {
            throw new \RuntimeException(
                "Plan [{$plan->slug}] has no stripe_price_id. Run: php artisan stripe:sync-plans"
            );
        }

        $checkout = $user
            ->newSubscription('default', $plan->stripe_price_id)
            ->withMetadata([
                'kirada_plan_id' => (string) $plan->id,
                'kirada_user_id' => (string) $user->id,
            ])
            ->checkout([
                'success_url' => $options['success_url'] ?? route('subscription.status').'?checkout=success',
                'cancel_url' => $options['cancel_url'] ?? route('subscription.status').'?checkout=cancel',
                'allow_promotion_codes' => true,
            ]);

        return ['type' => 'redirect', 'url' => $checkout->url];
    }
}
