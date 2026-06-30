<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('Reports') }}</flux:heading>
        <flux:subheading>{{ __('Portfolio overview and financial summary') }}</flux:subheading>
    </div>

    {{-- Summary Cards --}}
    <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        {{-- Properties --}}
        <div class="kirada-card">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-kirada-ocean/10">
                    <flux:icon.building-office class="h-5 w-5 text-kirada-ocean" />
                </div>
                <div>
                    <p class="text-sm text-zinc-500">{{ __('Properties') }}</p>
                    <p class="text-2xl font-bold">{{ $this->summary['properties'] }}</p>
                </div>
            </div>
        </div>

        {{-- Occupancy --}}
        <div class="kirada-card">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-100">
                    <flux:icon.home-modern class="h-5 w-5 text-green-600" />
                </div>
                <div>
                    <p class="text-sm text-zinc-500">{{ __('Occupancy') }}</p>
                    <p class="text-2xl font-bold">{{ $this->summary['occupancy_rate'] }}%</p>
                    <p class="text-xs text-zinc-400">{{ $this->summary['occupied_units'] }}/{{ $this->summary['units'] }} {{ __('units') }}</p>
                </div>
            </div>
        </div>

        {{-- Collection Rate --}}
        <div class="kirada-card">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100">
                    <flux:icon.banknotes class="h-5 w-5 text-blue-600" />
                </div>
                <div>
                    <p class="text-sm text-zinc-500">{{ __('Collection Rate') }}</p>
                    <p class="text-2xl font-bold">{{ $this->summary['collection_rate'] }}%</p>
                </div>
            </div>
        </div>

        {{-- Open Maintenance --}}
        <div class="kirada-card">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-100">
                    <flux:icon.wrench-screwdriver class="h-5 w-5 text-orange-600" />
                </div>
                <div>
                    <p class="text-sm text-zinc-500">{{ __('Open Maintenance') }}</p>
                    <p class="text-2xl font-bold">{{ $this->summary['maintenance_open'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Financial Summary --}}
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="kirada-card">
            <h3 class="mb-4 font-semibold text-zinc-900 dark:text-white">{{ __('Financial Summary') }}</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <span class="text-sm text-zinc-500">{{ __('Total Invoiced') }}</span>
                    <span class="font-semibold">{{ number_format($this->summary['total_invoiced'], 2) }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <span class="text-sm text-zinc-500">{{ __('Total Collected') }}</span>
                    <span class="font-semibold text-green-600">{{ number_format($this->summary['total_collected'], 2) }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <span class="text-sm text-zinc-500">{{ __('Outstanding') }}</span>
                    <span class="font-semibold text-orange-600">{{ number_format($this->summary['outstanding'], 2) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-zinc-500">{{ __('Overdue') }}</span>
                    <span class="font-semibold text-red-600">{{ number_format($this->summary['overdue'], 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Portfolio Stats --}}
        <div class="kirada-card">
            <h3 class="mb-4 font-semibold text-zinc-900 dark:text-white">{{ __('Portfolio') }}</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <span class="text-sm text-zinc-500">{{ __('Active Leases') }}</span>
                    <span class="font-semibold">{{ $this->summary['active_leases'] }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <span class="text-sm text-zinc-500">{{ __('Total Tenants') }}</span>
                    <span class="font-semibold">{{ $this->summary['tenants'] }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 dark:border-zinc-800">
                    <span class="text-sm text-zinc-500">{{ __('Maintenance Resolved') }}</span>
                    <span class="font-semibold text-green-600">{{ $this->summary['maintenance_resolved'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-zinc-500">{{ __('Maintenance Open') }}</span>
                    <span class="font-semibold text-orange-600">{{ $this->summary['maintenance_open'] }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Rent Collection Chart --}}
    <div class="mt-6 kirada-card">
        <h3 class="mb-4 font-semibold text-zinc-900 dark:text-white">{{ __('Rent Collection (Last 6 Months)') }}</h3>
        <div class="flex items-end gap-3" style="height: 200px;">
            @foreach ($this->rentChart as $bar)
                <div class="flex flex-1 flex-col items-center gap-2">
                    <div class="flex w-full flex-col gap-1" style="height: 160px;">
                        @php $maxVal = max(array_column($this->rentChart, 'invoiced') ?: [1]); @endphp
                        <div class="flex w-full flex-1 items-end">
                            <div class="w-full rounded-t bg-kirada-ocean/40" style="height: {{ $maxVal > 0 ? ($bar['invoiced'] / $maxVal) * 100 : 0 }}%"></div>
                        </div>
                        <div class="flex w-full flex-1 items-end">
                            <div class="w-full rounded-t bg-kirada-green" style="height: {{ $maxVal > 0 ? ($bar['collected'] / $maxVal) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                    <span class="text-xs text-zinc-500">{{ $bar['label'] }}</span>
                </div>
            @endforeach
        </div>
        <div class="mt-4 flex items-center gap-6">
            <div class="flex items-center gap-2">
                <div class="h-3 w-3 rounded bg-kirada-ocean/40"></div>
                <span class="text-xs text-zinc-500">{{ __('Invoiced') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="h-3 w-3 rounded bg-kirada-green"></div>
                <span class="text-xs text-zinc-500">{{ __('Collected') }}</span>
            </div>
        </div>
    </div>

    {{-- Maintenance Breakdown --}}
    <div class="mt-6 kirada-card">
        <h3 class="mb-4 font-semibold text-zinc-900 dark:text-white">{{ __('Maintenance Breakdown') }}</h3>
        @php $maintenance = $this->maintenanceBreakdown; $totalMaintenance = array_sum($maintenance) ?: 1; @endphp
        <div class="space-y-3">
            @foreach (['open' => 'red', 'in_progress' => 'orange', 'resolved' => 'green', 'cancelled' => 'zinc'] as $status => $color)
                @php $count = $maintenance[$status] ?? 0; @endphp
                <div class="flex items-center gap-3">
                    <span class="w-24 text-sm capitalize text-zinc-500">{{ __(str_replace('_', ' ', $status)) }}</span>
                    <div class="flex-1 rounded-full bg-zinc-100 dark:bg-zinc-800" style="height: 8px;">
                        <div class="rounded-full bg-{{ $color }}-500" style="height: 8px; width: {{ ($count / $totalMaintenance) * 100 }}%"></div>
                    </div>
                    <span class="w-8 text-right text-sm font-medium">{{ $count }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Top Properties by Outstanding --}}
    @if (!empty($this->topPropertiesByOutstanding))
    <div class="mt-6 kirada-card">
        <h3 class="mb-4 font-semibold text-zinc-900 dark:text-white">{{ __('Top Properties by Outstanding Rent') }}</h3>
        <div class="space-y-3">
            @foreach ($this->topPropertiesByOutstanding as $property)
                <div class="flex items-center justify-between border-b border-zinc-100 pb-3 last:border-0 dark:border-zinc-800">
                    <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $property['name'] }}</span>
                    <span class="font-semibold text-orange-600">{{ number_format($property['outstanding'], 2) }}</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>