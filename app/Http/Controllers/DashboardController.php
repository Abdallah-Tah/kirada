<?php

namespace App\Http\Controllers;

use App\Services\DashboardRedirectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardRedirectService $redirectService,
    ) {}

    /**
     * Main dashboard dispatcher — redirects to the role-specific dashboard.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $route = $this->redirectService->redirectFor($request->user());

        return redirect()->route($route);
    }
}