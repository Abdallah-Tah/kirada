<?php

namespace App\Http\Controllers;

use App\Support\Locales;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        $locale = Locales::normalize($locale);

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
