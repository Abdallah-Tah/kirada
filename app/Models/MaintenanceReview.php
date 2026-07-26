<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceReview extends Model
{
    protected $fillable = [
        'maintenance_request_id',
        'landlord_id',
        'maintenance_user_id',
        'rating',
        'quality_rating',
        'communication_rating',
        'professionalism_rating',
        'title',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
        'quality_rating' => 'integer',
        'communication_rating' => 'integer',
        'professionalism_rating' => 'integer',
    ];

    public function maintenanceRequest(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function maintenanceUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'maintenance_user_id');
    }
}
