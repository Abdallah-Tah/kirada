<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceQuote extends Model
{
    protected $fillable = [
        'maintenance_request_id',
        'maintenance_user_id',
        'currency_id',
        'status',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total',
        'notes',
        'approved_at',
        'invoiced_at',
        'paid_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'approved_at' => 'datetime',
        'invoiced_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────

    public function maintenanceRequest(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    public function maintenanceUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'maintenance_user_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MaintenanceQuoteItem::class)->orderBy('sort_order');
    }

    // ── Helpers ─────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isInvoiced(): bool
    {
        return in_array($this->status, ['invoiced', 'paid']);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function getReferenceAttribute(): string
    {
        return 'KIR-MQ-'.str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    public function getFormattedTotalAttribute(): string
    {
        if ($this->currency) {
            return $this->currency->format((float) $this->total);
        }

        return number_format((float) $this->total, 2);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => __('Pending Approval'),
            'approved' => __('Approved'),
            'declined' => __('Declined'),
            'invoiced' => __('Invoiced'),
            'paid' => __('Paid'),
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'amber',
            'approved' => 'blue',
            'declined' => 'red',
            'invoiced' => 'violet',
            'paid' => 'green',
            default => 'zinc',
        };
    }

    /**
     * Recalculate subtotal/tax/total from the items.
     */
    public function recalculate(): void
    {
        $this->subtotal = $this->items()->sum('amount');
        $this->tax_amount = round($this->subtotal * ($this->tax_rate / 100), 2);
        $this->total = $this->subtotal + $this->tax_amount;
    }
}
