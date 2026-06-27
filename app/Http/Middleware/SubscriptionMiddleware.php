<?php

namespace App\Http\Middleware;

use App\Models\User;
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

        if (!$user) {
            return $next($request);
        }

        // Only landlords need subscriptions
        if (!$user->isLandlord()) {
            return $next($request);
        }

        // Allow if on trial or has active subscription
        if ($user->onTrial() || $user->hasActiveSubscription()) {
            return $next($request);
        }

        // Trial expired or no subscription — redirect to subscription page
        return redirect()->route('subscription.status')
            ->with('warning', 'Your trial has expired. Please choose a plan to continue.');
    }
}