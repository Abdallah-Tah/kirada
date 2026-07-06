<?php

namespace App\Services;

use App\Models\MaintenanceAttachment;
use App\Models\MaintenanceComment;
use App\Models\MaintenanceRequest;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\MaintenanceCommentAdded;
use App\Notifications\MaintenanceRequestCreated;
use App\Notifications\MaintenanceStatusChanged;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class MaintenanceRequestService
{
    /**
     * Create a maintenance request.
     * Auto-resolves landlord_id from the property if the reporter is a tenant.
     */
    public function createRequest(array $data, User $reporter): MaintenanceRequest
    {
        // If tenant is creating, resolve landlord from property
        if ($reporter->hasRole('tenant') && ! isset($data['landlord_id'])) {
            $tenant = Tenant::where('user_id', $reporter->id)->first();
            if ($tenant) {
                $data['tenant_id'] = $tenant->id;
                $data['landlord_id'] = $tenant->landlord_id;
            }
        }

        // If landlord is creating for a tenant
        if ($reporter->hasRole('landlord') && ! isset($data['landlord_id'])) {
            $data['landlord_id'] = $reporter->id;
        }

        $data['reported_by'] = $reporter->id;

        if (! isset($data['status'])) {
            $data['status'] = 'open';
        }

        $request = MaintenanceRequest::create($data);
        $request->loadMissing(['landlord', 'tenant.user', 'property', 'reporter']);

        // Notify all relevant parties about the new request
        foreach ($this->notificationRecipients($request, $reporter) as $recipient) {
            $recipient->notify(new MaintenanceRequestCreated($request));
        }

        return $request;
    }

    /**
     * Assign a maintenance request to a maintenance-role user.
     */
    public function assignRequest(MaintenanceRequest $request, int $assigneeId): MaintenanceRequest
    {
        $assignee = User::findOrFail($assigneeId);

        if (! $assignee->hasRole('maintenance')) {
            throw new \DomainException('Can only assign to users with the maintenance role.');
        }

        $request->update([
            'assigned_to' => $assigneeId,
            'status' => 'in_progress',
        ]);

        $fresh = $request->fresh(['reporter', 'tenant.user', 'landlord']);
        $this->notifyStatusChanged($fresh, 'in_progress', 'open');

        return $fresh;
    }

    /**
     * Transition status through allowed paths.
     */
    public function changeStatus(MaintenanceRequest $request, string $newStatus, ?User $actor = null): MaintenanceRequest
    {
        $allowed = $actor
            ? $this->getAllowedTransitionsForUser($request, $actor)
            : $this->getAllowedTransitions($request->status);

        if (! in_array($newStatus, $allowed)) {
            throw new \DomainException(
                "Cannot transition from '{$request->status}' to '{$newStatus}'."
            );
        }

        $updates = ['status' => $newStatus];

        if ($newStatus === 'resolved') {
            $updates['resolved_at'] = now();
        }

        if ($newStatus === 'closed') {
            $updates['closed_at'] = now();
        }

        if ($newStatus === 'cancelled' || $newStatus === 'open') {
            $updates['resolved_at'] = null;
            $updates['closed_at'] = null;
        }

        $previousStatus = $request->status;
        $request->update($updates);

        $fresh = $request->fresh(['reporter', 'tenant.user', 'landlord']);
        $this->notifyStatusChanged($fresh, $newStatus, $previousStatus);

        return $fresh;
    }

    /**
     * Get allowed status transitions from a given status.
     */
    public function getAllowedTransitions(string $currentStatus): array
    {
        return match ($currentStatus) {
            'open' => ['in_progress', 'cancelled'],
            'in_progress' => ['resolved', 'cancelled'],
            'resolved' => ['closed', 'in_progress'],
            'closed' => ['in_progress'],
            'cancelled' => ['open'],
            default => [],
        };
    }

    /**
     * Get status transitions allowed for the current actor.
     */
    public function getAllowedTransitionsForUser(MaintenanceRequest $request, User $user): array
    {
        if ($user->hasRole('admin') || $request->landlord_id === $user->id) {
            return $this->getAllowedTransitions($request->status);
        }

        if ($user->hasRole('maintenance') && $request->assigned_to === $user->id) {
            return match ($request->status) {
                'open' => ['in_progress'],
                'in_progress' => ['resolved'],
                'resolved' => ['in_progress'],
                default => [],
            };
        }

        if ($user->hasRole('tenant') && $request->tenant?->user_id === $user->id) {
            return match ($request->status) {
                'open' => ['cancelled'],
                'resolved' => ['closed', 'in_progress'],
                default => [],
            };
        }

        return [];
    }

    /**
     * Add a comment to a maintenance request.
     */
    public function addComment(MaintenanceRequest $request, User $user, string $comment, bool $isInternal = false): MaintenanceComment
    {
        // Only admin/landlord can add internal comments
        if ($isInternal && ! $user->hasRole('admin') && $request->landlord_id !== $user->id) {
            $isInternal = false;
        }

        $maintenanceComment = MaintenanceComment::create([
            'maintenance_request_id' => $request->id,
            'user_id' => $user->id,
            'comment' => $comment,
            'is_internal' => $isInternal,
        ]);

        $this->notifyCommentAdded($request->fresh(['landlord', 'tenant.user', 'assignee', 'reporter']), $maintenanceComment, $user);

        return $maintenanceComment;
    }

    /**
     * Store photos/files attached to either the original request or a comment.
     *
     * @param  iterable<UploadedFile>  $files
     */
    public function storeAttachments(
        MaintenanceRequest $request,
        User $user,
        iterable $files,
        ?MaintenanceComment $comment = null,
        string $kind = 'initial',
        bool $isInternal = false,
    ): void {
        if ($isInternal && ! $user->hasRole('admin') && $request->landlord_id !== $user->id) {
            $isInternal = false;
        }

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store('maintenance-attachments', 'private');

            MaintenanceAttachment::create([
                'maintenance_request_id' => $request->id,
                'maintenance_comment_id' => $comment?->id,
                'uploaded_by' => $user->id,
                'disk' => 'private',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize() ?: 0,
                'kind' => $kind,
                'is_internal' => $isInternal,
            ]);
        }
    }

    /**
     * Get comments visible to the given user.
     * Tenants cannot see internal comments.
     */
    public function getVisibleComments(MaintenanceRequest $request, User $user): Collection
    {
        if ($user->hasRole('admin') || $request->landlord_id === $user->id) {
            return $request->comments()->with(['user:id,name', 'attachments'])->get();
        }

        return $request->publicComments()
            ->with(['user:id,name', 'attachments' => fn ($query) => $query->where('is_internal', false)])
            ->get();
    }

    /**
     * Get maintenance-role users for assignment dropdown.
     */
    public function getMaintenanceUsers(?int $landlordId = null): Collection
    {
        // For now, all maintenance users are available
        // Future: scope by landlord or property assignment
        return User::role('maintenance')->select('id', 'name')->orderBy('name')->get();
    }

    private function notifyStatusChanged(MaintenanceRequest $request, string $status, string $previousStatus = null): void
    {
        foreach ($this->notificationRecipients($request) as $recipient) {
            $recipient->notify(new MaintenanceStatusChanged($request, $status, $previousStatus));
        }
    }

    private function notifyCommentAdded(MaintenanceRequest $request, MaintenanceComment $comment, User $author): void
    {
        foreach ($this->notificationRecipients($request, $author) as $recipient) {
            if ($comment->is_internal && ! $recipient->hasRole('admin') && $request->landlord_id !== $recipient->id) {
                continue;
            }

            $recipient->notify(new MaintenanceCommentAdded($request, $comment));
        }
    }

    /**
     * @return array<int, User>
     */
    private function notificationRecipients(MaintenanceRequest $request, ?User $except = null): array
    {
        $users = collect([
            $request->landlord,
            $request->tenant?->user,
            $request->assignee,
            $request->reporter,
        ])
            ->filter()
            ->unique('id');

        if ($except) {
            $users = $users->reject(fn (User $user) => $user->is($except));
        }

        return $users->values()->all();
    }
}
