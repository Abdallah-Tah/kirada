<?php

namespace App\Models;

use Database\Factories\MaintenanceProfileFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceProfile extends Model
{
    /** @use HasFactory<MaintenanceProfileFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'bio',
        'trades',
        'service_areas',
        'currency_id',
        'hourly_rate',
        'callout_fee',
        'phone',
        'whatsapp',
        'years_experience',
        'is_published',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'trades' => 'array',
        'service_areas' => 'array',
        'hourly_rate' => 'integer',
        'callout_fee' => 'integer',
        'years_experience' => 'integer',
        'is_published' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * The trades a provider can advertise. Keys are stored; labels are translated
     * at render time so the directory filters stay stable across locales.
     */
    /** @var list<string> */
    public const TRADES = [
        'plumbing',
        'electrical',
        'hvac',
        'carpentry',
        'painting',
        'masonry',
        'appliances',
        'cleaning',
        'pest_control',
        'locksmith',
        'general',
    ];

    // ── Relationships ──────────────────────────────────

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // ── Scopes ─────────────────────────────────────────

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->whereNotNull('verified_at');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeWithTrade(Builder $query, string $trade): Builder
    {
        return $query->whereJsonContains('trades', $trade);
    }

    // ── Accessors ──────────────────────────────────────

    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * How complete the profile is, as a percentage. Drives the nudge on the
     * provider dashboard — an empty profile in a directory helps nobody.
     */
    public function completeness(): int
    {
        $checks = [
            filled($this->business_name),
            filled($this->bio),
            filled($this->trades),
            filled($this->service_areas),
            filled($this->phone),
            $this->hourly_rate !== null || $this->callout_fee !== null,
            $this->years_experience !== null,
        ];

        return (int) round(count(array_filter($checks)) / count($checks) * 100);
    }
}
