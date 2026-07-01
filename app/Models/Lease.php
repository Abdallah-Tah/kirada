<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Lease extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'landlord_id',
        'property_id',
        'unit_id',
        'tenant_id',
        'start_date',
        'end_date',
        'monthly_rent',
        'security_deposit',
        'payment_due_day',
        'status',
        'notes',
        'auto_generate_invoices',
        'invoice_generation_days_before_due',
        'grace_period_days',
        'late_fee_type',
        'late_fee_amount',
        'late_fee_frequency',
        'reminder_schedule',
    ];

    protected $casts = [
        'start_date'                         => 'date',
        'end_date'                           => 'date',
        'monthly_rent'                       => 'float',
        'security_deposit'                   => 'float',
        'payment_due_day'                    => 'integer',
        'auto_generate_invoices'             => 'boolean',
        'invoice_generation_days_before_due' => 'integer',
        'grace_period_days'                  => 'integer',
        'late_fee_amount'                    => 'float',
        'reminder_schedule'                  => 'array',
    ];

    protected $attributes = [
        'auto_generate_invoices'             => true,
        'invoice_generation_days_before_due' => 7,
        'grace_period_days'                  => 5,
        'late_fee_type'                      => 'none',
        'late_fee_frequency'                 => 'once',
    ];

    // ── Relationships ──────────────────────────────────

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id');
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

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isEnded(): bool
    {
        return $this->status === 'ended';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getDurationInDaysAttribute(): int
    {
        $end = $this->end_date ?? Carbon::now();

        return (int) $this->start_date->diffInDays($end);
    }
}