<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BwaEvent extends Model
{
    public const STATUS_QUEUED = 'queued';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_PROCESSED = 'processed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'event_id',
        'type',
        'status',
        'raw_body',
        'payload_hash',
        'occurred_at',
        'received_at',
        'processed_at',
        'error_message',
    ];

    protected $casts = [
        'raw_body' => 'encrypted',
        'occurred_at' => 'immutable_datetime',
        'received_at' => 'immutable_datetime',
        'processed_at' => 'immutable_datetime',
    ];
}
