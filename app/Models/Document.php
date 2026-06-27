<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'landlord_id',
        'tenant_id',
        'lease_id',
        'rent_invoice_id',
        'rent_payment_id',
        'uploaded_by',
        'title',
        'type',
        'file_path',
        'original_filename',
        'mime_type',
        'size',
        'visibility',
    ];

    protected $casts = [
        'size' => 'integer',
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

    public function rentInvoice(): BelongsTo
    {
        return $this->belongsTo(RentInvoice::class);
    }

    public function rentPayment(): BelongsTo
    {
        return $this->belongsTo(RentPayment::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // ── Scopes ──────────────────────────────────────────

    public function scopeForLandlord(Builder $query, int $landlordId): Builder
    {
        return $query->where('landlord_id', $landlordId);
    }

    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId)->where('visibility', 'tenant_visible');
    }

    // ── Helpers ─────────────────────────────────────────

    public function getFormattedSizeAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->size;
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 1) . ' ' . $units[$i];
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'lease_agreement'  => 'Lease Agreement',
            'payment_receipt'  => 'Payment Receipt',
            'payment_proof'    => 'Payment Proof',
            'id_document'      => 'ID Document',
            'other'            => 'Other',
            default            => ucfirst($this->type),
        };
    }
}