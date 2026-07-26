<?php

namespace App\Livewire\MaintenanceProfiles;

use App\Models\Currency;
use App\Models\MaintenanceProfile;
use App\Services\MaintenanceProfileService;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Edit extends Component
{
    public string $business_name = '';

    public string $bio = '';

    /** @var array<int, string> */
    public array $trades = [];

    /** @var array<int, string> */
    public array $service_areas = [];

    public string $newArea = '';

    public ?int $currency_id = null;

    public ?int $hourly_rate = null;

    public ?int $callout_fee = null;

    public string $phone = '';

    public string $whatsapp = '';

    public ?int $years_experience = null;

    public bool $is_published = false;

    public function mount(): void
    {
        $this->authorize('manageOwn', MaintenanceProfile::class);

        $profile = auth()->user()->maintenanceProfile;

        if (! $profile) {
            // Sensible starting point: their account name and phone country default.
            $this->business_name = auth()->user()->name;

            return;
        }

        $this->business_name = $profile->business_name;
        $this->bio = $profile->bio ?? '';
        $this->trades = $profile->trades ?? [];
        $this->service_areas = $profile->service_areas ?? [];
        $this->currency_id = $profile->currency_id;
        $this->hourly_rate = $profile->hourly_rate;
        $this->callout_fee = $profile->callout_fee;
        $this->phone = $profile->phone ?? '';
        $this->whatsapp = $profile->whatsapp ?? '';
        $this->years_experience = $profile->years_experience;
        $this->is_published = $profile->is_published;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'business_name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:2000',
            'trades' => 'required|array|min:1',
            'trades.*' => 'string|in:'.implode(',', MaintenanceProfile::TRADES),
            'service_areas' => 'required|array|min:1',
            'service_areas.*' => 'string|max:120',
            'currency_id' => 'nullable|exists:currencies,id',
            'hourly_rate' => 'nullable|integer|min:0|max:100000000',
            'callout_fee' => 'nullable|integer|min:0|max:100000000',
            'phone' => 'nullable|string|max:32',
            'whatsapp' => 'nullable|string|max:32',
            'years_experience' => 'nullable|integer|min:0|max:80',
            'is_published' => 'boolean',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'trades.required' => __('Select at least one trade.'),
            'service_areas.required' => __('Add at least one area you serve.'),
        ];
    }

    #[Computed]
    public function profile(): ?MaintenanceProfile
    {
        return auth()->user()->maintenanceProfile;
    }

    /**
     * @return Collection<int, Currency>
     */
    #[Computed]
    public function currencies()
    {
        return Currency::where('is_active', true)->orderBy('code')->get();
    }

    /**
     * @return list<string>
     */
    #[Computed]
    public function availableTrades(): array
    {
        return MaintenanceProfile::TRADES;
    }

    public function addArea(): void
    {
        $area = trim($this->newArea);

        if ($area === '') {
            return;
        }

        if (! in_array($area, $this->service_areas, true)) {
            $this->service_areas[] = $area;
        }

        $this->newArea = '';
    }

    public function removeArea(int $index): void
    {
        unset($this->service_areas[$index]);
        $this->service_areas = array_values($this->service_areas);
    }

    public function save(): void
    {
        $this->authorize('manageOwn', MaintenanceProfile::class);

        $validated = $this->validate();

        try {
            app(MaintenanceProfileService::class)->saveProfile(auth()->user(), $validated);
        } catch (\DomainException $e) {
            $this->addError('trades', $e->getMessage());

            return;
        }

        unset($this->profile);

        Flux::toast(
            $this->is_published
                ? __('Profile saved and listed in the directory.')
                : __('Profile saved. Publish it to appear in the directory.'),
            'success',
        );
    }

    public function render(): View
    {
        return view('livewire.maintenance-profiles.edit')
            ->layout('layouts.app')
            ->title(__('My Provider Profile'));
    }
}
