<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BwaWebhookRequest extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'request_id',
        'payload_hash',
        'received_at',
        'expires_at',
    ];

    protected $casts = [
        'received_at' => 'immutable_datetime',
        'expires_at' => 'immutable_datetime',
    ];
}
