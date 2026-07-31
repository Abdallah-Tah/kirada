<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandlordNotificationSetting extends Model
{
    public const CHANNEL_EMAIL = 'email';

    public const CHANNEL_WHATSAPP = 'whatsapp';

    public const CHANNELS = [
        self::CHANNEL_EMAIL,
        self::CHANNEL_WHATSAPP,
    ];

    protected $fillable = [
        'landlord_id',
        'invoice_channels',
        'reminder_channels',
        'auto_send_invoices',
        'attach_pdf_to_email',
    ];

    protected $casts = [
        'invoice_channels' => 'array',
        'reminder_channels' => 'array',
        'auto_send_invoices' => 'boolean',
        'attach_pdf_to_email' => 'boolean',
    ];

    protected $attributes = [
        'invoice_channels' => '["email"]',
        'reminder_channels' => '["email"]',
        'auto_send_invoices' => true,
        'attach_pdf_to_email' => true,
    ];

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }
}
