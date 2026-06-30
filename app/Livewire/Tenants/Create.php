<?php

namespace App\Livewire\Tenants;

use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

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
        $this->authorize('create', Tenant::class);

        $validated = $this->validate();

        $idDocumentPath = null;
        $idDocumentOriginal = null;

        if ($this->id_document) {
            $idDocumentPath = $this->id_document->store('tenant-id-documents', 'public');
            $idDocumentOriginal = $this->id_document->getClientOriginalName();
        }

        Tenant::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'national_id' => $validated['national_id'],
            'id_type' => $validated['id_type'],
            'id_document_number' => $validated['id_document_number'],
            'id_document_path' => $idDocumentPath,
            'id_document_original_filename' => $idDocumentOriginal,
            'address' => $validated['address'],
            'city' => $validated['city'],
            'status' => $validated['status'],
            'notes' => $validated['notes'],
            'landlord_id' => auth()->id(),
        ]);

        \Flux\Flux::toast('Tenant created successfully.', 'success');

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
        return view('livewire.tenants.create')
            ->layout('layouts.app')
            ->title(__('Create Tenant'));
    }
}