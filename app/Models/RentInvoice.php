<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'landlord_id',
        'lease_id',
        'property_id',
        'unit_id',
        'tenant_id',
        'invoice_number',
        'payment_reference',
        'invoice_month',
        'due_date',
        'amount',
        'currency_id',
        'status',
        'notes',
        'is_auto_generated',
        'sent_at',
        'reminders_sent',
    ];

    protected $casts = [
        'invoice_month' => 'date',
        'due_date' => 'date',
        'amount' => 'float',
        'is_auto_generated' => 'boolean',
        'sent_at' => 'datetime',
        'reminders_sent' => 'array',
    ];

    // ── Relationships ──────────────────────────────────

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id');
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

    public function lineItems(): HasMany
    {
        return $this->hasMany(RentInvoiceLineItem::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(RentPayment::class);
    }

    // ── Scopes ──────────────────────────────────────────

    public function scopeForLandlord(Builder $query, int $landlordId): Builder
    {
        return $query->where('landlord_id', $landlordId);
    }

    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->whereIn('status', ['unpaid', 'partially_paid', 'overdue']);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'overdue');
    }

    public function scopeActionable(Builder $query): Builder
    {
        return $query->whereIn('status', ['unpaid', 'partially_paid', 'overdue', 'sent']);
    }

    // ── Helpers ─────────────────────────────────────────

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isActionable(): bool
    {
        return \in_array($this->status, ['unpaid', 'partially_paid', 'overdue', 'sent'], true);
    }

    public function reminderWasSent(string $key): bool
    {
        return isset(($this->reminders_sent ?? [])[$key]);
    }

    public function markReminderSent(string $key): void
    {
        $sent = $this->reminders_sent ?? [];
        $sent[$key] = now()->toDateString();
        $this->update(['reminders_sent' => $sent]);
    }

    public function lateFeeTotal(): float
    {
        return (float) $this->lineItems()->where('type', 'late_fee')->sum('amount');
    }

    public function totalDue(): float
    {
        return $this->amount + $this->lateFeeTotal();
    }

    /**
     * The currency this invoice is denominated in: its own currency,
     * falling back to the property's currency (legacy rows).
     */
    public function displayCurrency(): ?Currency
    {
        return $this->currency ?? $this->property?->currency;
    }

    public function getFormattedAmountAttribute(): string
    {
        return Money::format($this->amount, $this->displayCurrency());
    }

    public function getFormattedTotalDueAttribute(): string
    {
        return Money::format($this->totalDue(), $this->displayCurrency());
    }

    public function getInvoiceMonthFormattedAttribute(): string
    {
        return $this->invoice_month?->format('F Y') ?? '';
    }
}
