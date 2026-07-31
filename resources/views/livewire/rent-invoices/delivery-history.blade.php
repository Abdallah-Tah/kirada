<div>
    <div class="kirada-page-header kirada-reveal flex flex-wrap items-start justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('Invoice delivery') }}</flux:heading>
            <flux:subheading>{{ $invoice->invoice_number }} — {{ $invoice->tenant?->full_name }}</flux:subheading>
        </div>
        <div class="flex flex-wrap gap-2">
            <flux:button :href="route('rent-invoices.pdf', $invoice)" variant="outline" icon="arrow-down-tray">
                {{ __('Download PDF') }}
            </flux:button>
            @can('update', $invoice)
                <flux:button
                    wire:click="send"
                    variant="primary"
                    icon="paper-airplane"
                    data-confirm="{{ __('Send this invoice through the configured channels?') }}"
                    data-confirm-title="{{ __('Send invoice') }}"
                    data-confirm-button="{{ __('Send invoice') }}"
                >
                    {{ __('Send invoice') }}
                </flux:button>
            @endcan
        </div>
    </div>

    <flux:error name="delivery" />

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <div class="kirada-form-card">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Resolved channels') }}</div>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($this->resolvedChannels as $channel)
                    <flux:badge color="{{ $channel === 'whatsapp' ? 'green' : 'sky' }}">
                        {{ __(ucfirst($channel)) }}
                    </flux:badge>
                @endforeach
            </div>
        </div>
        <div class="kirada-form-card">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('WhatsApp consent') }}</div>
            <div class="mt-3">
                <flux:badge color="{{ $invoice->tenant?->hasWhatsAppConsent() ? 'green' : 'amber' }}">
                    {{ $invoice->tenant?->hasWhatsAppConsent() ? __('Opted in') : __('Not opted in') }}
                </flux:badge>
            </div>
        </div>
        <div class="kirada-form-card">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Invoice status') }}</div>
            <div class="mt-3 font-semibold text-slate-900 dark:text-white">{{ __(ucfirst($invoice->status)) }}</div>
        </div>
    </div>

    <div class="kirada-table-card mt-6">
        <table class="w-full text-left text-sm">
            <thead>
                <tr>
                    <th class="px-4 py-3">{{ __('Event') }}</th>
                    <th class="px-4 py-3">{{ __('Channel') }}</th>
                    <th class="px-4 py-3">{{ __('Recipient') }}</th>
                    <th class="px-4 py-3">{{ __('Status') }}</th>
                    <th class="px-4 py-3">{{ __('Attempts') }}</th>
                    <th class="px-4 py-3">{{ __('Updated') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($this->deliveries as $delivery)
                    @php
                        $statusColor = match ($delivery->status) {
                            'sent', 'delivered', 'read' => 'green',
                            'failed' => 'red',
                            'skipped', 'retrying' => 'amber',
                            default => 'sky',
                        };
                    @endphp
                    <tr>
                        <td data-label="{{ __('Event') }}" class="px-4 py-3">{{ __(str_replace('_', ' ', ucfirst($delivery->event))) }}</td>
                        <td data-label="{{ __('Channel') }}" class="px-4 py-3">{{ __(ucfirst($delivery->channel)) }}</td>
                        <td data-label="{{ __('Recipient') }}" class="px-4 py-3 font-mono text-xs">{{ $delivery->recipient_masked ?: '—' }}</td>
                        <td data-label="{{ __('Status') }}" class="px-4 py-3">
                            <flux:badge size="sm" color="{{ $statusColor }}">{{ __(ucfirst($delivery->status)) }}</flux:badge>
                            @if ($delivery->error_message)
                                <div class="mt-1 max-w-xs text-xs text-red-600 dark:text-red-400">{{ $delivery->error_message }}</div>
                            @endif
                        </td>
                        <td data-label="{{ __('Attempts') }}" class="px-4 py-3">{{ $delivery->attempts }}</td>
                        <td data-label="{{ __('Updated') }}" class="px-4 py-3 text-slate-500">{{ $delivery->updated_at?->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-slate-500">
                            {{ __('This invoice has not been sent yet.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
