<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restrict Filament Admin Panel access to users with the Spatie 'admin' role.
 * Non-admin authenticated users receive a 403 Forbidden response.
 * Guests are redirected to the login page by Filament's Authenticate middleware.
 */
class FilamentAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('admin')) {
            abort(403, 'Unauthorized. Admin access required.');
        }

        return $next($request);
    }
}
