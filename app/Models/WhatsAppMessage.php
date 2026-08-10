<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppMessage extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'landlord_id',
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
}
