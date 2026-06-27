<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Document;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Message;
use App\Models\Property;
use App\Models\RentInvoice;
use App\Models\RentPayment;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Services\LeaseService;
use App\Services\MessagingService;
use App\Services\RentInvoiceService;
use App\Services\RentPaymentService;
use App\Services\SubscriptionService;
use App\Services\TenantInvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MvpSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $landlord;
    private User $maintenanceUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'CountryCurrencySeeder']);
        $this->artisan('db:seed', ['--class' => 'PlanSeeder']);

        $this->admin = User::factory()->create(['email' => 'admin@test.dj']);
        $this->admin->assignRole('admin');

        $this->landlord = User::factory()->create(['email' => 'landlord@test.dj']);
        $this->landlord->assignRole('landlord');

        $this->maintenanceUser = User::factory()->create(['email' => 'maintenance@test.dj']);
        $this->maintenanceUser->assignRole('maintenance');

        app(SubscriptionService::class)->startTrial($this->landlord);
    }

    public function setupLandlordData()
    {
        Auth::login($this->landlord);

        $property = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Test Building',
            'type' => 'residential',
            'address_line_1' => '123 Test St',
            'city' => 'Djibouti',
            'country' => 'Djibouti',
            'country_id' => 1,
            'currency_id' => 1,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('properties', ['id' => $property->id, 'name' => 'Test Building']);

        $unit = Unit::create([
            'property_id' => $property->id,
            'unit_number' => 'A-101',
            'type' => 'apartment',
            'bedrooms' => 2,
            'bathrooms' => 1,
            'monthly_rent' => 50000,
            'security_deposit' => 100000,
            'status' => 'vacant',
        ]);
        $this->assertDatabaseHas('units', ['id' => $unit->id, 'unit_number' => 'A-101']);

        $tenant = Tenant::create([
            'landlord_id' => $this->landlord->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+253 77 123 456',
        ]);
        $this->assertDatabaseHas('tenants', ['id' => $tenant->id, 'first_name' => 'John']);

        $lease = app(LeaseService::class)->createLease([
            'landlord_id' => $this->landlord->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'start_date' => '2026-06-01',
            'end_date' => '2027-05-31',
            'monthly_rent' => 50000,
            'security_deposit' => 100000,
            'payment_due_day' => 5,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('leases', ['id' => $lease->id, 'status' => 'active']);
        $this->assertEquals('occupied', $unit->fresh()->status);

        Auth::logout();
    }

    public function setupInvoiceData()
    {
        $this->setupLandlordData();

        $lease = Lease::first();
        $invoice = app(RentInvoiceService::class)->createInvoice(
            app(RentInvoiceService::class)->dataFromLease($lease, '2026-07-01')
            + ['landlord_id' => $this->landlord->id, 'status' => 'unpaid']
        );

        $this->assertInstanceOf(RentInvoice::class, $invoice);
        $this->assertEquals(50000, $invoice->amount);
        $this->assertEquals('unpaid', $invoice->status);
        $this->assertStringStartsWith('INV-', $invoice->invoice_number);
    }

    public function test_payment_can_partially_then_fully_pay_invoice()
    {
        $this->setupInvoiceData();

        $invoice = RentInvoice::first();
        $paySvc = app(RentPaymentService::class);

        // Partial payment
        $pay1 = $paySvc->createPayment([
            'landlord_id' => $this->landlord->id,
            'rent_invoice_id' => $invoice->id,
            'lease_id' => $invoice->lease_id,
            'property_id' => $invoice->property_id,
            'unit_id' => $invoice->unit_id,
            'tenant_id' => $invoice->tenant_id,
            'payment_date' => '2026-07-05',
            'amount' => 20000,
            'method' => 'cash',
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'confirmed_by' => $this->landlord->id,
        ]);

        $this->assertEquals('partially_paid', $invoice->fresh()->status);

        // Full payment
        $pay2 = $paySvc->createPayment([
            'landlord_id' => $this->landlord->id,
            'rent_invoice_id' => $invoice->id,
            'lease_id' => $invoice->lease_id,
            'property_id' => $invoice->property_id,
            'unit_id' => $invoice->unit_id,
            'tenant_id' => $invoice->tenant_id,
            'payment_date' => '2026-07-07',
            'amount' => 30000,
            'method' => 'mobile_money',
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'confirmed_by' => $this->landlord->id,
        ]);

        $this->assertEquals('paid', $invoice->fresh()->status);
    }

    public function setupTenantInvitation()
    {
        $this->setupLandlordData();

        $tenant = Tenant::first();
        $svc = app(TenantInvitationService::class);

        $invitation = $svc->createInvitation(
            $this->landlord->id,
            $tenant->id,
            'invited@test.dj',
            null
        );

        $this->assertEquals('pending', $invitation->status);

        $user = $svc->acceptInvitation($invitation, 'Invited User', 'invited@test.dj', 'password123');

        $this->assertTrue($user->hasRole('tenant'));
        $this->assertEquals($user->id, $tenant->fresh()->user_id);
        $this->assertEquals('accepted', $invitation->fresh()->status);
    }

    public function test_tenant_can_create_maintenance_request()
    {
        $this->setupTenantInvitation();

        $tenantUser = User::where('email', 'invited@test.dj')->first();
        Auth::login($tenantUser);

        $property = Property::first();
        $unit = Unit::first();

        $request = app(\App\Services\MaintenanceRequestService::class)->createRequest([
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'title' => 'Broken faucet',
            'description' => 'Kitchen faucet is leaking.',
            'priority' => 'medium',
        ], $tenantUser);

        $this->assertInstanceOf(MaintenanceRequest::class, $request);
        $this->assertEquals('open', $request->status);
        $this->assertEquals($tenantUser->id, $request->reported_by);
        $this->assertEquals($this->landlord->id, $request->landlord_id);

        Auth::logout();
    }

    public function test_landlord_and_tenant_can_message()
    {
        $this->setupTenantInvitation();

        $tenantUser = User::where('email', 'invited@test.dj')->first();
        $tenant = Tenant::first();
        $svc = app(MessagingService::class);

        // Landlord starts conversation
        Auth::login($this->landlord);
        $conv = $svc->startConversation($this->landlord, [
            'landlord_id' => $this->landlord->id,
            'tenant_id' => $tenant->id,
            'subject' => 'Test conversation',
        ]);
        $svc->sendMessage($conv, $this->landlord, 'Hello tenant!');
        Auth::logout();

        // Tenant replies
        Auth::login($tenantUser);
        $svc->sendMessage($conv, $tenantUser, 'Hi landlord!');
        Auth::logout();

        $this->assertEquals(2, $conv->fresh()->messages()->count());
        $this->assertNotNull($conv->fresh()->last_message_at);
    }

    public function test_document_upload_download_uses_authorization()
    {
        Storage::fake('private');

        $this->setupLandlordData();

        $tenant = Tenant::first();

        Auth::login($this->landlord);
        $file = UploadedFile::fake()->create('lease.pdf', 100, 'application/pdf');

        $doc = app(\App\Services\DocumentService::class)->uploadDocument([
            'title' => 'Lease Agreement',
            'type' => 'lease_agreement',
            'tenant_id' => $tenant->id,
            'visibility' => 'tenant_visible',
        ], $file, $this->landlord);

        $this->assertInstanceOf(Document::class, $doc);
        $this->assertTrue(Storage::disk('private')->exists($doc->file_path));

        // Download
        $response = app(\App\Services\DocumentService::class)->downloadDocument($doc);
        $this->assertNotNull($response);

        Auth::logout();
    }

    public function test_subscription_trial_exists_for_landlord()
    {
        $sub = $this->landlord->subscription;
        $this->assertNotNull($sub);
        $this->assertEquals('trialing', $sub->status);
        $this->assertTrue($this->landlord->onTrial());
        $this->assertFalse($this->admin->needsSubscription());
        $this->assertFalse($this->maintenanceUser->needsSubscription());
    }

    public function test_pwa_files_are_accessible()
    {
        // Offline route is served by Laravel
        $response = $this->get('/offline');
        $response->assertStatus(200);

        // Static PWA files exist in public directory
        $this->assertTrue(file_exists(public_path('manifest.json')), 'manifest.json exists');
        $this->assertTrue(file_exists(public_path('service-worker.js')), 'service-worker.js exists');

        // Manifest has correct structure
        $manifest = json_decode(file_get_contents(public_path('manifest.json')), true);
        $this->assertEquals('Kirada', $manifest['short_name']);
        $this->assertEquals('standalone', $manifest['display']);
        $this->assertEquals('/dashboard', $manifest['start_url']);
        $this->assertEquals('/', $manifest['scope']);

        // Icons exist
        $this->assertTrue(file_exists(public_path('icons/icon-192.png')), 'icon-192.png exists');
        $this->assertTrue(file_exists(public_path('icons/icon-512.png')), 'icon-512.png exists');
    }
}