<?php

namespace App\Livewire\Tenants;

use App\Models\Tenant;
use Livewire\Component;

class Create extends Component
{
    public string $first_name = '';
    public string $last_name = '';
    public string $phone = '';
    public ?string $email = null;
    public ?string $national_id = null;
    public ?string $address = null;
    public ?string $city = null;
    public string $status = 'active';
    public ?string $notes = null;

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
        $this->authorize('create', Tenant::class);

        $validated = $this->validate();

        Tenant::create([
            ...$validated,
            'landlord_id' => auth()->id(),
        ]);

        Flux::toast('Tenant created successfully.', 'success');

        $this->redirect(route('tenants.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.tenants.create')
            ->layout('layouts.app')
            ->title(__('Create Tenant'));
    }
}