<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandlordTeamMembership extends Model
{
    public const ROLES = [
        'landlord-admin',
        'property-manager',
        'accountant',
        'viewer',
    ];

    public const CHANNEL_EMAIL = 'email';

    public const CHANNEL_WHATSAPP = 'whatsapp';

    /** @var array<int, string> */
    public const CHANNELS = [self::CHANNEL_EMAIL, self::CHANNEL_WHATSAPP];

    protected $fillable = [
        'landlord_id',
        'user_id',
        'invited_by',
        'email',
        'phone',
        'delivery_channels',
        'role',
        'token_hash',
        'status',
        'expires_at',
        'accepted_at',
        'whatsapp_message_id',
        'whatsapp_request_id',
        'whatsapp_status',
        'whatsapp_sent_at',
        'whatsapp_delivered_at',
        'whatsapp_read_at',
        'whatsapp_failed_at',
        'whatsapp_error',
    ];

    protected $hidden = ['token_hash'];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'delivery_channels' => 'array',
        'whatsapp_sent_at' => 'datetime',
        'whatsapp_delivered_at' => 'datetime',
        'whatsapp_read_at' => 'datetime',
        'whatsapp_failed_at' => 'datetime',
    ];

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')->whereNotNull('accepted_at');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && $this->expires_at->isFuture();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->accepted_at !== null;
    }

    public function usesChannel(string $channel): bool
    {
        return in_array($channel, $this->delivery_channels ?? [self::CHANNEL_EMAIL], true);
    }
}
