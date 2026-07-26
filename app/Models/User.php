<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $country_id
 * @property string|null $preferred_language
 * @property string|null $phone_country_code
 * @property Carbon|null $terms_accepted_at
 * @property Carbon|null $privacy_accepted_at
 */
#[Fillable(['name', 'email', 'password', 'country_id', 'preferred_language', 'phone_country_code', 'address', 'city', 'postal_code', 'terms_accepted_at', 'privacy_accepted_at', 'stripe_customer_id'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'terms_accepted_at' => 'datetime',
            'privacy_accepted_at' => 'datetime',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }

    // ── Role helpers ────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * @return HasMany<LandlordPayoutAccount, $this>
     */
    public function payoutAccounts(): HasMany
    {
        return $this->hasMany(LandlordPayoutAccount::class, 'landlord_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * @return HasOne<LandlordPayoutAccount, $this>
     */
    public function primaryPayoutAccount(): HasOne
    {
        return $this->hasOne(LandlordPayoutAccount::class, 'landlord_id')
            ->where('is_primary', true)
            ->where('is_active', true);
    }

    public function isLandlord(): bool
    {
        return $this->hasRole('landlord');
    }

    public function isLandlordTeamMember(): bool
    {
        return $this->hasAnyRole(LandlordTeamMembership::ROLES)
            && $this->teamMembership?->isActive();
    }

    public function canAccessLandlordPortal(): bool
    {
        return $this->isLandlord() || $this->isLandlordTeamMember();
    }

    public function landlordAccountId(): ?int
    {
        if ($this->isLandlord()) {
            return $this->id;
        }

        return $this->teamMembership?->isActive()
            ? $this->teamMembership->landlord_id
            : null;
    }

    public function landlordAccount(): ?self
    {
        if ($this->isLandlord()) {
            return $this;
        }

        return $this->teamMembership?->isActive()
            ? $this->teamMembership->landlord
            : null;
    }

    public function belongsToLandlordAccount(?int $landlordId): bool
    {
        return $landlordId !== null && $this->landlordAccountId() === $landlordId;
    }

    public function teamMembership(): HasOne
    {
        return $this->hasOne(LandlordTeamMembership::class)->with('landlord');
    }

    public function landlordTeamMembers(): HasMany
    {
        return $this->hasMany(LandlordTeamMembership::class, 'landlord_id');
    }

    public function isTenant(): bool
    {
        return $this->hasRole('tenant');
    }

    public function isMaintenance(): bool
    {
        return $this->hasRole('maintenance');
    }

    // ── Subscription helpers ────────────────────────────

    /**
     * @return HasOne<Subscription, $this>
     */
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function onTrial(): bool
    {
        $sub = $this->subscription;

        return $sub && $sub->trialIsActive();
    }

    public function hasActiveSubscription(): bool
    {
        $sub = $this->subscription;

        return $sub && $sub->isActive();
    }

    public function trialExpired(): bool
    {
        $sub = $this->subscription;

        return $sub && $sub->trialHasExpired();
    }

    public function needsSubscription(): bool
    {
        $account = $this->landlordAccount();

        return $account !== null
            && ! $account->onTrial()
            && ! $account->hasActiveSubscription();
    }

    // ── Maintenance provider network ────────────────────

    /**
     * Maintenance workers linked to this landlord, in any state. Callers that
     * need only usable workers should go through approvedMaintenanceUsers().
     *
     * @return BelongsToMany<User, $this, LandlordMaintenance>
     */
    public function maintenanceConnections(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'landlord_maintenance', 'landlord_id', 'maintenance_user_id')
            ->using(LandlordMaintenance::class)
            ->withPivot(['status', 'requested_by', 'approved_at', 'rejected_at', 'message'])
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<User, $this, LandlordMaintenance>
     */
    public function approvedMaintenanceUsers(): BelongsToMany
    {
        return $this->maintenanceConnections()->wherePivotNotNull('approved_at');
    }

    /**
     * Landlords linked to this maintenance worker, in any state.
     *
     * @return BelongsToMany<User, $this, LandlordMaintenance>
     */
    public function landlordConnections(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'landlord_maintenance', 'maintenance_user_id', 'landlord_id')
            ->using(LandlordMaintenance::class)
            ->withPivot(['status', 'requested_by', 'approved_at', 'rejected_at', 'message'])
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<User, $this, LandlordMaintenance>
     */
    public function approvedLandlords(): BelongsToMany
    {
        return $this->landlordConnections()->wherePivotNotNull('approved_at');
    }

    /**
     * @return HasOne<MaintenanceProfile, $this>
     */
    public function maintenanceProfile(): HasOne
    {
        return $this->hasOne(MaintenanceProfile::class);
    }

    public function maintenanceReviews(): HasMany
    {
        return $this->hasMany(MaintenanceReview::class, 'maintenance_user_id');
    }

    public function assignedMaintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class, 'assigned_to');
    }
}
