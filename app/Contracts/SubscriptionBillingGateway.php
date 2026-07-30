<?php

namespace App\Contracts;

use App\Models\Plan;
use App\Models\User;

/**
 * Subscription billing gateway for the landlord's Kirada software plan.
 * Tenant rent collection is a separate, manual proof-based workflow.
 */
interface SubscriptionBillingGateway
{
    /**
     * Initiate a payment for $plan on behalf of $user.
     *
     * @return array{
     *   type: 'redirect',
     *   url?: string,
     * }
     */
    public function initiate(User $user, Plan $plan, array $options = []): array;
}
