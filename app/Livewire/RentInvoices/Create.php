<?php

namespace App\Livewire\RentInvoices;

use App\Models\Lease;
use App\Models\RentInvoice;
use App\Services\RentInvoiceService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Create extends Component
{
    public ?int $lease_id = null;
    public ?int $property_id = null;
    public ?int $unit_id = null;
    public ?int $tenant_id = null;
    public string $invoice_month = '';
    public string $due_date = '';
    public string $amount = '';
    public string $status = 'draft';
    public ?string $notes = null;

    // Auto-filled display fields (read-only)
    public ?string $property_name = null;
    public ?string $unit_number = null;
    public ?string $tenant_name = null;

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
            ->select('id', 'property_id', 'unit_id', 'tenant_id', 'monthly_rent', 'payment_due_day', 'start_date', 'end_date')
            ->latest();

        if (auth()->user()->hasRole('landlord')) {
            $query->forLandlord(auth()->id());
        }

        return $query->get();
    }

    /**
     * When a lease is selected, auto-fill property, unit, tenant, amount, and due date.
     */
    public function updatedLeaseId(): void
    {
        if (!$this->lease_id) {
            return;
        }

        $lease = $this->leases->firstWhere('id', $this->lease_id);

        if (!$lease) {
            return;
        }

        $data = app(RentInvoiceService::class)->dataFromLease($lease, $this->invoice_month ?: null);

        $this->property_id = $data['property_id'];
        $this->unit_id = $data['unit_id'];
        $this->tenant_id = $data['tenant_id'];
        $this->amount = (string) $data['amount'];
        $this->due_date = $data['due_date'];

        if (empty($this->invoice_month)) {
            $this->invoice_month = $data['invoice_month'];
        }

        $this->property_name = $lease->property?->name;
        $this->unit_number = $lease->unit?->unit_number;
        $this->tenant_name = trim(($lease->tenant?->first_name ?? '') . ' ' . ($lease->tenant?->last_name ?? ''));
    }

    public function save(): void
    {
        $this->authorize('create', RentInvoice::class);

        $validated = $this->validate();

        // Ensure landlord ownership
        if (auth()->user()->hasRole('landlord')) {
            $lease = Lease::find($validated['lease_id']);
            abort_if($lease->landlord_id !== auth()->id(), 403);
        }

        try {
            app(RentInvoiceService::class)->createInvoice([
                ...$validated,
                'landlord_id' => auth()->user()->hasRole('admin')
                    ? Lease::find($validated['lease_id'])->landlord_id
                    : auth()->id(),
            ]);
        } catch (\DomainException $e) {
            $this->addError('lease_id', $e->getMessage());
            return;
        }

        Flux::toast('Invoice created successfully.', 'success');

        $this->redirect(route('rent-invoices.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.rent-invoices.create')
            ->layout('layouts.app')
            ->title(__('Create Invoice'));
    }
}