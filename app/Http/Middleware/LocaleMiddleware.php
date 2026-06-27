<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    private const SUPPORTED_LOCALES = ['en', 'fr', 'ar', 'so', 'am'];
    private const DEFAULT_LOCALE = 'en';
    private const SESSION_KEY = 'locale';

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        App::setLocale($locale);

        // Store in session
        Session::put(self::SESSION_KEY, $locale);

        // For logged-in users, persist to preferred_language
        $user = $request->user();
        if ($user && $user->preferred_language !== $locale) {
            $user->forceFill(['preferred_language' => $locale])->save();
        }

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        // 1. Explicit query param (?lang=fr) — from language switcher
        if ($request->has('lang') && $this->isSupported($request->get('lang'))) {
            return $request->get('lang');
        }

        // 2. Session
        $sessionLocale = Session::get(self::SESSION_KEY);
        if ($this->isSupported($sessionLocale)) {
            return $sessionLocale;
        }

        // 3. User's preferred_language
        $user = $request->user();
        if ($user && $this->isSupported($user->preferred_language)) {
            return $user->preferred_language;
        }

        // 4. Browser Accept-Language header
        $browserLocale = $this->detectBrowserLocale($request);
        if ($this->isSupported($browserLocale)) {
            return $browserLocale;
        }

        // 5. Fallback
        return self::DEFAULT_LOCALE;
    }

    private function isSupported(?string $locale): bool
    {
        return $locale !== null && in_array($locale, self::SUPPORTED_LOCALES, true);
    }

    private function detectBrowserLocale(Request $request): ?string
    {
        $header = $request->header('Accept-Language');
        if (!$header) {
            return null;
        }

        // Parse "en-US,en;q=0.9,fr;q=0.8" → ["en", "fr"]
        $languages = [];
        $parts = explode(',', $header);
        foreach ($parts as $part) {
            $code = trim(explode(';', $part)[0]);
            // "en-US" → "en"
            $code = strtolower(explode('-', $code)[0]);
            $languages[] = $code;
        }

        foreach ($languages as $code) {
            if ($this->isSupported($code)) {
                return $code;
            }
        }

        return null;
    }
}