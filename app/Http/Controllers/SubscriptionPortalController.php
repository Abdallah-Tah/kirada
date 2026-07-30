<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;

class SubscriptionPortalController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isLandlord(), 403);

        if (! $request->user()->hasStripeId()) {
            return redirect()
                ->route('subscription.status')
                ->with('error', __('Complete a card subscription before opening the billing portal.'));
        }

        try {
            return $request->user()->redirectToBillingPortal(route('subscription.status'));
        } catch (ApiErrorException $exception) {
            Log::warning('Stripe billing portal could not start.', [
                'user_id' => $request->user()->id,
                'exception' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('subscription.status')
                ->with('error', __('The billing portal is temporarily unavailable. Please try again later.'));
        }
    }
}
