<?php

namespace App\Services;

use App\Models\MaintenanceProfile;
use App\Models\User;
use App\Notifications\MaintenanceConnectionRequested;
use App\Notifications\MaintenanceConnectionResolved;
use App\Support\Locales;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class MaintenanceProfileService
{
    /**
     * Create or update the calling provider's directory profile.
     *
     * @param  array<string, mixed>  $data
     */
    public function saveProfile(User $provider, array $data): MaintenanceProfile
    {
        if (! $provider->isMaintenance()) {
            throw new \DomainException('Only maintenance accounts can publish a provider profile.');
        }

        $trades = array_values(array_intersect(
            $data['trades'] ?? [],
            MaintenanceProfile::TRADES,
        ));

        if ($trades === []) {
            throw new \DomainException('Select at least one trade.');
        }

        $profile = $provider->maintenanceProfile ?? new MaintenanceProfile(['user_id' => $provider->id]);

        $wasVerified = $profile->exists && $profile->isVerified();

        $profile->fill([
            'business_name' => $data['business_name'],
            'headline' => $data['headline'] ?? null,
            'bio' => $data['bio'] ?? null,
            'trades' => $trades,
            'service_areas' => array_values(array_filter($data['service_areas'] ?? [])),
            'languages' => array_values(array_unique(array_filter($data['languages'] ?? []))),
            'currency_id' => $data['currency_id'] ?? null,
            'hourly_rate' => $data['hourly_rate'] ?? null,
            'callout_fee' => $data['callout_fee'] ?? null,
            'phone' => $data['phone'] ?? null,
            'whatsapp' => $data['whatsapp'] ?? null,
            'website' => $data['website'] ?? null,
            'years_experience' => $data['years_experience'] ?? null,
            'availability_status' => $data['availability_status'] ?? 'available',
            'is_published' => (bool) ($data['is_published'] ?? false),
        ]);

        // Changing the advertised business identity invalidates a prior review:
        // the badge must not carry over to details an admin never saw.
        if ($wasVerified && $profile->isDirty('business_name')) {
            $profile->verified_at = null;
            $profile->verified_by = null;
        }

        $profile->save();

        return $profile->refresh();
    }

    /**
     * The landlord-facing directory: published profiles only, newest verified first.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, MaintenanceProfile>
     */
    public function directory(array $filters = []): LengthAwarePaginator
    {
        return MaintenanceProfile::query()
            ->published()
            ->with([
                'user' => fn ($query) => $query
                    ->select('id', 'name')
                    ->withCount([
                        'assignedMaintenanceRequests as completed_jobs_count' => fn ($query) => $query
                            ->whereIn('status', ['resolved', 'closed']),
                    ]),
                'currency',
            ])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('business_name', 'like', "%{$search}%")
                        ->orWhere('bio', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['trade'] ?? null, fn (Builder $query, string $trade) => $query->withTrade($trade))
            ->when($filters['area'] ?? null, fn (Builder $query, string $area) => $query->whereJsonContains('service_areas', $area))
            ->when($filters['verified_only'] ?? false, fn (Builder $query) => $query->verified())
            ->orderByRaw('CASE WHEN verified_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('business_name')
            ->paginate(12)
            ->withQueryString();
    }

    /**
     * A landlord asks a provider to join their approved list. Idempotent: an
     * existing pending or approved link is returned untouched so repeated
     * clicks cannot spam the provider.
     */
    public function requestConnection(User $landlord, User $provider, ?string $message = null): string
    {
        $this->assertPairing($landlord, $provider);

        $existing = $landlord->maintenanceConnections()
            ->where('users.id', $provider->id)
            ->first();

        if ($existing && in_array($existing->pivot->status, ['pending', 'approved'], true)) {
            return $existing->pivot->status;
        }

        $attributes = [
            'status' => 'pending',
            'requested_by' => 'landlord',
            'approved_at' => null,
            'rejected_at' => null,
            'message' => $message,
        ];

        $existing
            ? $landlord->maintenanceConnections()->updateExistingPivot($provider->id, $attributes)
            : $landlord->maintenanceConnections()->attach($provider->id, $attributes);

        $provider->notify(
            (new MaintenanceConnectionRequested($landlord, $message))->locale(Locales::forLandlord($landlord)),
        );

        return 'pending';
    }

    /**
     * The provider accepts. Only then does approved_at get set, which is what
     * MaintenanceRequestService::assignRequest() gates assignment on.
     */
    public function approveConnection(User $provider, User $landlord): void
    {
        $this->assertPairing($landlord, $provider);

        $connection = $provider->landlordConnections()->where('users.id', $landlord->id)->first();

        if (! $connection) {
            throw new \DomainException('No pending request from this landlord.');
        }

        $provider->landlordConnections()->updateExistingPivot($landlord->id, [
            'status' => 'approved',
            'approved_at' => now(),
            'rejected_at' => null,
        ]);

        $landlord->notify(
            (new MaintenanceConnectionResolved($provider, 'approved'))->locale(Locales::forLandlord($landlord)),
        );
    }

    public function declineConnection(User $provider, User $landlord): void
    {
        $this->assertPairing($landlord, $provider);

        $connection = $provider->landlordConnections()->where('users.id', $landlord->id)->first();

        if (! $connection) {
            throw new \DomainException('No pending request from this landlord.');
        }

        $provider->landlordConnections()->updateExistingPivot($landlord->id, [
            'status' => 'rejected',
            'approved_at' => null,
            'rejected_at' => now(),
        ]);

        $landlord->notify(
            (new MaintenanceConnectionResolved($provider, 'rejected'))->locale(Locales::forLandlord($landlord)),
        );
    }

    /**
     * A landlord drops a provider. Existing requests already assigned to them
     * keep their history; only future assignment is revoked.
     */
    public function revokeConnection(User $landlord, User $provider): void
    {
        $this->assertPairing($landlord, $provider);

        DB::table('landlord_maintenance')
            ->where('landlord_id', $landlord->id)
            ->where('maintenance_user_id', $provider->id)
            ->delete();
    }

    private function assertPairing(User $landlord, User $provider): void
    {
        if (! $landlord->isLandlord() && ! $landlord->isAdmin()) {
            throw new \DomainException('Only landlords manage a maintenance network.');
        }

        if (! $provider->isMaintenance()) {
            throw new \DomainException('That account is not a maintenance provider.');
        }
    }
}
