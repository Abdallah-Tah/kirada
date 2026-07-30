<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;

/**
 * Starts secure Stripe Checkout for the landlord's Kirada plan.
 * This controller never processes tenant rent.
 */
class SubscriptionCheckoutController extends Controller
{
    public function __construct(private readonly SubscriptionService $subscriptions) {}

    /**
     * Initiate a checkout for a given plan and gateway.
     * Redirect the landlord to the Stripe Checkout URL.
     */
    public function initiate(Request $request, string $planSlug): RedirectResponse
    {
        $plan = Plan::active()->where('slug', $planSlug)->firstOrFail();
        $user = $request->user();

        abort_unless($user->isLandlord(), 403);

        $existing = $user->subscription('default');
        if ($existing && ! $existing->ended()) {
            return redirect()
                ->route('subscription.status')
                ->with('error', __('You already have a Stripe subscription. Use the billing portal to manage it.'));
        }

        try {
            $result = $this->subscriptions->initiateCheckout($user, $plan, 'stripe');
        } catch (ApiErrorException|\RuntimeException|\InvalidArgumentException $exception) {
            Log::warning('Kirada subscription checkout could not start.', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'exception' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('subscription.status')
                ->with('error', __('Card billing is temporarily unavailable. Please try again later or contact Kirada support.'));
        }

        if ($result['type'] === 'redirect') {
            return redirect()->away($result['url']);
        }

        return redirect()->route('subscription.status');
    }
}
