<?php

namespace App\Support;

use App\Models\User;

/**
 * The single source of truth for which languages Kirada speaks, and for the
 * language an outbound message should be written in.
 *
 * Outbound mail and WhatsApp are written in the *landlord's* language, not the
 * recipient's and not the browser locale of whoever triggered the send. A
 * landlord account is a business: its paperwork goes out in one language, and a
 * queue worker (which has no session) must reach the same answer as a web
 * request.
 */
class Locales
{
    /** @var array<int, string> */
    public const SUPPORTED = ['en', 'fr', 'ar', 'so', 'am'];

    public const DEFAULT = 'en';

    public static function isSupported(?string $locale): bool
    {
        return $locale !== null && in_array($locale, self::SUPPORTED, true);
    }

    /**
     * Normalise anything into a supported locale.
     */
    public static function normalize(?string $locale): string
    {
        return self::isSupported($locale) ? $locale : self::DEFAULT;
    }

    /**
     * The language to write to this user in.
     */
    public static function forUser(?User $user): string
    {
        return self::normalize($user?->preferred_language);
    }

    /**
     * The language a landlord account communicates in.
     *
     * Team members inherit the account owner's choice so one business does not
     * send French and Somali paperwork from the same office.
     */
    public static function forLandlord(?User $landlord): string
    {
        if (! $landlord) {
            return self::DEFAULT;
        }

        return self::forUser($landlord->landlordAccount() ?? $landlord);
    }
}
