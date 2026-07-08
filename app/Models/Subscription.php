<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'plan_id',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'status',
        'payment_method',
        'gateway',
        'gateway_subscription_id',
        'gateway_status',
        'notes',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'starts_at'      => 'datetime',
        'ends_at'        => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeTrialing(Builder $query): Builder
    {
        return $query->where('status', 'trialing');
    }

    public function isTrialing(): bool
    {
        return $this->status === 'trialing';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function trialIsActive(): bool
    {
        return $this->isTrialing()
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    public function trialHasExpired(): bool
    {
        return $this->isTrialing()
            && $this->trial_ends_at
            && $this->trial_ends_at->isPast();
    }
}