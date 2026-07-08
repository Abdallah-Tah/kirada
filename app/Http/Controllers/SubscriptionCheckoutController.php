<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Handles subscription checkout initiation and Stripe return redirects.
 *
 * Waafi inline payments are handled directly in the Livewire Status component
 * (via a Livewire action) because they need real-time UI feedback.
 */
class SubscriptionCheckoutController extends Controller
{
    public function __construct(private readonly SubscriptionService $subscriptions) {}

    /**
     * Initiate a checkout for a given plan and gateway.
     * Stripe → redirect to Checkout URL.
     * Waafi / CAC Bank → return JSON (handled by the Livewire component).
     */
    public function initiate(Request $request, string $planSlug, string $gateway): RedirectResponse|JsonResponse
    {
        $request->validate([
            'phone' => 'nullable|string|max:20',
        ]);

        $plan = Plan::active()->where('slug', $planSlug)->firstOrFail();
        $user = $request->user();

        abort_unless($user->isLandlord(), 403);

        try {
            $result = $this->subscriptions->initiateCheckout($user, $plan, $gateway, $request->only('phone'));
        } catch (\RuntimeException|\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return redirect()->route('subscription.status')->with('error', $e->getMessage());
        }

        if ($result['type'] === 'redirect') {
            return redirect()->away($result['url']);
        }

        // inline gateways (Waafi approved, CAC Bank instructions)
        return response()->json($result['data']);
    }
}
