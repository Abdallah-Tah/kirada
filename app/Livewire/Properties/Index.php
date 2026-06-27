<?php

namespace App\Livewire\Properties;

use App\Models\Property;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterType = '';
    public string $filterStatus = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function properties()
    {
        $query = Property::query()
            ->withCount('units')
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('address_line_1', 'like', "%{$this->search}%")
                      ->orWhere('city', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterStatus !== '', fn($q) => $q->where('is_active', $this->filterStatus === 'active'))
            ->latest();

        // Landlord sees only own; admin sees all
        if (auth()->user()->hasRole('landlord')) {
            $query->forLandlord(auth()->id());
        }

        return $query->paginate(10);
    }

    public function delete(int $id): void
    {
        $property = Property::findOrFail($id);

        $this->authorize('delete', $property);

        $property->delete();

        $this->dispatch('property-deleted');
        Flux::toast('Property deleted.', 'success');
    }

    public function render()
    {
        return view('livewire.properties.index')
            ->layout('layouts.app')
            ->title(__('Properties'));
    }
}