<?php

namespace App\Filament\Pages;

use App\Models\AuditEvent;
use App\Models\Lease;
use App\Models\NotificationDelivery;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class SystemHealth extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-server';

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'System Health';

    protected string $view = 'filament.pages.system-health';

    public function getViewData(): array
    {
        return [
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug') ? 'Enabled' : 'Disabled',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'db_connection' => config('database.default'),
            'db_name' => config('database.connections.'.config('database.default').'.database'),
            'queue_connection' => config('queue.default'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'mail_mailer' => config('mail.default'),

            // Sensitive values — masked
            'redis_enabled' => config('database.redis.client') !== null ? 'Yes' : 'No',
            'telescope_enabled' => config('telescope.enabled') ? 'Yes' : 'No',
            'horizon_enabled' => config('horizon.enabled') ? 'Yes' : 'No',
            'sanctum_enabled' => config('sanctum.middleware.encrypt_cookies') !== null ? 'Yes' : 'No',

            // Health metrics
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'pending_jobs' => DB::table('jobs')->count(),
            'recent_audit_events' => AuditEvent::latest()->limit(10)->get(),

            // Delivery health
            'delivery_success_rate' => $this->deliverySuccessRate(),
            'recent_delivery_failures' => NotificationDelivery::where('status', 'failed')
                ->where('created_at', '>=', now()->subDays(7))
                ->count(),

            // Data summary
            'total_users' => User::count(),
            'total_tenants' => Tenant::count(),
            'active_leases' => Lease::where('status', 'active')->count(),
            'occupied_units' => Unit::where('status', 'occupied')->count(),
            'vacant_units' => Unit::where('status', 'vacant')->count(),
            'active_subscriptions' => Subscription::whereIn('status', ['trialing', 'active'])->count(),
        ];
    }

    private function deliverySuccessRate(): float
    {
        $total = NotificationDelivery::where('created_at', '>=', now()->subDays(30))->count();
        if ($total === 0) {
            return 100.0;
        }

        $successful = NotificationDelivery::where('status', 'delivered')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return round(($successful / $total) * 100, 1);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
