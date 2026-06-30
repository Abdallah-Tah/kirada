<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
#[Fillable(['name', 'email', 'password', 'country_id', 'preferred_language', 'phone_country_code', 'terms_accepted_at', 'privacy_accepted_at'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable, HasRoles;

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

    public function isLandlord(): bool
    {
        return $this->hasRole('landlord');
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

    public function subscription()
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
        return $this->isLandlord()
            && !$this->onTrial()
            && !$this->hasActiveSubscription();
    }
}
