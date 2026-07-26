<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="kirada-app-main">
        <div class="kirada-app-content">
            {{ $slot }}
        </div>
        <x-app-footer />
    </flux:main>
</x-layouts::app.sidebar>