<?php

use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MaintenanceAttachmentController;
use App\Http\Controllers\MaintenancePdfController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\SubscriptionCheckoutController;
use App\Http\Controllers\SubscriptionPortalController;
use App\Livewire\Audit\Index as AuditIndex;
use App\Livewire\Contracts\Create as ContractCreate;
use App\Livewire\Contracts\Index as ContractIndex;
use App\Livewire\Contracts\Show as ContractShow;
use App\Livewire\Contracts\Sign as ContractSign;
use App\Livewire\Documents\Create as DocumentCreate;
use App\Livewire\Documents\Index as DocumentIndex;
use App\Livewire\LandlordTeam\Accept as LandlordTeamAccept;
use App\Livewire\LandlordTeam\Index as LandlordTeamIndex;
use App\Livewire\Leases\Create as LeaseCreate;
use App\Livewire\Leases\Edit as LeaseEdit;
use App\Livewire\Leases\Index as LeaseIndex;
use App\Livewire\Leases\Show as LeaseShow;
use App\Livewire\MaintenanceProfiles\Directory as MaintenanceDirectory;
use App\Livewire\MaintenanceProfiles\Edit as MaintenanceProfileEdit;
use App\Livewire\MaintenanceProfiles\Inbox as MaintenanceInbox;
use App\Livewire\MaintenanceProfiles\Network as MaintenanceNetwork;
use App\Livewire\MaintenanceProfiles\Show as MaintenanceProfileShow;
use App\Livewire\MaintenanceRequests\Create as MaintenanceRequestCreate;
use App\Livewire\MaintenanceRequests\Index as MaintenanceRequestIndex;
use App\Livewire\MaintenanceRequests\Show as MaintenanceRequestShow;
use App\Livewire\Messages\Index as MessageIndex;
use App\Livewire\Messages\Show as MessageShow;
use App\Livewire\Properties\Create as PropertyCreate;
use App\Livewire\Properties\Edit as PropertyEdit;
use App\Livewire\Properties\Index as PropertyIndex;
use App\Livewire\RentInvoices\Create as RentInvoiceCreate;
use App\Livewire\RentInvoices\DeliveryHistory as RentInvoiceDeliveryHistory;
use App\Livewire\RentInvoices\Edit as RentInvoiceEdit;
use App\Livewire\RentInvoices\Index as RentInvoiceIndex;
use App\Livewire\RentPayments\Create as RentPaymentCreate;
use App\Livewire\RentPayments\Edit as RentPaymentEdit;
use App\Livewire\RentPayments\Index as RentPaymentIndex;
use App\Livewire\RentPayments\Submit as RentPaymentSubmit;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\Search\Index as GlobalSearchIndex;
use App\Livewire\Subscriptions\Status as SubscriptionStatus;
use App\Livewire\TenantInvitations\Accept as TenantInvitationAccept;
use App\Livewire\TenantInvitations\Index as TenantInvitationIndex;
use App\Livewire\Tenants\Create as TenantCreate;
use App\Livewire\Tenants\Edit as TenantEdit;
use App\Livewire\Tenants\Index as TenantIndex;
use App\Livewire\Units\Create as UnitCreate;
use App\Livewire\Units\Edit as UnitEdit;
use App\Livewire\Units\Index as UnitIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Legal pages
Route::view('/terms-of-service', 'pages.legal.terms-of-service')->name('terms-of-service');
Route::view('/privacy-policy', 'pages.legal.privacy-policy')->name('privacy-policy');
Route::view('/how-it-works', 'pages.legal.how-it-works')->name('how-it-works');

// Language switcher (works for both guests and authenticated users)
Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

// Offline fallback page (PWA)
Route::view('/offline', 'offline')->name('offline');

// Role-based dashboard dispatcher
Route::middleware(['kirada-auth'])
    ->get('/dashboard', DashboardController::class)
    ->name('dashboard');

Route::middleware(['kirada-auth', 'throttle:kirada-authenticated-actions'])
    ->post('/onboarding/complete', [OnboardingController::class, 'complete'])
    ->name('onboarding.complete');

// Role-specific dashboards
Route::middleware(['kirada-auth'])->group(function () {
    Route::get('/search', GlobalSearchIndex::class)->name('search.index');

    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
        ->middleware('role:admin')
        ->name('admin.dashboard');

    Route::get('/landlord/dashboard', [DashboardController::class, 'landlord'])
        ->middleware('role:landlord|landlord-admin|property-manager|accountant|viewer')
        ->name('landlord.dashboard');

    Route::get('/tenant/dashboard', [DashboardController::class, 'tenant'])
        ->middleware('role:tenant')
        ->name('tenant.dashboard');

    Route::get('/maintenance/dashboard', [DashboardController::class, 'maintenance'])
        ->middleware('role:maintenance')
        ->name('maintenance.dashboard');
});

