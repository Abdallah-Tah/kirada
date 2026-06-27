<?php

namespace App\Services;

use App\Models\MaintenanceComment;
use App\Models\MaintenanceRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class MaintenanceRequestService
{
    /**
     * Create a maintenance request.
     * Auto-resolves landlord_id from the property if the reporter is a tenant.
     */
    public function createRequest(array $data, User $reporter): MaintenanceRequest
    {
        // If tenant is creating, resolve landlord from property
        if ($reporter->hasRole('tenant') && !isset($data['landlord_id'])) {
            $tenant = Tenant::where('user_id', $reporter->id)->first();
            if ($tenant) {
                $data['tenant_id'] = $tenant->id;
                $data['landlord_id'] = $tenant->landlord_id;
            }
        }

        // If landlord is creating for a tenant
        if ($reporter->hasRole('landlord') && !isset($data['landlord_id'])) {
            $data['landlord_id'] = $reporter->id;
        }

        $data['reported_by'] = $reporter->id;

        if (!isset($data['status'])) {
            $data['status'] = 'open';
        }

        return MaintenanceRequest::create($data);
    }

    /**
     * Assign a maintenance request to a maintenance-role user.
     */
    public function assignRequest(MaintenanceRequest $request, int $assigneeId): MaintenanceRequest
    {
        $assignee = User::findOrFail($assigneeId);

        if (!$assignee->hasRole('maintenance')) {
            throw new \DomainException('Can only assign to users with the maintenance role.');
        }

        $request->update([
            'assigned_to' => $assigneeId,
            'status' => 'in_progress',
        ]);

        return $request->fresh();
    }

    /**
     * Transition status through allowed paths.
     */
    public function changeStatus(MaintenanceRequest $request, string $newStatus): MaintenanceRequest
    {
        $allowed = $this->getAllowedTransitions($request->status);

        if (!in_array($newStatus, $allowed)) {
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

        $request->update($updates);

        return $request->fresh();
    }

    /**
     * Get allowed status transitions from a given status.
     */
    public function getAllowedTransitions(string $currentStatus): array
    {
        return match ($currentStatus) {
            'open'        => ['in_progress', 'cancelled'],
            'in_progress' => ['resolved', 'cancelled'],
            'resolved'    => ['closed', 'in_progress'],
            'closed'      => ['in_progress'],
            'cancelled'   => ['open'],
            default       => [],
        };
    }

    /**
     * Add a comment to a maintenance request.
     */
    public function addComment(MaintenanceRequest $request, User $user, string $comment, bool $isInternal = false): MaintenanceComment
    {
        // Only admin/landlord can add internal comments
        if ($isInternal && !$user->hasRole('admin') && $request->landlord_id !== $user->id) {
            $isInternal = false;
        }

        return MaintenanceComment::create([
            'maintenance_request_id' => $request->id,
            'user_id'                => $user->id,
            'comment'                => $comment,
            'is_internal'            => $isInternal,
        ]);
    }

    /**
     * Get comments visible to the given user.
     * Tenants cannot see internal comments.
     */
    public function getVisibleComments(MaintenanceRequest $request, User $user): Collection
    {
        if ($user->hasRole('admin') || $request->landlord_id === $user->id) {
            return $request->comments()->with('user:id,name')->get();
        }

        return $request->publicComments()->with('user:id,name')->get();
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
}