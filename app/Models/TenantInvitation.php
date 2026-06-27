<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class TenantInvitation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'landlord_id',
        'tenant_id',
        'email',
        'phone',
        'token',
        'status',
        'expires_at',
        'accepted_at',
        'accepted_user_id',
    ];

    protected $casts = [
        'expires_at'  => 'datetime',
        'accepted_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function acceptedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_user_id');
    }

    // ── Scopes ──────────────────────────────────────────

    public function scopeForLandlord(Builder $query, int $landlordId): Builder
    {
        return $query->where('landlord_id', $landlordId);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    // ── Helpers ─────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired'
            || ($this->isPending() && $this->expires_at->isPast());
    }

    public function getAcceptUrlAttribute(): string
    {
        return url('/tenant-invitations/' . $this->token);
    }
}