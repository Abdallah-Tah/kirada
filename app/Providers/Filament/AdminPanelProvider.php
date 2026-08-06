<?php

namespace App\Providers\Filament;

use App\Http\Middleware\FilamentAdminAccess;
use App\Http\Middleware\SecurityHeadersMiddleware;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Kirada Admin Filament Panel.
 *
 * Access is restricted to users with the Spatie 'admin' role via
 * FilamentAdminAccess middleware. Non-admin users receive 403 Forbidden.
 */
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        RateLimiter::for('admin-panel', fn ($request) => Limit::perMinute(60)
            ->by(optional($request->user())->id ?: $request->ip()));

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->emailVerification()
            ->profile()
            ->colors([
                'primary' => Color::Indigo,
                'danger' => Color::Red,
                'gray' => Color::Zinc,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'info' => Color::Blue,
            ])
            ->font('Inter')
            ->spa()
            ->sidebarCollapsibleOnDesktop()
            ->darkMode(true)
            ->brandName('Kirada Admin')
            ->navigationGroups([
                NavigationGroup::make('Dashboard'),
                NavigationGroup::make('Portfolio'),
                NavigationGroup::make('Rent & Payments'),
                NavigationGroup::make('People'),
                NavigationGroup::make('Maintenance'),
                NavigationGroup::make('Communications'),
                NavigationGroup::make('Billing'),
                NavigationGroup::make('System'),
            ])
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SecurityHeadersMiddleware::class,
                'throttle:admin-panel',
            ])
            ->authMiddleware([
                Authenticate::class,
                FilamentAdminAccess::class,
            ])
            ->authGuard('web')
            ->tenantMenu(false);
    }
}
