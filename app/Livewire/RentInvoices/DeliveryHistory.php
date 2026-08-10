<?php

namespace App\Livewire\RentInvoices;

use App\Models\LandlordNotificationSetting;
use App\Models\RentInvoice;
use App\Services\InvoiceDeliveryService;
use App\Services\NotificationChannelResolver;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DeliveryHistory extends Component
{
    public RentInvoice $invoice;

    /**
     * Channels the landlord picked for this send. Pre-checked with whatever the
     * lease/landlord settings resolve to, but overridable per send.
     *
     * @var array<int, string>
     */
    public array $channels = [];

    public function mount(RentInvoice $rentInvoice): void
    {
        $this->authorize('update', $rentInvoice);

        $this->invoice = $rentInvoice->load([
            'tenant.user',
            'property',
            'unit',
            'lease',
            'landlord.notificationSetting',
        ]);

        $this->channels = $this->resolvedChannels;
    }

    #[Computed]
    public function deliveries()
    {
        return $this->invoice->deliveries()
            ->latest()
            ->get();
    }

    #[Computed]
    public function resolvedChannels(): array
    {
        return app(NotificationChannelResolver::class)->channels($this->invoice);
    }

    /**
     * Every channel the landlord may pick, with the reason it cannot be used.
     *
     * @return array<int, array{value: string, label: string, available: bool, hint: string|null}>
     */
    #[Computed]
    public function channelOptions(): array
    {
        $resolver = app(NotificationChannelResolver::class);

        return [
            [
                'value' => LandlordNotificationSetting::CHANNEL_EMAIL,
                'label' => __('Email'),
                'available' => (bool) $resolver->emailRecipient($this->invoice),
                'hint' => $resolver->emailRecipient($this->invoice)
                    ? null
                    : __('No email address on file for this tenant.'),
            ],
            [
                'value' => LandlordNotificationSetting::CHANNEL_WHATSAPP,
                'label' => __('WhatsApp'),
                'available' => (bool) $resolver->whatsAppRecipient($this->invoice),
                'hint' => $resolver->whatsAppRecipient($this->invoice)
                    ? null
                    : ($this->invoice->tenant?->hasWhatsAppConsent()
                        ? __('No WhatsApp number on file for this tenant.')
                        : __('Tenant has not opted in to WhatsApp.')),
            ],
        ];
    }

    public function send(InvoiceDeliveryService $delivery): void
    {
        $this->authorize('update', $this->invoice);

        if (in_array($this->invoice->status, ['paid', 'cancelled'], true)) {
            $this->addError('delivery', __('Paid or cancelled invoices cannot be sent.'));

            return;
        }

        // Keep only channels the app actually knows how to send on; an empty
        // selection would otherwise silently fall back to the resolved defaults.
        $selected = array_values(array_intersect(
            LandlordNotificationSetting::CHANNELS,
            array_filter($this->channels, 'is_string'),
        ));

        if ($selected === []) {
            $this->addError('delivery', __('Select at least one channel to send on.'));

            return;
        }

        $records = $delivery->dispatch($this->invoice, 'manual_send', auth()->user(), $selected);
        unset($this->deliveries);

        if ($records->every(fn ($record) => $record->status === 'skipped')) {
            $this->addError('delivery', __('No selected channel has an eligible recipient.'));

            return;
        }

        Flux::toast(variant: 'success', text: __('Invoice delivery queued.'));
    }

    public function render()
    {
        return view('livewire.rent-invoices.delivery-history')
            ->layout('layouts.app')
            ->title(__('Invoice delivery'));
    }
}
