<?php

use App\Http\Controllers\DashboardController;
use App\Livewire\Subscriptions\Status as SubscriptionStatus;
use App\Http\Controllers\DocumentController;
use App\Livewire\Documents\Create as DocumentCreate;
use App\Livewire\Documents\Index as DocumentIndex;
use App\Livewire\Properties\Create as PropertyCreate;
use App\Livewire\Properties\Edit as PropertyEdit;
use App\Livewire\Properties\Index as PropertyIndex;
use App\Livewire\RentPayments\Create as RentPaymentCreate;
use App\Livewire\RentPayments\Edit as RentPaymentEdit;
use App\Livewire\RentPayments\Index as RentPaymentIndex;
use App\Livewire\TenantInvitations\Accept as TenantInvitationAccept;
use App\Livewire\TenantInvitations\Index as TenantInvitationIndex;
use App\Livewire\MaintenanceRequests\Create as MaintenanceRequestCreate;
use App\Livewire\MaintenanceRequests\Index as MaintenanceRequestIndex;
use App\Livewire\MaintenanceRequests\Show as MaintenanceRequestShow;
use App\Livewire\Messages\Index as MessageIndex;
use App\Livewire\Messages\Show as MessageShow;
use App\Livewire\RentInvoices\Create as RentInvoiceCreate;
use App\Livewire\RentInvoices\Edit as RentInvoiceEdit;
use App\Livewire\RentInvoices\Index as RentInvoiceIndex;
use App\Livewire\Leases\Create as LeaseCreate;
use App\Livewire\Leases\Edit as LeaseEdit;
use App\Livewire\Leases\Index as LeaseIndex;
use App\Livewire\Tenants\Create as TenantCreate;
use App\Livewire\Tenants\Edit as TenantEdit;
use App\Livewire\Tenants\Index as TenantIndex;
use App\Livewire\Units\Create as UnitCreate;
use App\Livewire\Units\Edit as UnitEdit;
use App\Livewire\Units\Index as UnitIndex;
use App\Livewire\AiAssistant\Index as AiAssistantIndex;
use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Language switcher (works for both guests and authenticated users)
Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

// Offline fallback page (PWA)
Route::view('/offline', 'offline')->name('offline');

// Role-based dashboard dispatcher
Route::middleware(['auth', 'verified'])
    ->get('/dashboard', DashboardController::class)
    ->name('dashboard');

// Role-specific dashboards
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
        ->middleware('role:admin')
        ->name('admin.dashboard');

    Route::get('/landlord/dashboard', [DashboardController::class, 'landlord'])
        ->middleware('role:landlord')
        ->name('landlord.dashboard');

    Route::get('/tenant/dashboard', [DashboardController::class, 'tenant'])
        ->middleware('role:tenant')
        ->name('tenant.dashboard');

    Route::get('/maintenance/dashboard', [DashboardController::class, 'maintenance'])
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

// Rent Invoices — admin + landlord only
Route::middleware(['auth', 'verified', 'role:admin|landlord'])->group(function () {
    Route::get('/rent-invoices', RentInvoiceIndex::class)->name('rent-invoices.index');
    Route::get('/rent-invoices/create', RentInvoiceCreate::class)->name('rent-invoices.create');
    Route::get('/rent-invoices/{rentInvoice}/edit', RentInvoiceEdit::class)->name('rent-invoices.edit');
});

// Rent Payments — admin + landlord only
Route::middleware(['auth', 'verified', 'role:admin|landlord'])->group(function () {
    Route::get('/rent-payments', RentPaymentIndex::class)->name('rent-payments.index');
    Route::get('/rent-payments/create', RentPaymentCreate::class)->name('rent-payments.create');
    Route::get('/rent-payments/{rentPayment}/edit', RentPaymentEdit::class)->name('rent-payments.edit');
});

// Tenant Invitations — admin + landlord management
Route::middleware(['auth', 'verified', 'role:admin|landlord'])->group(function () {
    Route::get('/tenant-invitations', TenantInvitationIndex::class)->name('tenant-invitations.index');
});

// Public invitation acceptance (no auth required)
Route::get('/tenant-invitations/{token}', TenantInvitationAccept::class)->name('tenant-invitations.accept');

// Maintenance Requests — admin, landlord, tenant, maintenance
Route::middleware(['auth', 'verified', 'role:admin|landlord|tenant|maintenance'])->group(function () {
    Route::get('/maintenance-requests', MaintenanceRequestIndex::class)->name('maintenance-requests.index');
    Route::get('/maintenance-requests/create', MaintenanceRequestCreate::class)->name('maintenance-requests.create');
    Route::get('/maintenance-requests/{maintenanceRequest}', MaintenanceRequestShow::class)->name('maintenance-requests.show');
});

// Messages — admin, landlord, tenant, maintenance
Route::middleware(['auth', 'verified', 'role:admin|landlord|tenant|maintenance'])->group(function () {
    Route::get('/messages', MessageIndex::class)->name('messages.index');
    Route::get('/messages/{conversation}', MessageShow::class)->name('messages.show');
});

// Documents — admin, landlord, tenant (no maintenance)
Route::middleware(['auth', 'verified', 'role:admin|landlord|tenant'])->group(function () {
    Route::get('/documents', DocumentIndex::class)->name('documents.index');
    Route::get('/documents/create', DocumentCreate::class)->name('documents.create');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
});

// Subscription status — landlord only
Route::middleware(['auth', 'verified', 'role:landlord'])->group(function () {
    Route::get('/subscription', SubscriptionStatus::class)->name('subscription.status');
});

// AI Assistant — all roles (read-only, scoped by role)
Route::middleware(['auth', 'verified', 'role:admin|landlord|tenant|maintenance'])->group(function () {
    Route::get('/ai-assistant', AiAssistantIndex::class)->name('ai-assistant.index');
});

require __DIR__.'/settings.php';