<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceQuoteItem extends Model
{
    protected $fillable = [
        'maintenance_quote_id',
        'description',
        'quantity',
        'unit_price',
        'amount',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(MaintenanceQuote::class, 'maintenance_quote_id');
    }
}
