<?php

namespace App\Livewire\Tenant\Contracts;

use App\Models\Contract;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * A renter's own contracts, read-only.
 *
 * Drafts are excluded: a contract the landlord has not sent is not yet an
 * agreement the tenant is party to.
 */
class Index extends Component
{
    use WithPagination;

    #[Computed]
    public function contracts()
    {
        return Contract::query()
            ->whereIn('tenant_id', auth()->user()->tenantProfileIds())
            ->where('status', '!=', 'draft')
            ->with(['signatures', 'property:id,name', 'unit:id,unit_number'])
            ->withCount([
                'signatures',
                'signatures as signed_signatures_count' => fn ($q) => $q->where('status', 'signed'),
            ])
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.tenant.contracts.index')
            ->layout('layouts.app')
            ->title(__('My Contracts'));
    }
}