// Properties — admin + landlord only
Route::middleware(['kirada-auth', 'role:admin|landlord|landlord-admin|property-manager|accountant|viewer', 'subscription'])->group(function () {
    Route::get('/properties', PropertyIndex::class)->name('properties.index');
    Route::get('/properties/create', PropertyCreate::class)->name('properties.create');
    Route::get('/properties/{property}/edit', PropertyEdit::class)->name('properties.edit');
});

// Units — admin + landlord only
Route::middleware(['kirada-auth', 'role:admin|landlord|landlord-admin|property-manager|accountant|viewer', 'subscription'])->group(function () {
    Route::get('/units', UnitIndex::class)->name('units.index');
    Route::get('/units/create', UnitCreate::class)->name('units.create');
    Route::get('/units/{unit}/edit', UnitEdit::class)->name('units.edit');
});

// Tenants — admin + landlord only
Route::middleware(['kirada-auth', 'role:admin|landlord|landlord-admin|property-manager|accountant|viewer', 'subscription'])->group(function () {
    Route::get('/tenants', TenantIndex::class)->name('tenants.index');
    Route::get('/tenants/create', TenantCreate::class)->name('tenants.create');
    Route::get('/tenants/{tenant}/edit', TenantEdit::class)->name('tenants.edit');
});

// Leases — admin + landlord only
Route::middleware(['kirada-auth', 'role:admin|landlord|landlord-admin|property-manager|accountant|viewer', 'subscription'])->group(function () {
    Route::get('/leases', LeaseIndex::class)->name('leases.index');
    Route::get('/leases/create', LeaseCreate::class)->name('leases.create');
    Route::get('/leases/{lease}', LeaseShow::class)->name('leases.show');
    Route::get('/leases/{lease}/edit', LeaseEdit::class)->name('leases.edit');
});

// Contracts — admin + landlord only (generation & e-signature management)
Route::middleware(['kirada-auth', 'role:admin|landlord|landlord-admin|property-manager|accountant|viewer', 'subscription'])->group(function () {
    Route::get('/contracts', ContractIndex::class)->name('contracts.index');
    Route::get('/contracts/create', ContractCreate::class)->name('contracts.create');
    Route::get('/contracts/{contract}', ContractShow::class)->name('contracts.show');
    Route::get('/contracts/{contract}/print', [ContractController::class, 'print'])->name('contracts.print');
    Route::get('/contracts/{contract}/download', [ContractController::class, 'download'])->name('contracts.download');
});

// Public contract signing (token-based, no auth — like a DocuSign signing link)
Route::get('/sign/{token}', ContractSign::class)->middleware('throttle:kirada-public-links')->name('contracts.sign');

// Payment operator webhooks (shared-secret verified per gateway, CSRF-exempt)
Route::post('/webhooks/payments/{gateway}', PaymentWebhookController::class)->middleware('throttle:kirada-webhooks')->name('webhooks.payments');

// Subscription checkout initiation — authenticated landlords only
Route::middleware(['kirada-auth', 'role:landlord'])->group(function () {
    Route::post('/subscription/checkout/{planSlug}', [SubscriptionCheckoutController::class, 'initiate'])
        ->name('subscription.checkout');
    Route::get('/subscription/billing-portal', SubscriptionPortalController::class)
        ->name('subscription.portal');
});

// Rent Invoices — list is shared with tenants ("My Rent", scoped in the
// component); create/edit stay admin + landlord only.
Route::middleware(['kirada-auth', 'role:admin|landlord|landlord-admin|property-manager|accountant|viewer|tenant'])->group(function () {
    Route::get('/rent-invoices', RentInvoiceIndex::class)->name('rent-invoices.index');
    Route::get('/rent-invoices/{rentInvoice}/delivery', RentInvoiceDeliveryHistory::class)
        ->name('rent-invoices.delivery');
    Route::get('/rent-invoices/{rentInvoice}/pdf', [ReceiptController::class, 'invoicePdf'])->name('rent-invoices.pdf');
    Route::get('/rent-payments/{rentPayment}/receipt', [ReceiptController::class, 'paymentReceipt'])->name('rent-payments.receipt');
});

// Tenant "I paid" submission — creates a pending payment for landlord confirmation
Route::middleware(['kirada-auth', 'role:tenant'])->group(function () {
    Route::get('/rent-payments/submit/{rentInvoice}', RentPaymentSubmit::class)->name('rent-payments.submit');
});

Route::middleware(['kirada-auth', 'role:admin|landlord|landlord-admin|property-manager|accountant', 'subscription'])->group(function () {
    Route::get('/rent-invoices/create', RentInvoiceCreate::class)->name('rent-invoices.create');
    Route::get('/rent-invoices/{rentInvoice}/edit', RentInvoiceEdit::class)->name('rent-invoices.edit');
});

