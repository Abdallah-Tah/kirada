<?php

namespace App\Livewire\Leases;

use App\Models\Lease;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Services\LeaseService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Edit extends Component
{
    public Lease $lease;
    public ?int $property_id = null;
    public ?int $unit_id = null;
    public ?int $tenant_id = null;
    public string $start_date = '';
    public ?string $end_date = null;
    public string $monthly_rent = '';
    public ?string $security_deposit = null;
    public int $payment_due_day = 1;
    public string $status = 'active';
    public ?string $notes = null;

    // Billing settings
    public bool $auto_generate_invoices = true;
    public int $invoice_generation_days_before_due = 7;
    public int $grace_period_days = 5;
    public string $late_fee_type = 'none';
    public ?string $late_fee_amount = null;
    public string $late_fee_frequency = 'once';
    public array $reminder_keys = ['before_due_7', 'before_due_3', 'before_due_1', 'overdue_1'];

    public function mount(Lease $lease): void
    {
        $this->authorize('update', $lease);

        $this->lease = $lease;
        $this->fill($lease->only([
            'property_id', 'unit_id', 'tenant_id',
            'start_date', 'end_date', 'monthly_rent',
            'security_deposit', 'payment_due_day', 'status', 'notes',
            'auto_generate_invoices', 'invoice_generation_days_before_due',
            'grace_period_days', 'late_fee_type', 'late_fee_frequency',
        ]));

        // Format dates for input fields
        $this->start_date      = $lease->start_date?->format('Y-m-d') ?? '';
        $this->end_date        = $lease->end_date?->format('Y-m-d') ?? null;
        $this->monthly_rent    = (string) $lease->monthly_rent;
        $this->security_deposit = $lease->security_deposit ? (string) $lease->security_deposit : null;
        $this->late_fee_amount = $lease->late_fee_amount ? (string) $lease->late_fee_amount : null;
        $this->reminder_keys   = $lease->reminder_schedule
            ?? ['before_due_7', 'before_due_3', 'before_due_1', 'overdue_1'];
    }

    protected function rules(): array
    {
        return [
            'property_id'                        => 'required|exists:properties,id',
            'unit_id'                            => 'required|exists:units,id',
            'tenant_id'                          => 'required|exists:tenants,id',
            'start_date'                         => 'required|date',
            'end_date'                           => 'nullable|date|after_or_equal:start_date',
            'monthly_rent'                       => 'required|numeric|min:0|max:99999999',
            'security_deposit'                   => 'nullable|numeric|min:0|max:99999999',
            'payment_due_day'                    => 'required|integer|min:1|max:28',
            'status'                             => 'required|in:active,ended,cancelled',
            'notes'                              => 'nullable|string|max:2000',
            'auto_generate_invoices'             => 'boolean',
            'invoice_generation_days_before_due' => 'required|integer|min:1|max:30',
            'grace_period_days'                  => 'required|integer|min:0|max:30',
            'late_fee_type'                      => 'required|in:none,fixed,percentage',
            'late_fee_amount'                    => 'nullable|numeric|min:0|max:99999',
            'late_fee_frequency'                 => 'required|in:once,weekly,monthly',
            'reminder_keys'                      => 'array',
            'reminder_keys.*'                    => 'string',
        ];
    }

    #[Computed]
    public function properties()
    {
        $query = Property::select('id', 'name')->orderBy('name');

        if (auth()->user()->hasRole('landlord')) {
            $query->forLandlord(auth()->id());
        }

        return $query->get();
    }

    #[Computed]
    public function units()
    {
        if (!$this->property_id) {
            return collect();
        }

        // Show vacant units + the current unit (even if occupied)
        $query = Unit::where('property_id', $this->property_id)
            ->where(function ($q) {
                $q->where('status', 'vacant')
                  ->orWhere('id', $this->lease->unit_id);
            })
            ->select('id', 'unit_number', 'monthly_rent')
            ->orderBy('unit_number');

        if (auth()->user()->hasRole('landlord')) {
            $query->whereHas('property', fn($q) => $q->forLandlord(auth()->id()));
        }

        return $query->get();
    }

    #[Computed]
    public function tenants()
    {
        $query = Tenant::select('id', 'first_name', 'last_name')->orderBy('last_name');

        if (auth()->user()->hasRole('landlord')) {
            $query->forLandlord(auth()->id());
        }

        return $query->get();
    }

    public function updatingPropertyId(): void
    {
        // Don't reset unit if it's the lease's current property
        if ($this->property_id !== $this->lease->property_id) {
            $this->unit_id = null;
        }
    }

    public function save(): void
    {
        $this->authorize('update', $this->lease);

        $validated = $this->validate();

        // Ensure landlord ownership
        if (auth()->user()->hasRole('landlord')) {
            $property = Property::find($validated['property_id']);
            abort_if($property->landlord_id !== auth()->id(), 403);

            $unit = Unit::find($validated['unit_id']);
            abort_if($unit->property->landlord_id !== auth()->id(), 403);

            $tenant = Tenant::find($validated['tenant_id']);
            abort_if($tenant->landlord_id !== auth()->id(), 403);
        }

        app(LeaseService::class)->updateLease($this->lease, [
            ...$validated,
            'reminder_schedule' => $this->reminder_keys ?: null,
        ]);

        \Flux\Flux::toast('Lease updated successfully.', 'success');

        $this->redirect(route('leases.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.leases.edit')
            ->layout('layouts.app')
            ->title(__('Edit Lease'));
    }
}
