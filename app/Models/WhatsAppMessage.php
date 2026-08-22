<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppMessage extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'landlord_id',
        'tenant_id',
        'provider_message_id',
        'from_number',
        'profile_name',
        'message_type',
        'body',
        'media_id',
        'payload',
        'received_at',
        'read_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'received_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    /** The tenant the sending number was matched to, if any. */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Inbound that matched no tenant. Nobody but an admin can see these, so
     * they need to be findable as a group rather than one row at a time.
     */
    public function scopeUnmatched(Builder $query): Builder
    {
        return $query->whereNull('landlord_id');
    }
}
