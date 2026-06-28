<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use App\Models\Lease;
use App\Models\RentInvoice;
use App\Models\RentPayment;
use App\Models\Tenant;
use App\Services\DocumentService;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public string $title = '';
    public string $type = 'other';
    public ?int $tenant_id = null;
    public ?int $lease_id = null;
    public ?int $rent_invoice_id = null;
    public ?int $rent_payment_id = null;
    public string $visibility = 'landlord_only';
    public $file = null;

    protected function rules(): array
    {
        $rules = [
            'title'     => 'required|string|max:255',
            'type'      => 'required|in:lease_agreement,payment_receipt,payment_proof,id_document,other',
            'file'      => 'required|file|max:10240', // 10MB max
            'visibility' => 'required|in:landlord_only,tenant_visible,admin_only',
        ];

        $user = auth()->user();

        // Landlord/admin can link to any entity
        if (!$user->hasRole('tenant')) {
            $rules['tenant_id'] = 'nullable|exists:tenants,id';
            $rules['lease_id'] = 'nullable|exists:leases,id';
            $rules['rent_invoice_id'] = 'nullable|exists:rent_invoices,id';
            $rules['rent_payment_id'] = 'nullable|exists:rent_payments,id';
        }

        return $rules;
    }

    #[Computed]
    public function tenants()
    {
        $user = auth()->user();
        $query = Tenant::select('id', 'first_name', 'last_name')->orderBy('first_name');

        if ($user->hasRole('landlord')) {
            $query->forLandlord($user->id);
        } elseif ($user->hasRole('tenant')) {
            $tenant = Tenant::where('user_id', $user->id)->first();
            if ($tenant) {
                $query->where('id', $tenant->id);
            } else {
                return collect();
            }
        }

        return $query->get();
    }

    #[Computed]
    public function leases()
    {
        $user = auth()->user();
        $query = Lease::select('id', 'tenant_id', 'unit_id')->with('tenant:id,first_name,last_name');

        if ($user->hasRole('landlord')) {
            $query->where('landlord_id', $user->id);
        } elseif ($user->hasRole('tenant')) {
            $tenant = Tenant::where('user_id', $user->id)->first();
            if ($tenant) {
                $query->where('tenant_id', $tenant->id);
            } else {
                return collect();
            }
        }

        return $query->latest()->get();
    }

    #[Computed]
    public function invoices()
    {
        $user = auth()->user();
        $query = RentInvoice::select('id', 'invoice_number', 'tenant_id');

        if ($user->hasRole('landlord')) {
            $query->forLandlord($user->id);
        } elseif ($user->hasRole('tenant')) {
            $tenant = Tenant::where('user_id', $user->id)->first();
            if ($tenant) {
                $query->where('tenant_id', $tenant->id);
            } else {
                return collect();
            }
        }

        return $query->latest()->get();
    }

    #[Computed]
    public function payments()
    {
        $user = auth()->user();
        $query = RentPayment::select('id', 'payment_number', 'tenant_id');

        if ($user->hasRole('landlord')) {
            $query->forLandlord($user->id);
        } elseif ($user->hasRole('tenant')) {
            $tenant = Tenant::where('user_id', $user->id)->first();
            if ($tenant) {
                $query->where('tenant_id', $tenant->id);
            } else {
                return collect();
            }
        }

        return $query->latest()->get();
    }

    #[Computed]
    public function allowedVisibilities()
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return [
                'landlord_only'  => 'Landlord Only',
                'tenant_visible' => 'Tenant Visible',
                'admin_only'     => 'Admin Only',
            ];
        }

        if ($user->hasRole('landlord')) {
            return [
                'landlord_only'  => 'Landlord Only',
                'tenant_visible' => 'Tenant Visible',
            ];
        }

        // Tenant: always tenant_visible
        return [
            'tenant_visible' => 'Tenant Visible',
        ];
    }

    public function save(): void
    {
        $this->authorize('create', Document::class);

        $validated = $this->validate();

        $user = auth()->user();

        // Tenant: force visibility + type restrictions
        if ($user->hasRole('tenant')) {
            $validated['visibility'] = 'tenant_visible';
            // Tenant can only upload payment_proof or id_document
            if (!in_array($validated['type'], ['payment_proof', 'id_document'])) {
                $this->addError('type', 'Tenants can only upload payment proofs or ID documents.');
                return;
            }

            // Auto-link tenant_id
            $tenant = Tenant::where('user_id', $user->id)->first();
            if ($tenant) {
                $validated['tenant_id'] = $tenant->id;
                $validated['landlord_id'] = $tenant->landlord_id;
            }
        }

        // Ensure landlord ownership
        if ($user->hasRole('landlord') && isset($validated['tenant_id']) && $validated['tenant_id']) {
            $tenant = Tenant::find($validated['tenant_id']);
            abort_if($tenant && $tenant->landlord_id !== $user->id, 403);
        }

        try {
            app(DocumentService::class)->uploadDocument($validated, $this->file, $user);
        } catch (\Exception $e) {
            $this->addError('file', $e->getMessage());
            return;
        }

        \Flux\Flux::toast('Document uploaded successfully.', 'success');

        $this->redirect(route('documents.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.documents.create')
            ->layout('layouts.app')
            ->title(__('Upload Document'));
    }
}
