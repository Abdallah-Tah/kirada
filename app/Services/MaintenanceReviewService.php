<?php

namespace App\Services;

use App\Models\MaintenanceRequest;
use App\Models\MaintenanceReview;
use App\Models\User;

class MaintenanceReviewService
{
    /**
     * Reviews are purchase-verified: one review for a completed work order,
     * written by the landlord account that hired the assigned provider.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(User $actor, MaintenanceRequest $request, array $data): MaintenanceReview
    {
        if (! $actor->can('maintenance.respond') || ! $actor->belongsToLandlordAccount($request->landlord_id)) {
            throw new \DomainException('You cannot review this maintenance job.');
        }

        if (! in_array($request->status, ['resolved', 'closed'], true) || ! $request->assigned_to) {
            throw new \DomainException('Only completed jobs assigned to a provider can be reviewed.');
        }

        if (! $request->assignee?->isMaintenance()) {
            throw new \DomainException('The assigned account is not a maintenance provider.');
        }

        if ($request->review()->exists()) {
            throw new \DomainException('This completed job has already been reviewed.');
        }

        return MaintenanceReview::create([
            'maintenance_request_id' => $request->id,
            'landlord_id' => $request->landlord_id,
            'maintenance_user_id' => $request->assigned_to,
            'rating' => $data['rating'],
            'quality_rating' => $data['quality_rating'],
            'communication_rating' => $data['communication_rating'],
            'professionalism_rating' => $data['professionalism_rating'],
            'title' => $data['title'] ?? null,
            'comment' => $data['comment'] ?? null,
        ]);
    }
}
