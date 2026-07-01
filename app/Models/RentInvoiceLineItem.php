<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentInvoiceLineItem extends Model
{
    protected $fillable = [
        'rent_invoice_id',
        'type',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(RentInvoice::class, 'rent_invoice_id');
    }
}
