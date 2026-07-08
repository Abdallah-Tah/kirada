<?php

namespace App\Services\SubscriptionGateways;

use App\Contracts\SubscriptionBillingGateway;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Stripe\Customer;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Checkout\Session as CheckoutSession;

class StripeGateway implements SubscriptionBillingGateway
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

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

        $customerId = $this->resolveCustomer($user);

        $session = CheckoutSession::create([
            'mode'                => 'subscription',
            'customer'            => $customerId,
            'line_items'          => [[
                'price'    => $plan->stripe_price_id,
                'quantity' => 1,
            ]],
            'success_url'         => $options['success_url'] ?? route('subscription.status') . '?checkout=success',
            'cancel_url'          => $options['cancel_url']  ?? route('subscription.status') . '?checkout=cancel',
            'metadata'            => ['plan_id' => $plan->id, 'user_id' => $user->id],
            'subscription_data'   => ['metadata' => ['plan_id' => $plan->id, 'user_id' => $user->id]],
            'allow_promotion_codes' => true,
        ]);

        return ['type' => 'redirect', 'url' => $session->url];
    }

    public function verifyWebhook(Request $request): bool
    {
        $secret = config('services.stripe.webhook_secret');

        if (! $secret) {
            return false;
        }

        try {
            Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', ''),
                $secret
            );

            return true;
        } catch (SignatureVerificationException) {
            return false;
        }
    }

    public function parseWebhook(Request $request): ?array
    {
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', ''),
                $secret
            );
        } catch (\Exception) {
            return null;
        }

        return match ($event->type) {
            'customer.subscription.created',
            'customer.subscription.updated' => $this->mapSubscriptionEvent($event),
            'customer.subscription.deleted' => $this->mapSubscriptionEvent($event, 'cancelled'),
            'invoice.payment_failed'        => $this->mapInvoiceEvent($event, 'past_due'),
            default                         => null,
        };
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function resolveCustomer(User $user): string
    {
        if ($user->stripe_customer_id) {
            return $user->stripe_customer_id;
        }

        $customer = Customer::create([
            'email'    => $user->email,
            'name'     => $user->name,
            'metadata' => ['user_id' => $user->id],
        ]);

        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer->id;
    }

    private function mapSubscriptionEvent(object $event, ?string $forceStatus = null): array
    {
        $sub  = $event->data->object;
        $meta = $sub->metadata->toArray();

        return [
            'event'                   => $event->type,
            'gateway_subscription_id' => $sub->id,
            'gateway_status'          => $sub->status,
            'stripe_customer_id'      => $sub->customer,
            'plan_id'                 => isset($meta['plan_id']) ? (int) $meta['plan_id'] : null,
            'ends_at'                 => $sub->current_period_end
                ? \Carbon\Carbon::createFromTimestamp($sub->current_period_end)
                : null,
            'override_status'         => $forceStatus,
        ];
    }

    private function mapInvoiceEvent(object $event, string $status): array
    {
        $invoice = $event->data->object;

        return [
            'event'                   => $event->type,
            'gateway_subscription_id' => $invoice->subscription,
            'gateway_status'          => $status,
            'stripe_customer_id'      => $invoice->customer,
            'plan_id'                 => null,
            'ends_at'                 => null,
            'override_status'         => $status,
        ];
    }
}
