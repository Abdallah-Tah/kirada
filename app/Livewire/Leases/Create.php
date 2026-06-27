<?php

namespace App\Livewire\Leases;

use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Services\LeaseService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Create extends Component
{
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

    protected function rules(): array
    {
        return [
            'property_id'     => 'required|exists:properties,id',
            'unit_id'         => 'required|exists:units,id',
            'tenant_id'       => 'required|exists:tenants,id',
            'start_date'      => 'required|date',
            'end_date'        => 'nullable|date|after_or_equal:start_date',
            'monthly_rent'    => 'required|numeric|min:0|max:99999999',
            'security_deposit'=> 'nullable|numeric|min:0|max:99999999',
            'payment_due_day' => 'required|integer|min:1|max:28',
            'status'          => 'required|in:active,ended,cancelled',
            'notes'           => 'nullable|string|max:2000',
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

        $query = Unit::where('property_id', $this->property_id)
            ->where('status', 'vacant')
            ->select('id', 'unit_number', 'monthly_rent')
            ->orderBy('unit_number');

        // If admin, still respect landlord ownership of properties
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

    /**
     * When property changes, reset unit selection.
     */
    public function updatingPropertyId(): void
    {
        $this->unit_id = null;
    }

    /**
     * When a unit is selected, auto-fill the monthly rent from the unit.
     */
    public function updatedUnitId(): void
    {
        $unit = $this->units->firstWhere('id', $this->unit_id);

        if ($unit && empty($this->monthly_rent)) {
            $this->monthly_rent = (string) $unit->monthly_rent;
        }
    }

    public function save(): void
    {
        $this->authorize('create', \App\Models\Lease::class);

        $validated = $this->validate();

        // Ensure landlord owns the property and unit
        if (auth()->user()->hasRole('landlord')) {
            $property = Property::find($validated['property_id']);
            abort_if($property->landlord_id !== auth()->id(), 403);

            $unit = Unit::find($validated['unit_id']);
            abort_if($unit->property->landlord_id !== auth()->id(), 403);

            $tenant = Tenant::find($validated['tenant_id']);
            abort_if($tenant->landlord_id !== auth()->id(), 403);
        }

        // Ensure unit is vacant before creating an active lease
        $unit = Unit::find($validated['unit_id']);
        if ($validated['status'] === 'active' && !$unit->isVacant()) {
            $this->addError('unit_id', 'This unit is not vacant.');
            return;
        }

        app(LeaseService::class)->createLease([
            ...$validated,
            'landlord_id' => auth()->user()->hasRole('admin')
                ? Property::find($validated['property_id'])->landlord_id
                : auth()->id(),
        ]);

        Flux::toast('Lease created successfully. Unit marked as occupied.', 'success');

        $this->redirect(route('leases.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.leases.create')
            ->layout('layouts.app')
            ->title(__('Create Lease'));
    }
}