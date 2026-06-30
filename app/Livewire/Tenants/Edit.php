<?php

namespace App\Livewire\Tenants;

use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public Tenant $tenant;
    public string $first_name = '';
    public string $last_name = '';
    public string $phone = '';
    public ?string $email = null;
    public ?string $national_id = null;
    public ?string $id_type = null;
    public ?string $id_document_number = null;
    public $id_document = null;
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
            'national_id', 'id_type', 'id_document_number',
            'address', 'city', 'status', 'notes',
        ]));
    }

    protected function rules(): array
    {
        return [
            'first_name'           => 'required|string|max:100',
            'last_name'            => 'required|string|max:100',
            'phone'                => 'required|string|max:30',
            'email'                => 'nullable|email|max:255',
            'national_id'          => 'nullable|string|max:100',
            'id_type'              => 'nullable|in:national_id,passport,driver_license,other',
            'id_document_number'   => 'nullable|string|max:100',
            'id_document'          => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp|max:10240',
            'address'              => 'nullable|string|max:500',
            'city'                 => 'nullable|string|max:100',
            'status'               => 'required|in:active,inactive',
            'notes'                => 'nullable|string|max:2000',
        ];
    }

    public function save(): void
    {
        $this->authorize('update', $this->tenant);

        $validated = $this->validate();

        $updateData = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'national_id' => $validated['national_id'],
            'id_type' => $validated['id_type'],
            'id_document_number' => $validated['id_document_number'],
            'address' => $validated['address'],
            'city' => $validated['city'],
            'status' => $validated['status'],
            'notes' => $validated['notes'],
        ];

        if ($this->id_document) {
            // Delete old document if exists
            if ($this->tenant->id_document_path) {
                Storage::disk('public')->delete($this->tenant->id_document_path);
            }

            $updateData['id_document_path'] = $this->id_document->store('tenant-id-documents', 'public');
            $updateData['id_document_original_filename'] = $this->id_document->getClientOriginalName();
        }

        $this->tenant->update($updateData);

        \Flux\Flux::toast('Tenant updated successfully.', 'success');

        $this->redirect(route('tenants.index'), navigate: true);
    }

    /**
     * Remove the existing ID document.
     */
    public function removeIdDocument(): void
    {
        if ($this->tenant->id_document_path) {
            Storage::disk('public')->delete($this->tenant->id_document_path);
            $this->tenant->update([
                'id_document_path' => null,
                'id_document_original_filename' => null,
            ]);
            $this->tenant->refresh();
            \Flux\Flux::toast('ID document removed.', 'success');
        }
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