<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Carbon;

class SubscriptionService
{
    public const TRIAL_DAYS = 30;

    /**
     * Start a 30-day trial for a landlord.
     */
    public function startTrial(User $user): Subscription
    {
        if (!$user->isLandlord()) {
            throw new \DomainException('Only landlord accounts can start a trial.');
        }

        // Check if already has a subscription
        $existing = Subscription::where('user_id', $user->id)->first();
        if ($existing) {
            return $existing;
        }

        return Subscription::create([
            'user_id'      => $user->id,
            'status'       => 'trialing',
            'trial_ends_at' => now()->addDays(self::TRIAL_DAYS),
        ]);
    }

    /**
     * Activate a subscription with a plan.
     */
    public function activateSubscription(User $user, Plan $plan, string $paymentMethod = 'manual'): Subscription
    {
        $sub = Subscription::where('user_id', $user->id)->first();

        if (!$sub) {
            $sub = new Subscription(['user_id' => $user->id]);
        }

        $sub->fill([
            'plan_id'        => $plan->id,
            'status'         => 'active',
            'starts_at'      => now(),
            'ends_at'        => now()->addMonth(),
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

        if (!$sub) {
            return [
                'state'         => 'none',
                'subscription'  => null,
                'plan'          => null,
                'trial_ends_at' => null,
                'days_left'     => null,
            ];
        }

        if ($sub->trialIsActive()) {
            return [
                'state'         => 'trialing',
                'subscription'  => $sub,
                'plan'          => $sub->plan,
                'trial_ends_at' => $sub->trial_ends_at,
                'days_left'     => (int) now()->diffInDays($sub->trial_ends_at, false),
            ];
        }

        if ($sub->trialHasExpired()) {
            return [
                'state'         => 'trial_expired',
                'subscription'  => $sub,
                'plan'          => $sub->plan,
                'trial_ends_at' => $sub->trial_ends_at,
                'days_left'     => 0,
            ];
        }

        if ($sub->isActive()) {
            return [
                'state'         => 'active',
                'subscription'  => $sub,
                'plan'          => $sub->plan,
                'trial_ends_at' => null,
                'days_left'     => null,
            ];
        }

        return [
            'state'         => $sub->status,
            'subscription'  => $sub,
            'plan'          => $sub->plan,
            'trial_ends_at' => null,
            'days_left'     => null,
        ];
    }
}