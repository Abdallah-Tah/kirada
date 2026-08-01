<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'invoice_delivery_channels',
        'reminder_delivery_channels',
        'auto_send_invoice_override',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'monthly_rent' => 'float',
        'security_deposit' => 'float',
        'payment_due_day' => 'integer',
        'auto_generate_invoices' => 'boolean',
        'invoice_generation_days_before_due' => 'integer',
        'grace_period_days' => 'integer',
        'late_fee_amount' => 'float',
        'reminder_schedule' => 'array',
        'invoice_delivery_channels' => 'array',
        'reminder_delivery_channels' => 'array',
        'auto_send_invoice_override' => 'boolean',
    ];

    protected $attributes = [
        'auto_generate_invoices' => true,
        'invoice_generation_days_before_due' => 7,
        'grace_period_days' => 5,
        'late_fee_type' => 'none',
        'late_fee_frequency' => 'once',
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

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(RentInvoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(RentPayment::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
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

    /**
     * Limit the query to active leases whose fixed term ends within the next
     * number of days (including today). Open-ended leases are intentionally
     * excluded because they do not need renewal follow-up.
     */
    public function scopeExpiringWithin(Builder $query, int $days = 30): Builder
    {
        $today = Carbon::today();

        return $query
            ->active()
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [
                $today->toDateString(),
                $today->copy()->addDays(max(0, $days))->toDateString(),
            ]);
    }

    /**
     * Active leases whose fixed term has already ended but were not closed.
     * This is a reporting signal; it never mutates the lease automatically.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query
            ->active()
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', Carbon::today());
    }

    // ── Helpers ─────────────────────────────────────────

    public function getLeaseNumberAttribute(): string
    {
        return '#L-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function getFormattedRentAttribute(): string
    {
        return Money::format($this->monthly_rent, $this->property?->currency);
    }

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

    public function getDaysUntilEndAttribute(): ?int
    {
        if (! $this->end_date) {
            return null;
        }

        return Carbon::today()->diffInDays($this->end_date, false);
    }

    public function isExpiredTerm(): bool
    {
        return $this->isActive() && $this->days_until_end !== null && $this->days_until_end < 0;
    }

    public function isExpiringWithin(int $days = 30): bool
    {
        return $this->isActive()
            && $this->days_until_end !== null
            && $this->days_until_end >= 0
            && $this->days_until_end <= max(0, $days);
    }

    public function getDurationInDaysAttribute(): int
    {
        $end = $this->end_date ?? Carbon::now();

        return (int) $this->start_date->diffInDays($end);
    }
}
