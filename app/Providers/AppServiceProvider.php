<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Volt\Volt;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureRateLimits();
        $this->configureViewNamespaces();
        $this->configureVolt();
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Register view namespaces for the starter kit's Blade components.
     * - layouts:: → resources/views/layouts (x-layouts::auth, x-layouts::app)
     * - pages:: → resources/views/pages (Fortify auth views)
     */
    protected function configureViewNamespaces(): void
    {
        View::addNamespace('pages', resource_path('views/pages'));

        // Register layouts as an anonymous component path with prefix
        Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');
        Blade::anonymousComponentPath(resource_path('views/pages'), 'pages');
    }

    protected function configureRateLimits(): void
    {
        RateLimiter::for('kirada-webhooks', fn (Request $request) => [
            Limit::perMinute(30)->by($request->ip()),
        ]);

        RateLimiter::for('kirada-public-links', fn (Request $request) => [
            Limit::perMinute(20)->by($request->ip()),
        ]);

        RateLimiter::for('kirada-authenticated-actions', fn (Request $request) => [
            Limit::perMinute(120)->by((string) ($request->user()?->id ?? $request->ip())),
        ]);
    }

    /**
     * Mount Volt for the starter kit's settings/auth pages.
     * Kirada feature modules use pure Livewire class components.
     */
    protected function configureVolt(): void
    {
        Volt::mount(resource_path('views'));
    }
}
