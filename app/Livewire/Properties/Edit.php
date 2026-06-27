<?php

namespace App\Livewire\Properties;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Property;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Edit extends Component
{
    public Property $property;
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
    public ?int $country_id = null;
    public ?int $currency_id = null;

    public function mount(Property $property): void
    {
        $this->authorize('update', $property);

        $this->property = $property;
        $this->fill($property->only([
            'name', 'type', 'address_line_1', 'address_line_2',
            'city', 'region', 'postal_code', 'country',
            'description', 'is_active', 'country_id', 'currency_id',
        ]));
    }

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
            'country_id'     => 'required|exists:countries,id',
            'currency_id'    => 'required|exists:currencies,id',
        ];
    }

    public function save(): void
    {
        $this->authorize('update', $this->property);

        $validated = $this->validate();

        $this->property->update($validated);

        Flux::toast('Property updated successfully.', 'success');

        $this->redirect(route('properties.index'), navigate: true);
    }

    #[Computed]
    public function countries()
    {
        return Country::active()->orderBy('name')->get();
    }

    #[Computed]
    public function currencies()
    {
        return Currency::active()->orderBy('code')->get();
    }

    public function updatedCountryId(): void
    {
        if (!$this->country_id) {
            return;
        }

        $country = Country::find($this->country_id);
        $default = $country?->defaultCurrency();

        if ($default) {
            $this->currency_id = $default->id;
        }
    }

    public function render()
    {
        return view('livewire.properties.edit')
            ->layout('layouts.app')
            ->title(__('Edit Property'));
    }
}