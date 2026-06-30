<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $type
 * @property string $version
 * @property Carbon $effective_date
 * @property string $content_hash
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class LegalDocument extends Model
{
    protected $fillable = [
        'type',
        'version',
        'effective_date',
        'content_hash',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function acceptances(): HasMany
    {
        return $this->hasMany(LegalAcceptance::class);
    }

    /**
     * Get the active document for a given type.
     */
    public static function activeFor(string $type): ?self
    {
        return static::where('type', $type)
            ->where('is_active', true)
            ->latest('effective_date')
            ->first();
    }
}