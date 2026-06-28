<?php

namespace App\Livewire\RentInvoices;

use App\Models\Lease;
use App\Models\RentInvoice;
use App\Services\RentInvoiceService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Edit extends Component
{
    public RentInvoice $invoice;
    public ?int $lease_id = null;
    public ?int $property_id = null;
    public ?int $unit_id = null;
    public ?int $tenant_id = null;
    public string $invoice_month = '';
    public string $due_date = '';
    public string $amount = '';
    public string $status = 'draft';
    public ?string $notes = null;

    public function mount(RentInvoice $invoice): void
    {
        $this->authorize('update', $invoice);

        $this->invoice = $invoice;
        $this->fill($invoice->only([
            'lease_id', 'property_id', 'unit_id', 'tenant_id',
            'amount', 'status', 'notes',
        ]));

        $this->invoice_month = $invoice->invoice_month?->format('Y-m-d') ?? '';
        $this->due_date = $invoice->due_date?->format('Y-m-d') ?? '';
        $this->amount = (string) $invoice->amount;
    }

    protected function rules(): array
    {
        return [
            'lease_id'      => 'required|exists:leases,id',
            'property_id'   => 'required|exists:properties,id',
            'unit_id'       => 'required|exists:units,id',
            'tenant_id'     => 'required|exists:tenants,id',
            'invoice_month' => 'required|date',
            'due_date'      => 'required|date',
            'amount'        => 'required|numeric|min:0|max:99999999',
            'status'        => 'required|in:draft,unpaid,partially_paid,paid,overdue,cancelled',
            'notes'         => 'nullable|string|max:2000',
        ];
    }

    #[Computed]
    public function leases()
    {
        $query = Lease::with(['property:id,name', 'unit:id,unit_number', 'tenant:id,first_name,last_name'])
            ->select('id', 'property_id', 'unit_id', 'tenant_id', 'monthly_rent', 'payment_due_day')
            ->latest();

        if (auth()->user()->hasRole('landlord')) {
            $query->forLandlord(auth()->id());
        }

        return $query->get();
    }

    public function save(): void
    {
        $this->authorize('update', $this->invoice);

        $validated = $this->validate();

        // Ensure landlord ownership
        if (auth()->user()->hasRole('landlord')) {
            $lease = Lease::find($validated['lease_id']);
            abort_if($lease->landlord_id !== auth()->id(), 403);
        }

        app(RentInvoiceService::class)->updateInvoice($this->invoice, $validated);

        \Flux\Flux::toast('Invoice updated successfully.', 'success');

        $this->redirect(route('rent-invoices.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.rent-invoices.edit')
            ->layout('layouts.app')
            ->title(__('Edit Invoice'));
    }
}