// Rent Payments — admin + landlord only
Route::middleware(['kirada-auth', 'role:admin|landlord|landlord-admin|property-manager|accountant', 'subscription'])->group(function () {
    Route::get('/rent-payments', RentPaymentIndex::class)->name('rent-payments.index');
    Route::get('/rent-payments/create', RentPaymentCreate::class)->name('rent-payments.create');
    Route::get('/rent-payments/{rentPayment}/edit', RentPaymentEdit::class)->name('rent-payments.edit');
});

// Tenant Invitations — admin + landlord management
Route::middleware(['kirada-auth', 'role:admin|landlord|landlord-admin|property-manager', 'subscription'])->group(function () {
    Route::get('/tenant-invitations', TenantInvitationIndex::class)->name('tenant-invitations.index');
});

// Public invitation acceptance (no auth required)
Route::get('/tenant-invitations/{token}', TenantInvitationAccept::class)->middleware('throttle:kirada-public-links')->name('tenant-invitations.accept');

// Maintenance Requests — admin, landlord, tenant, maintenance
Route::middleware(['kirada-auth', 'role:admin|landlord|landlord-admin|property-manager|viewer|tenant|maintenance', 'subscription'])->group(function () {
    Route::get('/maintenance-requests', MaintenanceRequestIndex::class)->name('maintenance-requests.index');
    Route::get('/maintenance-requests/create', MaintenanceRequestCreate::class)->name('maintenance-requests.create');
    Route::get('/maintenance-requests/{maintenanceRequest}', MaintenanceRequestShow::class)->name('maintenance-requests.show');
    Route::get('/maintenance-quotes/{maintenanceQuote}/pdf', MaintenancePdfController::class)
        ->name('maintenance-quotes.pdf');
    Route::get('/maintenance-attachments/{attachment}', [MaintenanceAttachmentController::class, 'show'])
        ->name('maintenance-attachments.show');
});

// Maintenance provider directory — admin + landlord hire from here.
// Deliberately outside the 'subscription' gate: building a maintenance team is
// part of evaluating Kirada, and a lapsed landlord still needs their team list.
Route::middleware(['kirada-auth', 'role:admin|landlord|landlord-admin|property-manager'])->group(function () {
    Route::get('/maintenance-directory', MaintenanceDirectory::class)->name('maintenance-directory.index');
    Route::get('/maintenance-directory/{profile}', MaintenanceProfileShow::class)->name('maintenance-directory.show');
    Route::get('/maintenance-network', MaintenanceNetwork::class)->name('maintenance-network.index');
});

// Maintenance provider's own profile + work invitations
Route::middleware(['kirada-auth', 'role:maintenance'])->group(function () {
    Route::get('/maintenance-profile', MaintenanceProfileEdit::class)->name('maintenance-profile.edit');
    Route::get('/maintenance-network/invitations', MaintenanceInbox::class)->name('maintenance-network.inbox');
});

// Messages — admin, landlord, tenant, maintenance
Route::middleware(['kirada-auth', 'role:admin|landlord|landlord-admin|property-manager|viewer|tenant|maintenance'])->group(function () {
    Route::get('/messages', MessageIndex::class)->name('messages.index');
    Route::get('/messages/{conversation}', MessageShow::class)->name('messages.show');
});

// Documents — admin, landlord, tenant (no maintenance)
Route::middleware(['kirada-auth', 'role:admin|landlord|landlord-admin|property-manager|accountant|viewer|tenant', 'subscription'])->group(function () {
    Route::get('/documents', DocumentIndex::class)->name('documents.index');
    Route::get('/documents/create', DocumentCreate::class)->name('documents.create');
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
});

// Subscription status — landlord only
Route::middleware(['kirada-auth', 'role:landlord'])->group(function () {
    Route::get('/subscription', SubscriptionStatus::class)->name('subscription.status');
});

// Reports — admin + landlord only
Route::middleware(['kirada-auth', 'role:admin|landlord|landlord-admin|accountant|viewer', 'subscription'])->group(function () {
    Route::get('/reports', ReportsIndex::class)->name('reports.index');
});

Route::middleware([
    'kirada-auth',
    'role:admin|landlord|landlord-admin',
    'permission:audit.view',
])->group(function () {
    Route::get('/audit', AuditIndex::class)->name('audit.index');
});

Route::middleware(['kirada-auth', 'role:landlord|landlord-admin|property-manager|accountant|viewer'])->group(function () {
    Route::get('/property-team', LandlordTeamIndex::class)
        ->name('property-team.index');
});

Route::get('/team-invitations/{token}', LandlordTeamAccept::class)
    ->middleware('throttle:kirada-public-links')
    ->name('team-invitations.accept');

require __DIR__.'/settings.php';
