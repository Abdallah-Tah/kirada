<?php

namespace App\Contracts;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Subscription billing gateway — landlord pays Kirada for their plan.
 *
 * Each implementation handles one payment method (Stripe card, Waafi mobile
 * money, CAC Bank transfer). The gateway is responsible for:
 *   1. Initiating a checkout (returns a URL the browser navigates to, or
 *      structured data the UI renders inline).
 *   2. Verifying and parsing incoming webhook / callback payloads.
 */
interface SubscriptionBillingGateway
{
    /**
     * Initiate a payment for $plan on behalf of $user.
     *
     * @return array{
     *   type: 'redirect'|'inline',
     *   url?: string,
     *   data?: array<string, mixed>,
     * }
     */
    public function initiate(User $user, Plan $plan, array $options = []): array;

    /**
     * Verify the authenticity of an incoming webhook request.
     */
    public function verifyWebhook(Request $request): bool;

    /**
     * Parse a webhook payload into a normalised event.
     *
     * @return array{
     *   event: string,
     *   gateway_subscription_id?: string,
     *   gateway_status?: string,
     *   stripe_customer_id?: string,
     *   plan_id?: int,
     *   ends_at?: \Carbon\Carbon,
     * }|null
     */
    public function parseWebhook(Request $request): ?array;
}
