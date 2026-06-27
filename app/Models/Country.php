<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'code2', 'name', 'dial_code', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function currencies(): BelongsToMany
    {
        return $this->belongsToMany(Currency::class, 'country_currency')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    public function defaultCurrency()
    {
        return $this->currencies()->wherePivot('is_default', true)->first();
    }

    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}