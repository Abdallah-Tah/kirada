<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    private const SUPPORTED = ['en', 'fr', 'ar', 'so', 'am'];

    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (!in_array($locale, self::SUPPORTED, true)) {
            $locale = 'en';
        }

        Session::put('locale', $locale);

        $user = $request->user();
        if ($user && $user->preferred_language !== $locale) {
            $user->forceFill(['preferred_language' => $locale])->save();
        }

        return redirect()
            ->back()
            ->withCookie(cookie()->forever('locale', $locale));
    }
}
