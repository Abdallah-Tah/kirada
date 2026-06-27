<?php

namespace App\Livewire\Units;

use App\Models\Building;
use App\Models\Property;
use App\Models\Unit;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Create extends Component
{
    public ?int $property_id = null;
    public ?int $building_id = null;
    public string $unit_number = '';
    public ?string $floor = null;
    public string $type = 'apartment';
    public ?string $area_sqm = null;
    public int $bedrooms = 0;
    public int $bathrooms = 0;
    public string $monthly_rent = '';
    public string $security_deposit = '';
    public string $status = 'vacant';
    public ?string $description = null;
    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'property_id'     => 'required|exists:properties,id',
            'building_id'     => 'nullable|exists:buildings,id',
            'unit_number'     => 'required|string|max:50',
            'floor'           => 'nullable|string|max:20',
            'type'            => 'required|in:apartment,office,shop,warehouse,other',
            'area_sqm'        => 'nullable|numeric|min:0|max:999999',
            'bedrooms'        => 'required|integer|min:0|max:50',
            'bathrooms'       => 'required|integer|min:0|max:50',
            'monthly_rent'    => 'required|numeric|min:0|max:99999999',
            'security_deposit'=> 'required|numeric|min:0|max:99999999',
            'status'          => 'required|in:vacant,occupied,maintenance',
            'description'     => 'nullable|string|max:2000',
            'is_active'       => 'boolean',
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
    public function buildings()
    {
        if (!$this->property_id) {
            return collect();
        }

        return Building::where('property_id', $this->property_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    public function save(): void
    {
        $this->authorize('create', Unit::class);

        $validated = $this->validate();

        // Ensure landlord owns the property
        $property = Property::find($validated['property_id']);
        if (auth()->user()->hasRole('landlord') && $property->landlord_id !== auth()->id()) {
            abort(403);
        }

        Unit::create($validated);

        Flux::toast('Unit created successfully.', 'success');

        $this->redirect(route('units.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.units.create')
            ->layout('layouts.app')
            ->title(__('Create Unit'));
    }
}