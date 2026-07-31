<?php

namespace App\Livewire\RentInvoices;

use App\Models\RentInvoice;
use App\Services\InvoiceDeliveryService;
use App\Services\NotificationChannelResolver;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DeliveryHistory extends Component
{
    public RentInvoice $invoice;

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

    public function send(InvoiceDeliveryService $delivery): void
    {
        $this->authorize('update', $this->invoice);

        if (in_array($this->invoice->status, ['paid', 'cancelled'], true)) {
            $this->addError('delivery', __('Paid or cancelled invoices cannot be sent.'));

            return;
        }

        $records = $delivery->dispatch($this->invoice, 'manual_send', auth()->user());
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
