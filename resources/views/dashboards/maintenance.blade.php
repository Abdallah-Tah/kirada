<x-layouts::app :title="__('Maintenance Dashboard')">
    <flux:main class="kirada-shell">
        <div class="kirada-page-header">
            <flux:heading size="xl" class="text-slate-950">{{ __('Maintenance Dashboard') }}</flux:heading>
            <flux:subheading class="mt-1 text-slate-500">{{ __('Assigned work orders, active jobs, and recent resolutions.') }}</flux:subheading>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-3">
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Assigned Open') }}</p>
                <p class="kirada-stat-value text-sky-600">{{ $assigned_open }}</p>
            </div>
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('In Progress') }}</p>
                <p class="kirada-stat-value text-amber-600">{{ $in_progress }}</p>
            </div>
            <div class="kirada-stat-card">
                <p class="kirada-stat-label">{{ __('Resolved This Month') }}</p>
                <p class="kirada-stat-value text-emerald-600">{{ $resolved_this_month }}</p>
            </div>
        </div>

        @if($recent_assigned->isNotEmpty())
        <div class="mt-6 kirada-card">
            <h3 class="font-semibold text-slate-950">{{ __('Recent Assigned Requests') }}</h3>
            <div class="mt-4 divide-y divide-slate-100">
                @foreach($recent_assigned as $request)
                    <div class="flex items-center justify-between gap-4 py-3 text-sm">
                        <span class="font-medium text-slate-800">{{ $request->title }}</span>
                        <span class="text-right text-slate-500">
                            {{ $request->property?->name }}
                            @if($request->unit) — {{ $request->unit->unit_number }} @endif
                            · {{ ucfirst($request->status) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
        @else
            <div class="mt-6 kirada-card">
                <p class="text-sm text-slate-500">{{ __('No assigned requests yet.') }}</p>
            </div>
        @endif
    </flux:main>
</x-layouts::app>
