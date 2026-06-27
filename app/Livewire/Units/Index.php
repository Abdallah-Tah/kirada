<?php

namespace App\Livewire\Units;

use App\Models\Property;
use App\Models\Unit;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public ?int $propertyId = null;
    public string $search = '';
    public string $filterStatus = '';
    public string $filterType = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPropertyId(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
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
        $query = Unit::query()
            ->with('property:id,name', 'building:id,name')
            ->when($this->propertyId, fn($q) => $q->forProperty($this->propertyId))
            ->when($this->search, function ($q) {
                $q->where('unit_number', 'like', "%{$this->search}%")
                  ->orWhere('floor', 'like', "%{$this->search}%");
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->latest();

        // Landlord sees only units belonging to own properties
        if (auth()->user()->hasRole('landlord')) {
            $query->whereHas('property', fn($q) => $q->forLandlord(auth()->id()));
        }

        return $query->paginate(10);
    }

    public function delete(int $id): void
    {
        $unit = Unit::findOrFail($id);

        $this->authorize('delete', $unit);

        $unit->delete();

        unset($this->units);

        Flux::toast('Unit deleted.', 'success');
    }

    public function render()
    {
        return view('livewire.units.index')
            ->layout('layouts.app')
            ->title(__('Units'));
    }
}