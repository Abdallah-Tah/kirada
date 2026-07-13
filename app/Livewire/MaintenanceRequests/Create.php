<?php

namespace App\Livewire\MaintenanceRequests;

use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Services\MaintenanceRequestService;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public ?int $property_id = null;

    public ?int $unit_id = null;

    public ?int $tenant_id = null;

    public ?int $lease_id = null;

    public string $title = '';

    public string $description = '';

    public string $category = 'other';

    public ?string $location = null;

    public bool $permission_to_enter = false;

    public ?string $preferred_access_window = null;

    public string $priority = 'medium';

    public array $photos = [];

    public function mount(): void
    {
        $user = auth()->user();

        if (! $user->hasRole('tenant')) {
            return;
        }

        $tenant = Tenant::where('user_id', $user->id)->first();

        if (! $tenant) {
            return;
        }

        $this->tenant_id = $tenant->id;

        $lease = Lease::active()
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->first();

        if ($lease) {
            $this->lease_id = $lease->id;
            $this->property_id = $lease->property_id;
            $this->unit_id = $lease->unit_id;
        }
    }

    protected function rules(): array
    {
        return [
            'property_id' => 'required|exists:properties,id',
            'unit_id' => 'nullable|exists:units,id',
            'tenant_id' => 'nullable|exists:tenants,id',
            'lease_id' => 'nullable|exists:leases,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'category' => 'required|in:plumbing,electrical,ac_heating,appliance,door_lock,pest,cleaning,safety,other',
            'location' => 'nullable|string|max:120',
            'permission_to_enter' => 'boolean',
            'preferred_access_window' => 'nullable|string|max:255',
            'priority' => 'required|in:low,medium,high,urgent',
            'photos' => 'array|max:6',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ];
    }

    public function categoryOptions(): array
    {
        return [
            'plumbing' => 'Plumbing',
            'electrical' => 'Electrical',
            'ac_heating' => 'AC / Heating',
            'appliance' => 'Appliance',
            'door_lock' => 'Door / Lock',
            'pest' => 'Pest',
            'cleaning' => 'Cleaning',
            'safety' => 'Safety',
            'other' => 'Other',
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
        if (! $this->property_id) {
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
        if (! $this->unit_id) {
            $this->lease_id = null;

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

    public function updatedPropertyId(): void
    {
        if (auth()->user()->hasRole('tenant')) {
            return;
        }

        $this->unit_id = null;
        $this->tenant_id = null;
        $this->lease_id = null;
    }

    public function save(): void
    {
        $this->authorize('create', MaintenanceRequest::class);

        $validated = $this->validate();
        $photos = $validated['photos'] ?? [];
        unset($validated['photos']);

        // Ensure landlord ownership
        if (auth()->user()->hasRole('landlord')) {
            $property = Property::find($validated['property_id']);
            abort_if($property->landlord_id !== auth()->id(), 403);
        }

        if (auth()->user()->hasRole('tenant')) {
            $tenant = Tenant::where('user_id', auth()->id())->firstOrFail();
            $lease = Lease::active()
                ->where('tenant_id', $tenant->id)
                ->where('property_id', $validated['property_id'])
                ->when($validated['unit_id'] ?? null, fn ($query, $unitId) => $query->where('unit_id', $unitId))
                ->latest()
                ->firstOrFail();

            $validated['tenant_id'] = $tenant->id;
            $validated['lease_id'] = $lease->id;
            $validated['property_id'] = $lease->property_id;
            $validated['unit_id'] = $lease->unit_id;
        }

        try {
            $request = app(MaintenanceRequestService::class)->createRequest(
                $validated,
                auth()->user(),
            );

            app(MaintenanceRequestService::class)->storeAttachments(
                $request,
                auth()->user(),
                $photos,
                kind: 'initial',
            );
        } catch (\DomainException $e) {
            $this->addError('property_id', $e->getMessage());

            return;
        }

        Flux::toast('Maintenance request submitted.', 'success');

        $this->redirect(route('maintenance-requests.show', $request), navigate: true);
    }

    public function render()
    {
        return view('livewire.maintenance-requests.create')
            ->layout('layouts.app')
            ->title(__('New Maintenance Request'));
    }
}
