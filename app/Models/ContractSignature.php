<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'party_role',
        'name',
        'email',
        'typed_name',
        'sign_order',
        'token',
        'status',
        'signature_data',
        'signature_hash',
        'expires_at',
        'signed_at',
        'signed_ip',
        'signed_user_agent',
        'decline_reason',
    ];

    protected $casts = [
        'sign_order' => 'integer',
        'expires_at' => 'datetime',
        'signed_at' => 'datetime',
    ];

    /**
     * Keep the raw signature image out of array/JSON output by default.
     */
    protected $hidden = [
        'signature_data',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function isSigned(): bool
    {
        return $this->status === 'signed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->party_role) {
            'bailleur' => 'Bailleur (Landlord)',
            'preneur' => 'Preneur (Tenant)',
            'temoin' => 'Témoin (Witness)',
            default => ucfirst($this->party_role),
        };
    }
}
