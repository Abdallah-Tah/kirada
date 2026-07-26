<?php

namespace App\Livewire\MaintenanceRequests;

use App\Models\MaintenanceQuote;
use App\Models\MaintenanceRequest;
use App\Services\MaintenanceQuoteService;
use App\Services\MaintenanceRequestService;
use App\Services\MaintenanceReviewService;
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

    // ── Quote state ──
    public bool $showQuoteForm = false;

    public array $quoteItems = [];

    public float $quoteTaxRate = 0;

    public string $quoteNotes = '';

    public int $reviewRating = 5;

    public int $reviewQuality = 5;

    public int $reviewCommunication = 5;

    public int $reviewProfessionalism = 5;

    public string $reviewTitle = '';

    public string $reviewComment = '';

    protected function rules(): array
    {
        return [
            'newComment' => 'nullable|string|max:5000',
            'commentPhotos' => 'array|max:6',
            'commentPhotos.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
            'statusPhotos' => 'array|max:6',
            'statusPhotos.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
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
            'reporter', 'review',
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
            ->getMaintenanceUsers($this->maintenanceRequest->landlord_id);
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

        return $user->hasRole('admin')
            || ($user->can('maintenance.respond') && $user->belongsToLandlordAccount($this->maintenanceRequest->landlord_id));
    }

    #[Computed]
    public function canReview(): bool
    {
        return auth()->user()->can('maintenance.respond')
            && auth()->user()->belongsToLandlordAccount($this->maintenanceRequest->landlord_id)
            && in_array($this->maintenanceRequest->status, ['resolved', 'closed'], true)
            && $this->maintenanceRequest->assigned_to !== null
            && $this->maintenanceRequest->review === null;
    }

    #[Computed]
    public function isAssignedPro(): bool
    {
        $user = auth()->user();

        return $this->maintenanceRequest->assigned_to === $user->id
            && $user->hasRole('maintenance');
    }

    #[Computed]
    public function quotes()
    {
        return $this->maintenanceRequest->quotes()
            ->with(['items', 'currency', 'maintenanceUser'])
            ->latest()
            ->get();
    }

    #[Computed]
    public function activeQuote(): ?MaintenanceQuote
    {
        return $this->quotes->firstWhere('status', 'approved')
            ?? $this->quotes->firstWhere('status', 'invoiced')
            ?? $this->quotes->firstWhere('status', 'paid');
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
            'commentPhotos.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
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

        try {
            app(MaintenanceRequestService::class)->storeAttachments(
                $this->maintenanceRequest,
                auth()->user(),
                $photos,
                $maintenanceComment,
                kind: $this->maintenanceRequest->isResolved() ? 'resolution' : 'comment',
                isInternal: $this->isInternal,
            );
        } catch (\DomainException $e) {
            $this->addError('commentPhotos', $e->getMessage());

            return;
        }

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
            'statusPhotos.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
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

    public function submitReview(): void
    {
        abort_unless($this->canReview, 403);

        $validated = $this->validate([
            'reviewRating' => 'required|integer|between:1,5',
            'reviewQuality' => 'required|integer|between:1,5',
            'reviewCommunication' => 'required|integer|between:1,5',
            'reviewProfessionalism' => 'required|integer|between:1,5',
            'reviewTitle' => 'nullable|string|max:120',
            'reviewComment' => 'nullable|string|max:2000',
        ]);

        try {
            app(MaintenanceReviewService::class)->create(auth()->user(), $this->maintenanceRequest, [
                'rating' => $validated['reviewRating'],
                'quality_rating' => $validated['reviewQuality'],
                'communication_rating' => $validated['reviewCommunication'],
                'professionalism_rating' => $validated['reviewProfessionalism'],
                'title' => $validated['reviewTitle'] ?: null,
                'comment' => $validated['reviewComment'] ?: null,
            ]);
        } catch (\DomainException $exception) {
            $this->addError('reviewComment', __($exception->getMessage()));

            return;
        }

        $this->maintenanceRequest->load('review');
        unset($this->canReview);
        Flux::toast(__('Review published.'), 'success');
    }

    // ── Quote actions ────────────────────────────────────

    public function startQuote(): void
    {
        $this->authorize('view', $this->maintenanceRequest);

        $this->quoteItems = [
            ['description' => '', 'quantity' => 1, 'unit_price' => 0],
        ];
        $this->quoteTaxRate = 0;
        $this->quoteNotes = '';
        $this->showQuoteForm = true;
    }

    public function addQuoteItem(): void
    {
        $this->quoteItems[] = ['description' => '', 'quantity' => 1, 'unit_price' => 0];
    }

    public function removeQuoteItem(int $index): void
    {
        unset($this->quoteItems[$index]);
        $this->quoteItems = array_values($this->quoteItems);
    }

    public function submitQuote(): void
    {
        $this->authorize('view', $this->maintenanceRequest);

        if (! $this->isAssignedPro) {
            Flux::toast('Only the assigned maintenance professional can submit a quote.', 'error');

            return;
        }

        $validated = $this->validate([
            'quoteItems' => 'required|array|min:1',
            'quoteItems.*.description' => 'required|string|max:255',
            'quoteItems.*.quantity' => 'required|numeric|min:0.01',
            'quoteItems.*.unit_price' => 'required|numeric|min:0',
            'quoteTaxRate' => 'nullable|numeric|min:0|max:100',
            'quoteNotes' => 'nullable|string|max:2000',
        ]);

        try {
            app(MaintenanceQuoteService::class)->submitQuote(
                $this->maintenanceRequest,
                auth()->user(),
                $validated['quoteItems'],
                (float) ($validated['quoteTaxRate'] ?? 0),
                $validated['quoteNotes'] ?? null,
                $this->maintenanceRequest->property?->currency_id,
            );

            $this->showQuoteForm = false;
            $this->quoteItems = [];
            unset($this->quotes, $this->activeQuote);

            Flux::toast('Quote submitted for landlord approval.', 'success');
        } catch (\DomainException $e) {
            Flux::toast($e->getMessage(), 'error');
        }
    }

    public function approveQuote(int $quoteId): void
    {
        $this->authorize('update', $this->maintenanceRequest);

        $quote = MaintenanceQuote::findOrFail($quoteId);

        try {
            app(MaintenanceQuoteService::class)->approve($quote);
            unset($this->quotes, $this->activeQuote);
            Flux::toast('Quote approved.', 'success');
        } catch (\DomainException $e) {
            Flux::toast($e->getMessage(), 'error');
        }
    }

    public function declineQuote(int $quoteId): void
    {
        $this->authorize('update', $this->maintenanceRequest);

        $quote = MaintenanceQuote::findOrFail($quoteId);

        try {
            app(MaintenanceQuoteService::class)->decline($quote);
            unset($this->quotes, $this->activeQuote);
            Flux::toast('Quote declined.', 'success');
        } catch (\DomainException $e) {
            Flux::toast($e->getMessage(), 'error');
        }
    }

    public function invoiceQuote(int $quoteId): void
    {
        $this->authorize('update', $this->maintenanceRequest);

        $quote = MaintenanceQuote::findOrFail($quoteId);

        try {
            app(MaintenanceQuoteService::class)->markInvoiced($quote);
            unset($this->quotes, $this->activeQuote);
            Flux::toast('Quote converted to invoice.', 'success');
        } catch (\DomainException $e) {
            Flux::toast($e->getMessage(), 'error');
        }
    }

    public function payQuote(int $quoteId): void
    {
        $this->authorize('update', $this->maintenanceRequest);

        $quote = MaintenanceQuote::findOrFail($quoteId);

        try {
            app(MaintenanceQuoteService::class)->markPaid($quote);
            unset($this->quotes, $this->activeQuote);
            Flux::toast('Invoice marked as paid.', 'success');
        } catch (\DomainException $e) {
            Flux::toast($e->getMessage(), 'error');
        }
    }

    public function render()
    {
        return view('livewire.maintenance-requests.show')
            ->layout('layouts.app')
            ->title(__('Maintenance Request'));
    }
}
