<?php

namespace App\Livewire\Properties;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Property;
use Livewire\Attributes\Computed;
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

    public ?int $country_id = null;

    public ?int $currency_id = null;

    public ?float $latitude = null;

    public ?float $longitude = null;

    public function mount(): void
    {
        $this->country_id = auth()->user()?->country_id
            ?? Country::where('code', 'DJI')->value('id')
            ?? Country::orderBy('name')->value('id');

        $this->syncCountryFields();
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:residential,commercial,mixed',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'region' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
            'description' => 'nullable|string|max:2000',
            'is_active' => 'boolean',
            'country_id' => 'required|exists:countries,id',
            'currency_id' => 'required|exists:currencies,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
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

        \Flux\Flux::toast('Property created successfully.', 'success');

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
        if ($this->country_id) {
            $country = Country::find($this->country_id);

            if ($country) {
                return $country->currencies()->active()->orderBy('code')->get();
            }
        }

        return Currency::active()->orderBy('code')->get();
    }

    /**
     * When a country is selected, auto-set the default currency.
     */
    public function updatedCountryId(): void
    {
        $this->syncCountryFields();
    }

    /**
     * Browser address autocomplete sends normalized Google address parts here.
     *
     * @param  array<string, mixed>  $address
     */
    public function applyGoogleAddress(array $address): void
    {
        $this->address_line_1 = (string) ($address['address_line_1'] ?? $this->address_line_1);
        $this->city = (string) ($address['city'] ?? $this->city);
        $this->region = ! empty($address['region']) ? (string) $address['region'] : $this->region;
        $this->postal_code = ! empty($address['postal_code']) ? (string) $address['postal_code'] : $this->postal_code;
        $this->latitude = isset($address['latitude']) ? (float) $address['latitude'] : $this->latitude;
        $this->longitude = isset($address['longitude']) ? (float) $address['longitude'] : $this->longitude;

        if (! empty($address['country_code'])) {
            $country = Country::where('code2', strtoupper((string) $address['country_code']))->first();

            if ($country) {
                $this->country_id = $country->id;
                $this->syncCountryFields();
            }
        }
    }

    private function syncCountryFields(): void
    {
        if (! $this->country_id) {
            return;
        }

        $country = Country::find($this->country_id);
        $default = $country?->defaultCurrency();

        if ($country) {
            $this->country = $country->name;
        }

        if ($default) {
            $this->currency_id = $default->id;
        }
    }

    public function render()
    {
        return view('livewire.properties.create')
            ->layout('layouts.app')
            ->title(__('Create Property'));
    }
}
