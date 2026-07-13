<?php

namespace App\Services;

use App\Contracts\SubscriptionBillingGateway;
use App\Models\Plan;
use App\Models\Lease;
use App\Models\Unit;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionGateways\CacBankGateway;
use App\Services\SubscriptionGateways\StripeGateway;
use App\Services\SubscriptionGateways\WaafiPayGateway;

class SubscriptionService
{
    private const GATEWAYS = ['stripe', 'waafi', 'cacbank'];

    public const TRIAL_DAYS = 30;

    /**
     * Start a 30-day trial for a landlord.
     */
    public function startTrial(User $user, ?Plan $plan = null): Subscription
    {
        if (! $user->isLandlord()) {
            throw new \DomainException('Only landlord accounts can start a trial.');
        }

        // Check if already has a subscription
        $existing = Subscription::where('user_id', $user->id)->first();
        if ($existing) {
            if ($plan && $existing->isTrialing()) {
                $existing->update(['plan_id' => $plan->id]);
            }

            return $existing->fresh();
        }

        return Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan?->id,
            'status' => 'trialing',
            'trial_ends_at' => now()->addDays(self::TRIAL_DAYS),
        ]);
    }

    /**
     * Activate a subscription with a plan.
     */
    public function activateSubscription(User $user, Plan $plan, string $paymentMethod = 'manual'): Subscription
    {
        $sub = Subscription::where('user_id', $user->id)->first();

        if (! $sub) {
            $sub = new Subscription(['user_id' => $user->id]);
        }

        $sub->fill([
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
            'payment_method' => $paymentMethod,
        ]);

        $sub->save();

        return $sub->fresh();
    }

    /**
     * Cancel a subscription.
     */
    public function cancelSubscription(Subscription $subscription): Subscription
    {
        $subscription->update(['status' => 'cancelled']);

        return $subscription->fresh();
    }

    /**
     * Mark expired trials as expired.
     */
    public function expireTrials(): int
    {
        return Subscription::where('status', 'trialing')
            ->where('trial_ends_at', '<', now())
            ->update(['status' => 'expired']);
    }

    /**
     * Mark active subscriptions whose paid period has ended.
     */
    public function expireEndedSubscriptions(): int
    {
        return Subscription::where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->update(['status' => 'expired']);
    }

    public function enforceActiveUnitLimit(User $landlord): void
    {
        $plan = $landlord->subscription?->plan;

        if (! $plan || $plan->max_active_units === null) {
            return;
        }

        $activeUnits = Unit::query()
            ->where('is_active', true)
            ->whereHas('property', fn ($query) => $query->where('landlord_id', $landlord->id))
            ->count();

        if ($activeUnits >= $plan->max_active_units) {
            throw new \DomainException("Your current plan allows up to {$plan->max_active_units} active units.");
        }
    }

    public function enforceActiveLeaseLimit(User $landlord): void
    {
        $plan = $landlord->subscription?->plan;

        if (! $plan || $plan->max_active_leases === null) {
            return;
        }

        $activeLeases = Lease::query()
            ->where('landlord_id', $landlord->id)
            ->where('status', 'active')
            ->count();

        if ($activeLeases >= $plan->max_active_leases) {
            throw new \DomainException("Your current plan allows up to {$plan->max_active_leases} active leases.");
        }
    }

    /**
     * Initiate a checkout for a plan via the chosen gateway.
     * Returns the gateway result array (type + url or data).
     */
    public function initiateCheckout(User $user, Plan $plan, string $gateway, array $options = []): array
    {
        if (! in_array($gateway, self::GATEWAYS, true)) {
            throw new \InvalidArgumentException("Unknown payment gateway: {$gateway}");
        }

        abort_unless($user->isLandlord(), 403);

        return $this->gateway($gateway)->initiate($user, $plan, $options);
    }

    /**
     * Resolve a billing gateway by name.
     */
    public function gateway(string $name): SubscriptionBillingGateway
    {
        return match ($name) {
            'stripe'  => app(StripeGateway::class),
            'waafi'   => app(WaafiPayGateway::class),
            'cacbank' => app(CacBankGateway::class),
            default   => throw new \InvalidArgumentException("Unknown gateway: {$name}"),
        };
    }

    /**
     * List the enabled subscription gateways.
     */
    public function enabledGateways(): array
    {
        $enabled = [];

        if (config('services.stripe.secret')) {
            $enabled[] = 'stripe';
        }
        if (config('services.waafi.merchant_uid')) {
            $enabled[] = 'waafi';
        }
        // CAC Bank is always available (reference-based, no API credentials needed)
        $enabled[] = 'cacbank';

        return $enabled;
    }

    /**
     * Get available plans.
     */
    public function getAvailablePlans()
    {
        return Plan::active()->orderBy('monthly_price')->get();
    }

    /**
     * Get the subscription status summary for a user.
     */
    public function getStatusSummary(User $user): array
    {
        $sub = $user->subscription;

        if (! $sub) {
            return [
                'state' => 'none',
                'subscription' => null,
                'plan' => null,
                'trial_ends_at' => null,
                'days_left' => null,
            ];
        }

        if ($sub->trialIsActive()) {
            return [
                'state' => 'trialing',
                'subscription' => $sub,
                'plan' => $sub->plan,
                'trial_ends_at' => $sub->trial_ends_at,
                'days_left' => (int) now()->diffInDays($sub->trial_ends_at, false),
            ];
        }

        if ($sub->trialHasExpired()) {
            return [
                'state' => 'trial_expired',
                'subscription' => $sub,
                'plan' => $sub->plan,
                'trial_ends_at' => $sub->trial_ends_at,
                'days_left' => 0,
            ];
        }

        if ($sub->isActive()) {
            return [
                'state' => 'active',
                'subscription' => $sub,
                'plan' => $sub->plan,
                'trial_ends_at' => null,
                'days_left' => null,
            ];
        }

        return [
            'state' => $sub->status,
            'subscription' => $sub,
            'plan' => $sub->plan,
            'trial_ends_at' => null,
            'days_left' => null,
        ];
    }
}
