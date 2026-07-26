<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandlordPayoutAccount extends Model
{
    protected $fillable = [
        'landlord_id',
        'label',
        'method',
        'account_number',
        'account_name',
        'instructions',
        'is_primary',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
    ];

    public const METHODS = [
        'd_money' => 'D-Money',
        'waafi' => 'Waafi Pay',
        'cac_bank' => 'Cac Bank',
        'bank_transfer' => 'Bank Transfer',
        'cash' => 'Cash',
        'other' => 'Other',
    ];

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function getMethodLabelAttribute(): string
    {
        return self::METHODS[$this->method] ?? ucfirst($this->method);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
