<?php

use App\Http\Controllers\DashboardController;
use App\Livewire\Properties\Create as PropertyCreate;
use App\Livewire\Properties\Edit as PropertyEdit;
use App\Livewire\Properties\Index as PropertyIndex;
use App\Livewire\Leases\Create as LeaseCreate;
use App\Livewire\Leases\Edit as LeaseEdit;
use App\Livewire\Leases\Index as LeaseIndex;
use App\Livewire\Tenants\Create as TenantCreate;
use App\Livewire\Tenants\Edit as TenantEdit;
use App\Livewire\Tenants\Index as TenantIndex;
use App\Livewire\Units\Create as UnitCreate;
use App\Livewire\Units\Edit as UnitEdit;
use App\Livewire\Units\Index as UnitIndex;
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

// Properties — admin + landlord only
Route::middleware(['auth', 'verified', 'role:admin|landlord'])->group(function () {
    Route::get('/properties', PropertyIndex::class)->name('properties.index');
    Route::get('/properties/create', PropertyCreate::class)->name('properties.create');
    Route::get('/properties/{property}/edit', PropertyEdit::class)->name('properties.edit');
});

// Units — admin + landlord only
Route::middleware(['auth', 'verified', 'role:admin|landlord'])->group(function () {
    Route::get('/units', UnitIndex::class)->name('units.index');
    Route::get('/units/create', UnitCreate::class)->name('units.create');
    Route::get('/units/{unit}/edit', UnitEdit::class)->name('units.edit');
});

// Tenants — admin + landlord only
Route::middleware(['auth', 'verified', 'role:admin|landlord'])->group(function () {
    Route::get('/tenants', TenantIndex::class)->name('tenants.index');
    Route::get('/tenants/create', TenantCreate::class)->name('tenants.create');
    Route::get('/tenants/{tenant}/edit', TenantEdit::class)->name('tenants.edit');
});

// Leases — admin + landlord only
Route::middleware(['auth', 'verified', 'role:admin|landlord'])->group(function () {
    Route::get('/leases', LeaseIndex::class)->name('leases.index');
    Route::get('/leases/create', LeaseCreate::class)->name('leases.create');
    Route::get('/leases/{lease}/edit', LeaseEdit::class)->name('leases.edit');
});

require __DIR__.'/settings.php';