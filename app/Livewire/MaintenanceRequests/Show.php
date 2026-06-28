<?php

namespace App\Livewire\MaintenanceRequests;

use App\Models\MaintenanceRequest;
use App\Services\MaintenanceRequestService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    public MaintenanceRequest $maintenanceRequest;
    public string $newComment = '';
    public bool $isInternal = false;
    public ?int $assignTo = null;
    public ?string $newStatus = null;

    public function mount(MaintenanceRequest $maintenanceRequest): void
    {
        $this->authorize('view', $maintenanceRequest);
        $this->maintenanceRequest = $maintenanceRequest;
        $this->assignTo = $maintenanceRequest->assigned_to;
    }

    #[Computed]
    public function visibleComments()
    {
        return app(MaintenanceRequestService::class)
            ->getVisibleComments($this->maintenanceRequest, auth()->user());
    }

    #[Computed]
    public function maintenanceUsers()
    {
        return app(MaintenanceRequestService::class)
            ->getMaintenanceUsers();
    }

    #[Computed]
    public function allowedTransitions()
    {
        return app(MaintenanceRequestService::class)
            ->getAllowedTransitions($this->maintenanceRequest->status);
    }

    #[Computed]
    public function canManage()
    {
        $user = auth()->user();
        return $user->hasRole('admin') || $this->maintenanceRequest->landlord_id === $user->id;
    }

    public function addComment(): void
    {
        $this->authorize('view', $this->maintenanceRequest);

        if (empty(trim($this->newComment))) {
            return;
        }

        // Only admin/landlord can add internal comments
        if ($this->isInternal && !$this->canManage) {
            $this->isInternal = false;
        }

        app(MaintenanceRequestService::class)->addComment(
            $this->maintenanceRequest,
            auth()->user(),
            $this->newComment,
            $this->isInternal,
        );

        $this->newComment = '';
        $this->isInternal = false;

        unset($this->visibleComments);

        \Flux\Flux::toast('Comment added.', 'success');
    }

    public function assign(): void
    {
        $this->authorize('update', $this->maintenanceRequest);

        if (!$this->assignTo) {
            return;
        }

        try {
            app(MaintenanceRequestService::class)->assignRequest(
                $this->maintenanceRequest,
                $this->assignTo,
            );

            unset($this->maintenanceRequest);
            $this->maintenanceRequest = MaintenanceRequest::find($this->maintenanceRequest->id);

            \Flux\Flux::toast('Request assigned.', 'success');
        } catch (\DomainException $e) {
            \Flux\Flux::toast($e->getMessage(), 'error');
        }
    }

    public function changeStatus(): void
    {
        $this->authorize('update', $this->maintenanceRequest);

        if (!$this->newStatus) {
            return;
        }

        try {
            app(MaintenanceRequestService::class)->changeStatus(
                $this->maintenanceRequest,
                $this->newStatus,
            );

            $this->maintenanceRequest->refresh();
            $this->newStatus = null;

            unset($this->allowedTransitions);

            \Flux\Flux::toast('Status updated.', 'success');
        } catch (\DomainException $e) {
            \Flux\Flux::toast($e->getMessage(), 'error');
        }
    }

    public function render()
    {
        return view('livewire.maintenance-requests.show')
            ->layout('layouts.app')
            ->title(__('Maintenance Request'));
    }
}
