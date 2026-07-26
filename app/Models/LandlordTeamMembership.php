<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandlordTeamMembership extends Model
{
    public const ROLES = [
        'landlord-admin',
        'property-manager',
        'accountant',
        'viewer',
    ];

    protected $fillable = [
        'landlord_id',
        'user_id',
        'invited_by',
        'email',
        'role',
        'token_hash',
        'status',
        'expires_at',
        'accepted_at',
    ];

    protected $hidden = ['token_hash'];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')->whereNotNull('accepted_at');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && $this->expires_at->isFuture();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->accepted_at !== null;
    }
}
