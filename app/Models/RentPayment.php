<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'landlord_id',
        'rent_invoice_id',
        'lease_id',
        'property_id',
        'unit_id',
        'tenant_id',
        'payment_number',
        'payment_date',
        'amount',
        'currency_id',
        'method',
        'status',
        'reference_number',
        'gateway_event_id',
        'proof_path',
        'notes',
        'confirmed_at',
        'confirmed_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'float',
        'confirmed_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function rentInvoice(): BelongsTo
    {
        return $this->belongsTo(RentInvoice::class);
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

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    // ── Scopes ──────────────────────────────────────────

    public function scopeForLandlord(Builder $query, int $landlordId): Builder
    {
        return $query->where('landlord_id', $landlordId);
    }

    public function scopeConfirmed(Builder $query): Builder
    {
        return $query->where('status', 'confirmed');
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

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * The currency this payment is denominated in: its own currency,
     * falling back to the invoice's, then the property's (legacy rows).
     */
    public function displayCurrency(): ?Currency
    {
        return $this->currency
            ?? $this->rentInvoice?->currency
            ?? $this->property?->currency;
    }

    public function getFormattedAmountAttribute(): string
    {
        return Money::format($this->amount, $this->displayCurrency());
    }
}
