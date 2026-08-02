<?php

namespace App\Livewire\MaintenanceProfiles;

use App\Models\MaintenanceProfile;
use App\Models\User;
use App\Services\MaintenanceProfileService;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Network extends Component
{
    public function mount(): void
    {
        $this->authorize('viewAny', MaintenanceProfile::class);
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function connections()
    {
        return auth()->user()->landlordAccount()->maintenanceConnections()
            ->with('maintenanceProfile.currency')
            ->orderByRaw("CASE landlord_maintenance.status WHEN 'approved' THEN 0 WHEN 'pending' THEN 1 ELSE 2 END")
            ->orderBy('users.name')
            ->get();
    }

    public function revoke(int $providerId): void
    {
        $this->authorize('viewAny', MaintenanceProfile::class);

        $provider = User::findOrFail($providerId);

        try {
            app(MaintenanceProfileService::class)->revokeConnection(auth()->user()->landlordAccount(), $provider);
        } catch (\DomainException $e) {
            Flux::toast(text: $e->getMessage(), variant: 'danger');

            return;
        }

        unset($this->connections);

        Flux::toast(text: __('Provider removed from your team.'), variant: 'success');
    }

    public function render(): View
    {
        return view('livewire.maintenance-profiles.network')
            ->layout('layouts.app')
            ->title(__('My Maintenance Team'));
    }
}
