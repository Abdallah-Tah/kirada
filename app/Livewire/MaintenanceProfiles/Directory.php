<?php

namespace App\Livewire\MaintenanceProfiles;

use App\Models\MaintenanceProfile;
use App\Models\User;
use App\Services\MaintenanceProfileService;
use Flux\Flux;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Directory extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $trade = '';

    #[Url(except: '')]
    public string $area = '';

    #[Url(except: false)]
    public bool $verifiedOnly = false;

    public ?int $requestingProviderId = null;

    public string $requestMessage = '';

    public function mount(): void
    {
        $this->authorize('viewAny', MaintenanceProfile::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTrade(): void
    {
        $this->resetPage();
    }

    public function updatingArea(): void
    {
        $this->resetPage();
    }

    public function updatingVerifiedOnly(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, MaintenanceProfile>
     */
    #[Computed]
    public function profiles()
    {
        return app(MaintenanceProfileService::class)->directory([
            'search' => $this->search,
            'trade' => $this->trade,
            'area' => $this->area,
            'verified_only' => $this->verifiedOnly,
        ]);
    }

    /**
     * Connection state keyed by provider user id, so each card can render the
     * right action without an extra query per row.
     *
     * @return array<int, string>
     */
    #[Computed]
    public function connectionStates(): array
    {
        return auth()->user()->maintenanceConnections()
            ->pluck('landlord_maintenance.status', 'users.id')
            ->all();
    }

    /**
     * @return list<string>
     */
    #[Computed]
    public function availableTrades(): array
    {
        return MaintenanceProfile::TRADES;
    }

    public function startRequest(int $providerId): void
    {
        $this->requestingProviderId = $providerId;
        $this->requestMessage = '';

        Flux::modal('connect-provider')->show();
    }

    public function sendRequest(): void
    {
        $this->authorize('viewAny', MaintenanceProfile::class);

        $this->validate([
            'requestMessage' => 'nullable|string|max:500',
        ]);

        $provider = User::findOrFail($this->requestingProviderId);

        try {
            app(MaintenanceProfileService::class)->requestConnection(
                auth()->user(),
                $provider,
                $this->requestMessage ?: null,
            );
        } catch (\DomainException $e) {
            Flux::toast($e->getMessage(), 'danger');

            return;
        }

        unset($this->connectionStates);

        $this->reset('requestingProviderId', 'requestMessage');

        Flux::modal('connect-provider')->close();
        Flux::toast(__('Invitation sent. They will appear in your team once they accept.'), 'success');
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'trade', 'area', 'verifiedOnly');
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.maintenance-profiles.directory')
            ->layout('layouts.app')
            ->title(__('Find Maintenance'));
    }
}
