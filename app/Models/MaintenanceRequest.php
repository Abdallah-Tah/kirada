<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'landlord_id',
        'tenant_id',
        'lease_id',
        'property_id',
        'unit_id',
        'title',
        'description',
        'category',
        'location',
        'permission_to_enter',
        'preferred_access_window',
        'priority',
        'status',
        'assigned_to',
        'reported_by',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'permission_to_enter' => 'boolean',
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

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(MaintenanceComment::class)->latest();
    }

    public function publicComments(): HasMany
    {
        return $this->hasMany(MaintenanceComment::class)->where('is_internal', false)->latest();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MaintenanceAttachment::class);
    }

    public function publicAttachments(): HasMany
    {
        return $this->hasMany(MaintenanceAttachment::class)->where('is_internal', false);
    }

    // ── Scopes ──────────────────────────────────────────

    public function scopeForLandlord(Builder $query, int $landlordId): Builder
    {
        return $query->where('landlord_id', $landlordId);
    }

    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    // ── Helpers ─────────────────────────────────────────

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'red',
            'high' => 'orange',
            'medium' => 'blue',
            'low' => 'zinc',
            default => 'zinc',
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'plumbing' => 'Plumbing',
            'electrical' => 'Electrical',
            'ac_heating' => 'AC / Heating',
            'appliance' => 'Appliance',
            'door_lock' => 'Door / Lock',
            'pest' => 'Pest',
            'cleaning' => 'Cleaning',
            'safety' => 'Safety',
            default => 'Other',
        };
    }
}
