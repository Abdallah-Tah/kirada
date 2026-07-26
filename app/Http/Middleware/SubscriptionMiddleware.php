<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionMiddleware
{
    /**
     * Ensure the landlord has an active subscription or trial.
     * Not applied globally yet — will be used later to protect
     * landlord business routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Only the landlord portal uses a landlord account subscription.
        if (! $user->canAccessLandlordPortal()) {
            return $next($request);
        }

        $landlord = $user->landlordAccount();

        if ($landlord && ($landlord->onTrial() || $landlord->hasActiveSubscription())) {
            return $next($request);
        }

        // Trial expired or no subscription — redirect to subscription page
        return redirect()->route('subscription.status')
            ->with('warning', 'Your trial has expired. Please choose a plan to continue.');
    }
}
