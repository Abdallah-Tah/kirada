<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contract extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'landlord_id',
        'lease_id',
        'property_id',
        'unit_id',
        'tenant_id',
        'document_id',
        'created_by',
        'reference',
        'type',
        'title',
        'locale',
        'status',
        'body_html',
        'variables',
        'sent_at',
        'completed_at',
    ];

    protected $casts = [
        'variables' => 'array',
        'sent_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function signatures(): HasMany
    {
        return $this->hasMany(ContractSignature::class)->orderBy('sign_order');
    }

    // ── Scopes ──────────────────────────────────────────

    public function scopeForLandlord(Builder $query, int $landlordId): Builder
    {
        return $query->where('landlord_id', $landlordId);
    }

    // ── Helpers ─────────────────────────────────────────

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return in_array($this->status, ['cancelled', 'declined'], true);
    }

    /**
     * True when every signer has signed.
     */
    public function allSigned(): bool
    {
        $signatures = $this->relationLoaded('signatures') ? $this->signatures : $this->signatures()->get();

        return $signatures->isNotEmpty()
            && $signatures->every(fn (ContractSignature $s) => $s->status === 'signed');
    }

    public function signedCount(): int
    {
        $signatures = $this->relationLoaded('signatures') ? $this->signatures : $this->signatures()->get();

        return $signatures->where('status', 'signed')->count();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft'     => 'Draft',
            'sent'      => 'Awaiting signatures',
            'signed'    => 'Partially signed',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'declined'  => 'Declined',
            default     => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'green',
            'sent', 'signed' => 'amber',
            'cancelled', 'declined' => 'red',
            default => 'slate',
        };
    }
}
