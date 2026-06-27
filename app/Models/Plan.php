<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'monthly_price',
        'currency_id',
        'max_active_units',
        'max_active_leases',
        'is_active',
    ];

    protected $casts = [
        'monthly_price'      => 'float',
        'max_active_units'   => 'integer',
        'max_active_leases'  => 'integer',
        'is_active'          => 'boolean',
    ];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getFormattedPriceAttribute(): string
    {
        $currency = $this->currency;
        if (!$currency) {
            return number_format($this->monthly_price, 0) . ' DJF';
        }

        return $currency->format($this->monthly_price);
    }

    public function getLimitsLabelAttribute(): string
    {
        if ($this->max_active_units === null) {
            return 'Unlimited';
        }

        return "Up to {$this->max_active_units} units";
    }
}