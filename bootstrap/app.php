<?php

use App\Http\Middleware\LocaleMiddleware;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\SubscriptionMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Behind a reverse proxy (Cloudflare Tunnel on loopback, or Nginx): trust it
        // so X-Forwarded-Proto=https is honored and asset()/url() emit https URLs.
        // Prevents mixed-content (http) asset URLs that break images over the tunnel.
        //
        // TRUSTED_PROXIES must name the actual edge — a public origin that trusts '*'
        // lets any client spoof X-Forwarded-For (defeating the IP-keyed throttles) and
        // X-Forwarded-Proto (faking $request->isSecure()). Defaults to loopback.
        $proxies = env('TRUSTED_PROXIES', '127.0.0.1,::1');

        $middleware->trustProxies(at: $proxies === '*' ? '*' : array_values(array_filter(
            array_map('trim', explode(',', (string) $proxies)),
        )), headers: Request::HEADER_X_FORWARDED_FOR |
            Request::HEADER_X_FORWARDED_HOST |
            Request::HEADER_X_FORWARDED_PORT |
            Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->web(append: [
            LocaleMiddleware::class,
            SecurityHeadersMiddleware::class,
        ]);

        // Webhooks authenticate with signatures, not sessions.
        $middleware->validateCsrfTokens(except: [
            'webhooks/payments/*',
            'stripe/*',
        ]);

        // The shell every authenticated feature route sits behind. Centralised so
        // the rate limit can't be forgotten on a new route group — 120/min per
        // user covers page loads and form posts (Livewire registers its own
        // /livewire/update route, which this does not cover).
        $middleware->group('kirada-auth', [
            'auth',
            'verified',
            'throttle:kirada-authenticated-actions',
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'subscription' => SubscriptionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
