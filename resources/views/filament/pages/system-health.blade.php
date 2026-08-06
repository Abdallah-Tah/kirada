@php
    $data = $this->getViewData();
@endphp

<div class="space-y-6">
    {{-- Environment & Infrastructure --}}
    <x-filament::section>
        <x-slot name="heading">Environment</x-slot>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">App Environment</p>
                <p class="mt-1 text-lg font-semibold">{{ $data['app_env'] }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Debug Mode</p>
                <p class="mt-1 text-lg font-semibold">{{ $data['app_debug'] }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">PHP Version</p>
                <p class="mt-1 text-lg font-semibold">{{ $data['php_version'] }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Laravel Version</p>
                <p class="mt-1 text-lg font-semibold">{{ $data['laravel_version'] }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Database</p>
                <p class="mt-1 text-lg font-semibold">{{ $data['db_connection'] }}: {{ $data['db_name'] }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Queue</p>
                <p class="mt-1 text-lg font-semibold">{{ $data['queue_connection'] }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Cache</p>
                <p class="mt-1 text-lg font-semibold">{{ $data['cache_driver'] }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Session</p>
                <p class="mt-1 text-lg font-semibold">{{ $data['session_driver'] }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Mail Mailer</p>
                <p class="mt-1 text-lg font-semibold">{{ $data['mail_mailer'] }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Redis</p>
                <p class="mt-1 text-lg font-semibold">{{ $data['redis_enabled'] }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Telescope</p>
                <p class="mt-1 text-lg font-semibold">{{ $data['telescope_enabled'] }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Horizon</p>
                <p class="mt-1 text-lg font-semibold">{{ $data['horizon_enabled'] }}</p>
            </div>
        </div>
    </x-filament::section>

    {{-- Queue Health --}}
    <x-filament::section>
        <x-slot name="heading">Queue Health</x-slot>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Jobs</p>
                <p class="mt-1 text-lg font-semibold {{ $data['pending_jobs'] > 100 ? 'text-red-600' : '' }}">{{ number_format($data['pending_jobs']) }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Failed Jobs</p>
                <p class="mt-1 text-lg font-semibold {{ $data['failed_jobs'] > 0 ? 'text-red-600' : 'text-green-600' }}">{{ number_format($data['failed_jobs']) }}</p>
            </div>
        </div>
    </x-filament::section>

    {{-- Delivery Health --}}
    <x-filament::section>
        <x-slot name="heading">Delivery Health (30 days)</x-slot>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Success Rate</p>
                <p class="mt-1 text-lg font-semibold {{ $data['delivery_success_rate'] < 90 ? 'text-red-600' : 'text-green-600' }}">{{ $data['delivery_success_rate'] }}%</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Recent Failures (7d)</p>
                <p class="mt-1 text-lg font-semibold {{ $data['recent_delivery_failures'] > 0 ? 'text-red-600' : 'text-green-600' }}">{{ number_format($data['recent_delivery_failures']) }}</p>
            </div>
        </div>
    </x-filament::section>

    {{-- Data Summary --}}
    <x-filament::section>
        <x-slot name="heading">Data Summary</x-slot>
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</p>
                <p class="mt-1 text-lg font-semibold">{{ number_format($data['total_users']) }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Tenants</p>
                <p class="mt-1 text-lg font-semibold">{{ number_format($data['total_tenants']) }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Leases</p>
                <p class="mt-1 text-lg font-semibold">{{ number_format($data['active_leases']) }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Occupied / Vacant</p>
                <p class="mt-1 text-lg font-semibold">{{ $data['occupied_units'] }} / {{ $data['vacant_units'] }}</p>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Subscriptions</p>
                <p class="mt-1 text-lg font-semibold">{{ number_format($data['active_subscriptions']) }}</p>
            </div>
        </div>
    </x-filament::section>

    {{-- Recent Audit Events --}}
    <x-filament::section>
        <x-slot name="heading">Recent Audit Events</x-slot>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Actor</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Event</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($data['recent_audit_events'] as $event)
                        <tr>
                            <td class="px-4 py-3 text-sm">{{ $event->actor?->name ?? 'System' }}</td>
                            <td class="px-4 py-3 text-sm">{{ $event->event }}</td>
                            <td class="px-4 py-3 text-sm">{{ $event->auditable_type }}</td>
                            <td class="px-4 py-3 text-sm">{{ $event->created_at?->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-filament::section>
</div>
