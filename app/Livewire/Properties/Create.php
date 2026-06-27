<?php

namespace App\Livewire\Properties;

use App\Models\Property;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';
    public string $type = 'residential';
    public string $address_line_1 = '';
    public ?string $address_line_2 = null;
    public string $city = '';
    public ?string $region = null;
    public ?string $postal_code = null;
    public string $country = 'Djibouti';
    public ?string $description = null;
    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name'           => 'required|string|max:255',
            'type'           => 'required|in:residential,commercial,mixed',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city'           => 'required|string|max:100',
            'region'         => 'nullable|string|max:100',
            'postal_code'    => 'nullable|string|max:20',
            'country'        => 'required|string|max:100',
            'description'    => 'nullable|string|max:2000',
            'is_active'      => 'boolean',
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $this->authorize('create', Property::class);

        Property::create([
            ...$validated,
            'landlord_id' => auth()->id(),
        ]);

        Flux::toast('Property created successfully.', 'success');

        $this->redirect(route('properties.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.properties.create')
            ->layout('layouts.app')
            ->title(__('Create Property'));
    }
}