<?php

namespace App\Services;

use App\Models\LandlordNotificationSetting;
use App\Models\RentInvoice;

class NotificationChannelResolver
{
    /**
     * @return array<int, string>
     */
    public function channels(RentInvoice $invoice, bool $reminder = false): array
    {
        $invoice->loadMissing(['lease', 'landlord.notificationSetting']);

        $override = $reminder
            ? $invoice->lease?->reminder_delivery_channels
            : $invoice->lease?->invoice_delivery_channels;

        $setting = $invoice->landlord?->notificationSetting;
        $defaults = $reminder
            ? ($setting?->reminder_channels ?? [LandlordNotificationSetting::CHANNEL_EMAIL])
            : ($setting?->invoice_channels ?? [LandlordNotificationSetting::CHANNEL_EMAIL]);

        $channels = is_array($override) ? $override : $defaults;

        return array_values(array_intersect(
            LandlordNotificationSetting::CHANNELS,
            array_unique(array_filter($channels, 'is_string')),
        )) ?: [LandlordNotificationSetting::CHANNEL_EMAIL];
    }

    public function autoSendEnabled(RentInvoice $invoice): bool
    {
        $invoice->loadMissing(['lease', 'landlord.notificationSetting']);

        if ($invoice->lease?->auto_send_invoice_override !== null) {
            return (bool) $invoice->lease->auto_send_invoice_override;
        }

        return $invoice->landlord?->notificationSetting?->auto_send_invoices ?? true;
    }

    public function attachPdfToEmail(RentInvoice $invoice): bool
    {
        $invoice->loadMissing('landlord.notificationSetting');

        return $invoice->landlord?->notificationSetting?->attach_pdf_to_email ?? true;
    }

    public function emailRecipient(RentInvoice $invoice): ?string
    {
        $invoice->loadMissing('tenant.user');

        return $invoice->tenant?->email ?: $invoice->tenant?->user?->email;
    }

    public function whatsAppRecipient(RentInvoice $invoice): ?string
    {
        $invoice->loadMissing('tenant');

        if (! $invoice->tenant?->hasWhatsAppConsent() || blank($invoice->tenant->phone)) {
            return null;
        }

        return $invoice->tenant->phone;
    }
}
