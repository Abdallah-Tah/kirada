<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Role-based dashboard dispatcher
Route::middleware(['auth', 'verified'])
    ->get('/dashboard', DashboardController::class)
    ->name('dashboard');

// Role-specific dashboards
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/admin/dashboard', 'dashboards.admin')
        ->middleware('role:admin')
        ->name('admin.dashboard');

    Route::view('/landlord/dashboard', 'dashboards.landlord')
        ->middleware('role:landlord')
        ->name('landlord.dashboard');

    Route::view('/tenant/dashboard', 'dashboards.tenant')
        ->middleware('role:tenant')
        ->name('tenant.dashboard');

    Route::view('/maintenance/dashboard', 'dashboards.maintenance')
        ->middleware('role:maintenance')
        ->name('maintenance.dashboard');
});

require __DIR__.'/settings.php';