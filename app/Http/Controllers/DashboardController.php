<?php

namespace App\Http\Controllers;

use App\Services\DashboardMetricsService;
use App\Services\DashboardRedirectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardRedirectService $redirectService,
        private DashboardMetricsService $metricsService,
    ) {}

    /**
     * Main dashboard dispatcher — redirects to the role-specific dashboard.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $route = $this->redirectService->redirectFor($request->user());

        return redirect()->route($route);
    }

    public function admin(): View
    {
        return view('dashboards.admin', $this->metricsService->getAdminMetrics());
    }

    public function landlord(): View
    {
        return view('dashboards.landlord', $this->metricsService->getLandlordMetrics(auth()->user()));
    }

    public function tenant(): View
    {
        return view('dashboards.tenant', $this->metricsService->getTenantMetrics(auth()->user()));
    }

    public function maintenance(): View
    {
        return view('dashboards.maintenance', $this->metricsService->getMaintenanceMetrics(auth()->user()));
    }
}