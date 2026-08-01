<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function complete(Request $request): RedirectResponse
    {
        $request->user()->completeOnboarding();

        return redirect()
            ->back()
            ->with('status', __('Onboarding guide completed.'));
    }
}
