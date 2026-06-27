<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'symbol', 'decimals', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'country_currency')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Format an amount in this currency.
     */
    public function format(float $amount): string
    {
        $formatted = number_format($amount, $this->decimals);

        return $this->symbol
            ? "{$formatted} {$this->symbol}"
            : "{$formatted} {$this->code}";
    }
}