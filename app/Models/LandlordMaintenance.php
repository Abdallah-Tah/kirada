<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

/**
 * The landlord ↔ maintenance-provider link.
 *
 * Exists as a pivot model so the timestamps are cast: views format
 * approved_at/created_at, and an uncast pivot hands back raw strings.
 *
 * @property string $status One of pending|approved|rejected
 * @property string $requested_by
 * @property Carbon|null $approved_at
 * @property Carbon|null $rejected_at
 * @property string|null $message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class LandlordMaintenance extends Pivot
{
    protected $table = 'landlord_maintenance';

    public $incrementing = true;

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];
}
