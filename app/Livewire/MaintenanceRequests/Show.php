<?php

namespace App\Livewire\MaintenanceRequests;

use App\Models\MaintenanceRequest;
use App\Services\MaintenanceRequestService;
use App\Services\MessagingService;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class Show extends Component
{
    use WithFileUploads;

    public MaintenanceRequest $maintenanceRequest;

    public string $newComment = '';

    public bool $isInternal = false;

    public ?int $assignTo = null;

    public ?string $newStatus = null;

    public array $commentPhotos = [];

    public array $statusPhotos = [];

    protected function rules(): array
    {
        return [
            'newComment' => 'nullable|string|max:5000',
            'commentPhotos' => 'array|max:6',
            'commentPhotos.*' => 'image|max:5120',
            'statusPhotos' => 'array|max:6',
            'statusPhotos.*' => 'image|max:5120',
        ];
    }

    public function mount(MaintenanceRequest $maintenanceRequest): void
    {
        $this->authorize('view', $maintenanceRequest);
        $this->maintenanceRequest = $maintenanceRequest->load([
            'property',
            'unit',
            'tenant',
            'assignee',
            'reporter',
        ]);
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
            ->getAllowedTransitionsForUser($this->maintenanceRequest, auth()->user());
    }

    #[Computed]
    public function canManage()
    {
        $user = auth()->user();

        return $user->hasRole('admin') || $this->maintenanceRequest->landlord_id === $user->id;
    }

    #[Computed]
    public function visibleAttachments()
    {
        $query = $this->maintenanceRequest
            ->attachments()
            ->whereNull('maintenance_comment_id')
            ->with('uploader:id,name')
            ->oldest();

        if (! $this->canManage) {
            $query->where('is_internal', false);
        }

        return $query->get();
    }

    #[Computed]
    public function canMessage(): bool
    {
        return (bool) $this->maintenanceRequest->tenant_id && (bool) $this->maintenanceRequest->landlord_id;
    }

    #[Computed]
    public function timeline(): array
    {
        $events = [
            [
                'label' => __('Submitted'),
                'detail' => $this->maintenanceRequest->reporter?->name,
                'date' => $this->maintenanceRequest->created_at,
                'active' => true,
            ],
        ];

        if ($this->maintenanceRequest->assigned_to) {
            $events[] = [
                'label' => __('Assigned'),
                'detail' => $this->maintenanceRequest->assignee?->name,
                'date' => null,
                'active' => true,
            ];
        }

        $events[] = [
            'label' => __('Resolved'),
            'detail' => null,
            'date' => $this->maintenanceRequest->resolved_at,
            'active' => (bool) $this->maintenanceRequest->resolved_at,
        ];

        $events[] = [
            'label' => __('Closed'),
            'detail' => null,
            'date' => $this->maintenanceRequest->closed_at,
            'active' => (bool) $this->maintenanceRequest->closed_at,
        ];

        return $events;
    }

    public function addComment(): void
    {
        $this->authorize('view', $this->maintenanceRequest);

        $validated = $this->validate([
            'newComment' => 'nullable|string|max:5000',
            'commentPhotos' => 'array|max:6',
            'commentPhotos.*' => 'image|max:5120',
        ]);
        $photos = $validated['commentPhotos'] ?? [];
        $comment = trim($validated['newComment'] ?? '');

        if ($comment === '' && empty($photos)) {
            return;
        }

        // Only admin/landlord can add internal comments
        if ($this->isInternal && ! $this->canManage) {
            $this->isInternal = false;
        }

        $maintenanceComment = app(MaintenanceRequestService::class)->addComment(
            $this->maintenanceRequest,
            auth()->user(),
            $comment !== '' ? $comment : __('Photos added.'),
            $this->isInternal,
        );

        app(MaintenanceRequestService::class)->storeAttachments(
            $this->maintenanceRequest,
            auth()->user(),
            $photos,
            $maintenanceComment,
            kind: $this->maintenanceRequest->isResolved() ? 'resolution' : 'comment',
            isInternal: $this->isInternal,
        );

        $this->newComment = '';
        $this->commentPhotos = [];
        $this->isInternal = false;

        unset($this->visibleComments);

        Flux::toast('Comment added.', 'success');
    }

    public function assign(): void
    {
        $this->authorize('update', $this->maintenanceRequest);

        if (! $this->assignTo) {
            return;
        }

        try {
            app(MaintenanceRequestService::class)->assignRequest(
                $this->maintenanceRequest,
                $this->assignTo,
            );

            $this->maintenanceRequest = $this->maintenanceRequest->fresh([
                'property',
                'unit',
                'tenant',
                'assignee',
                'reporter',
            ]);

            unset($this->allowedTransitions, $this->timeline);
            Flux::toast('Request assigned.', 'success');
        } catch (\DomainException $e) {
            Flux::toast($e->getMessage(), 'error');
        }
    }

    public function changeStatus(): void
    {
        $this->authorize('update', $this->maintenanceRequest);

        if (! $this->newStatus) {
            return;
        }

        $validated = $this->validate([
            'statusPhotos' => 'array|max:6',
            'statusPhotos.*' => 'image|max:5120',
        ]);
        $photos = $validated['statusPhotos'] ?? [];

        try {
            $request = app(MaintenanceRequestService::class)->changeStatus(
                $this->maintenanceRequest,
                $this->newStatus,
                auth()->user(),
            );

            app(MaintenanceRequestService::class)->storeAttachments(
                $request,
                auth()->user(),
                $photos,
                kind: $this->newStatus === 'resolved' ? 'resolution' : 'status',
                isInternal: false,
            );

            $this->maintenanceRequest = $request->load([
                'property',
                'unit',
                'tenant',
                'assignee',
                'reporter',
            ]);
            $this->statusPhotos = [];
            $this->newStatus = null;

            unset($this->allowedTransitions, $this->visibleAttachments, $this->timeline);

            Flux::toast('Status updated.', 'success');
        } catch (\DomainException $e) {
            Flux::toast($e->getMessage(), 'error');
        }
    }

    public function openConversation(): void
    {
        $this->authorize('view', $this->maintenanceRequest);

        if (! $this->canMessage) {
            Flux::toast('This request is missing tenant or landlord details.', 'error');

            return;
        }

        $conversation = app(MessagingService::class)
            ->getOrCreateMaintenanceConversation($this->maintenanceRequest);

        $this->redirect(route('messages.show', $conversation), navigate: true);
    }

    public function render()
    {
        return view('livewire.maintenance-requests.show')
            ->layout('layouts.app')
            ->title(__('Maintenance Request'));
    }
}
