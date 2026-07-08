<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use App\Services\SubscriptionGateways\StripeGateway;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly StripeGateway $stripe,
        private readonly SubscriptionService $subscriptions,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        if (! $this->stripe->verifyWebhook($request)) {
            return response()->json(['message' => 'Invalid signature.'], 403);
        }

        $event = $this->stripe->parseWebhook($request);

        if (! $event) {
            // Unsupported event type — acknowledge silently
            return response()->json(['message' => 'ignored']);
        }

        try {
            $this->handle($event);
        } catch (\Throwable $e) {
            Log::error('Stripe webhook handler failed', [
                'event'     => $event['event'] ?? null,
                'exception' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Handler error.'], 500);
        }

        return response()->json(['message' => 'ok']);
    }

    // ── Event handlers ───────────────────────────────────────────────────────

    private function handle(array $event): void
    {
        match (true) {
            str_starts_with($event['event'], 'customer.subscription') => $this->handleSubscription($event),
            str_starts_with($event['event'], 'invoice.')              => $this->handleInvoice($event),
            default                                                    => null,
        };
    }

    private function handleSubscription(array $event): void
    {
        $user = $this->resolveUserByCustomer($event['stripe_customer_id'] ?? null);

        if (! $user) {
            return;
        }

        $sub = Subscription::where('user_id', $user->id)->first()
            ?? new Subscription(['user_id' => $user->id]);

        $overrideStatus = $event['override_status'] ?? null;

        $kiradaStatus = match ($overrideStatus ?? $event['gateway_status']) {
            'active', 'trialing' => $overrideStatus ?? 'active',
            'cancelled', 'canceled' => 'cancelled',
            'past_due'              => 'past_due',
            default                 => 'expired',
        };

        $sub->fill([
            'plan_id'                 => $event['plan_id'] ?? $sub->plan_id,
            'status'                  => $kiradaStatus,
            'gateway'                 => 'stripe',
            'payment_method'          => 'stripe',
            'gateway_subscription_id' => $event['gateway_subscription_id'] ?? $sub->gateway_subscription_id,
            'gateway_status'          => $event['gateway_status'] ?? null,
            'starts_at'               => $sub->starts_at ?? now(),
            'ends_at'                 => $event['ends_at'] ?? $sub->ends_at,
        ]);

        $sub->save();
    }

    private function handleInvoice(array $event): void
    {
        if (! ($event['gateway_subscription_id'] ?? null)) {
            return;
        }

        $sub = Subscription::where('gateway_subscription_id', $event['gateway_subscription_id'])->first();

        if (! $sub) {
            return;
        }

        if (($event['override_status'] ?? null) === 'past_due') {
            $sub->update(['status' => 'past_due', 'gateway_status' => 'past_due']);
        }
    }

    private function resolveUserByCustomer(?string $customerId): ?User
    {
        if (! $customerId) {
            return null;
        }

        return User::where('stripe_customer_id', $customerId)->first();
    }
}
