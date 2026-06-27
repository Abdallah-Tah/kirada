<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'landlord_id',
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'email',
        'national_id',
        'address',
        'city',
        'status',
        'notes',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'landlord_id' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ── Scopes ──────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeForLandlord(Builder $query, int $landlordId): Builder
    {
        return $query->where('landlord_id', $landlordId);
    }

    // ── Helpers ─────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }
}