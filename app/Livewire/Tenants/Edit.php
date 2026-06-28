<?php

namespace App\Livewire\Tenants;

use App\Models\Tenant;
use Livewire\Component;

class Edit extends Component
{
    public Tenant $tenant;
    public string $first_name = '';
    public string $last_name = '';
    public string $phone = '';
    public ?string $email = null;
    public ?string $national_id = null;
    public ?string $address = null;
    public ?string $city = null;
    public string $status = 'active';
    public ?string $notes = null;

    public function mount(Tenant $tenant): void
    {
        $this->authorize('update', $tenant);

        $this->tenant = $tenant;
        $this->fill($tenant->only([
            'first_name', 'last_name', 'phone', 'email',
            'national_id', 'address', 'city', 'status', 'notes',
        ]));
    }

    protected function rules(): array
    {
        return [
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'phone'        => 'required|string|max:30',
            'email'        => 'nullable|email|max:255',
            'national_id'  => 'nullable|string|max:100',
            'address'      => 'nullable|string|max:500',
            'city'         => 'nullable|string|max:100',
            'status'       => 'required|in:active,inactive',
            'notes'        => 'nullable|string|max:2000',
        ];
    }

    public function save(): void
    {
        $this->authorize('update', $this->tenant);

        $validated = $this->validate();

        $this->tenant->update($validated);

        \Flux\Flux::toast('Tenant updated successfully.', 'success');

        $this->redirect(route('tenants.index'), navigate: true);
    }

    /**
     * Browser address autocomplete sends normalized Google address parts here.
     *
     * @param  array<string, mixed>  $address
     */
    public function applyGoogleAddress(array $address): void
    {
        $this->address = (string) ($address['address_line_1'] ?? $this->address);
        $this->city = ! empty($address['city']) ? (string) $address['city'] : $this->city;
    }

    public function render()
    {
        return view('livewire.tenants.edit')
            ->layout('layouts.app')
            ->title(__('Edit Tenant'));
    }
}
