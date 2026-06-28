<?php

namespace App\Livewire\MaintenanceRequests;

use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Services\MaintenanceRequestService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Create extends Component
{
    public ?int $property_id = null;
    public ?int $unit_id = null;
    public ?int $tenant_id = null;
    public ?int $lease_id = null;
    public string $title = '';
    public string $description = '';
    public string $priority = 'medium';

    protected function rules(): array
    {
        return [
            'property_id' => 'required|exists:properties,id',
            'unit_id'     => 'nullable|exists:units,id',
            'tenant_id'   => 'nullable|exists:tenants,id',
            'lease_id'    => 'nullable|exists:leases,id',
            'title'       => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'priority'    => 'required|in:low,medium,high,urgent',
        ];
    }

    #[Computed]
    public function properties()
    {
        $user = auth()->user();
        $query = Property::active()->select('id', 'name')->orderBy('name');

        if ($user->hasRole('landlord')) {
            $query->forLandlord($user->id);
        } elseif ($user->hasRole('tenant')) {
            $tenant = Tenant::where('user_id', $user->id)->first();
            if ($tenant) {
                $query->whereHas('units', function ($q) use ($tenant) {
                    $q->whereHas('leases', function ($q) use ($tenant) {
                        $q->where('tenant_id', $tenant->id)->where('status', 'active');
                    });
                });
            } else {
                return collect();
            }
        }

        return $query->get();
    }

    #[Computed]
    public function units()
    {
        if (!$this->property_id) {
            return collect();
        }

        $user = auth()->user();
        $query = Unit::where('property_id', $this->property_id)
            ->select('id', 'unit_number');

        if ($user->hasRole('tenant')) {
            $tenant = Tenant::where('user_id', $user->id)->first();
            if ($tenant) {
                $query->whereHas('leases', function ($q) use ($tenant) {
                    $q->where('tenant_id', $tenant->id)->where('status', 'active');
                });
            }
        }

        return $query->orderBy('unit_number')->get();
    }

    #[Computed]
    public function tenants()
    {
        $user = auth()->user();
        $query = Tenant::select('id', 'first_name', 'last_name')->orderBy('first_name');

        if ($user->hasRole('landlord')) {
            $query->forLandlord($user->id);
        } elseif ($user->hasRole('tenant')) {
            $tenant = Tenant::where('user_id', $user->id)->first();
            if ($tenant) {
                $query->where('id', $tenant->id);
            } else {
                return collect();
            }
        }

        return $query->get();
    }

    /**
     * When a unit is selected, auto-fill tenant + lease if applicable.
     */
    public function updatedUnitId(): void
    {
        if (!$this->unit_id) {
            return;
        }

        $lease = Lease::where('unit_id', $this->unit_id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if ($lease) {
            $this->lease_id = $lease->id;
            $this->tenant_id = $lease->tenant_id;
        }
    }

    public function save(): void
    {
        $this->authorize('create', MaintenanceRequest::class);

        $validated = $this->validate();

        // Ensure landlord ownership
        if (auth()->user()->hasRole('landlord')) {
            $property = Property::find($validated['property_id']);
            abort_if($property->landlord_id !== auth()->id(), 403);
        }

        $request = app(MaintenanceRequestService::class)->createRequest(
            $validated,
            auth()->user(),
        );

        \Flux\Flux::toast('Maintenance request submitted.', 'success');

        $this->redirect(route('maintenance-requests.show', $request), navigate: true);
    }

    public function render()
    {
        return view('livewire.maintenance-requests.create')
            ->layout('layouts.app')
            ->title(__('New Maintenance Request'));
    }
}
