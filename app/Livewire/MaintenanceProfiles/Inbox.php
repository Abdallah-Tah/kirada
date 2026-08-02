<?php

namespace App\Livewire\MaintenanceProfiles;

use App\Models\User;
use App\Services\MaintenanceProfileService;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Inbox extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()->isMaintenance(), 403);
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function pending()
    {
        return auth()->user()->landlordConnections()
            ->wherePivot('status', 'pending')
            ->orderByPivot('created_at', 'desc')
            ->get();
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function active()
    {
        return auth()->user()->approvedLandlords()
            ->orderByPivot('approved_at', 'desc')
            ->get();
    }

    public function accept(int $landlordId): void
    {
        $this->resolve($landlordId, 'approve');
    }

    public function decline(int $landlordId): void
    {
        $this->resolve($landlordId, 'decline');
    }

    private function resolve(int $landlordId, string $action): void
    {
        abort_unless(auth()->user()->isMaintenance(), 403);

        $landlord = User::findOrFail($landlordId);
        $service = app(MaintenanceProfileService::class);

        try {
            $action === 'approve'
                ? $service->approveConnection(auth()->user(), $landlord)
                : $service->declineConnection(auth()->user(), $landlord);
        } catch (\DomainException $e) {
            Flux::toast(text: $e->getMessage(), variant: 'danger');

            return;
        }

        unset($this->pending, $this->active);

        Flux::toast(
            text: $action === 'approve'
                ? __('You joined :landlord\'s maintenance team.', ['landlord' => $landlord->name])
                : __('Invitation declined.'),
            variant: 'success',
        );
    }

    public function render(): View
    {
        return view('livewire.maintenance-profiles.inbox')
            ->layout('layouts.app')
            ->title(__('Work Invitations'));
    }
}
