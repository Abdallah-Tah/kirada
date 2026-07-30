<?php

namespace Tests\Feature;

use App\Livewire\MaintenanceProfiles\Directory;
use App\Livewire\MaintenanceProfiles\Edit;
use App\Livewire\MaintenanceProfiles\Inbox;
use App\Models\Currency;
use App\Models\MaintenanceProfile;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\MaintenanceConnectionRequested;
use App\Services\MaintenanceProfileService;
use App\Services\MaintenanceRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class MaintenanceDirectoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    public function test_a_visitor_can_register_as_a_maintenance_provider(): void
    {
        $this->skipUnlessFortifyHas('registration');

        $response = $this->post('/register', [
            'account_type' => 'maintenance',
            'name' => 'Ali Fix',
            'email' => 'ali@example.com',
            'password' => 'Password123!secure',
            'password_confirmation' => 'Password123!secure',
            'terms_accepted' => 'on',
            'privacy_accepted' => 'on',
        ]);

        $response->assertRedirect();

        $user = User::where('email', 'ali@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('maintenance'));
        $this->assertFalse($user->hasRole('landlord'));

        // Providers are paid by landlords, not by a Kirada plan — a trial would
        // put them behind a paywall they can never clear.
        $this->assertNull($user->kiradaSubscription);
    }

    public function test_registration_rejects_a_role_that_is_not_self_service(): void
    {
        $this->skipUnlessFortifyHas('registration');

        $response = $this->post('/register', [
            'account_type' => 'admin',
            'name' => 'Sneaky',
            'email' => 'sneaky@example.com',
            'password' => 'Password123!secure',
            'password_confirmation' => 'Password123!secure',
            'terms_accepted' => 'on',
            'privacy_accepted' => 'on',
        ]);

        $response->assertSessionHasErrors('account_type');
        $this->assertDatabaseMissing('users', ['email' => 'sneaky@example.com']);
    }

    public function test_registration_leaves_no_orphan_user_when_roles_are_missing(): void
    {
        $this->skipUnlessFortifyHas('registration');

        // Reproduces the production 500: an unseeded roles table made assignRole()
        // throw after the user row was already committed, leaving an orphan that
        // blocked the visitor from ever retrying with the same email.
        \DB::table('model_has_roles')->delete();
        \DB::table('roles')->delete();

        $this->post('/register', [
            'name' => 'Orphan Test',
            'email' => 'orphan@example.com',
            'password' => 'Password123!secure',
            'password_confirmation' => 'Password123!secure',
            'terms_accepted' => 'on',
            'privacy_accepted' => 'on',
        ]);

        $this->assertDatabaseMissing('users', ['email' => 'orphan@example.com']);
    }

    public function test_provider_can_publish_a_profile(): void
    {
        $provider = $this->provider();

        Livewire::actingAs($provider)
            ->test(Edit::class)
            ->set('business_name', 'Ali Plumbing')
            ->set('trades', ['plumbing', 'general'])
            ->set('service_areas', ['Balbala'])
            ->set('phone', '+25377000123')
            ->set('is_published', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('maintenance_profiles', [
            'user_id' => $provider->id,
            'business_name' => 'Ali Plumbing',
            'is_published' => true,
        ]);
    }

    public function test_profile_requires_at_least_one_trade_and_area(): void
    {
        Livewire::actingAs($this->provider())
            ->test(Edit::class)
            ->set('business_name', 'No Trades')
            ->set('trades', [])
            ->set('service_areas', [])
            ->call('save')
            ->assertHasErrors(['trades', 'service_areas']);
    }

    public function test_directory_shows_only_published_profiles(): void
    {
        $published = $this->providerWithProfile('Published Pro', published: true);
        $draft = $this->providerWithProfile('Draft Pro', published: false);

        Livewire::actingAs($this->landlord())
            ->test(Directory::class)
            ->assertSee('Published Pro')
            ->assertDontSee('Draft Pro');

        $this->assertTrue($published->maintenanceProfile->is_published);
        $this->assertFalse($draft->maintenanceProfile->is_published);
    }

    public function test_directory_filters_by_trade(): void
    {
        $this->providerWithProfile('Plumber Pro', published: true, trades: ['plumbing']);
        $this->providerWithProfile('Sparky Pro', published: true, trades: ['electrical']);

        Livewire::actingAs($this->landlord())
            ->test(Directory::class)
            ->set('trade', 'electrical')
            ->assertSee('Sparky Pro')
            ->assertDontSee('Plumber Pro');
    }

    public function test_maintenance_users_cannot_browse_the_directory(): void
    {
        $this->actingAs($this->provider())
            ->get(route('maintenance-directory.index'))
            ->assertForbidden();
    }

    public function test_landlords_cannot_reach_the_provider_profile_editor(): void
    {
        $this->actingAs($this->landlord())
            ->get(route('maintenance-profile.edit'))
            ->assertForbidden();
    }

    public function test_connection_request_only_makes_a_worker_assignable_after_they_accept(): void
    {
        Notification::fake();

        $landlord = $this->landlord();
        $provider = $this->providerWithProfile('Ready Pro', published: true);
        $service = app(MaintenanceProfileService::class);

        // Landlord invites.
        $service->requestConnection($landlord, $provider, 'Two buildings in Balbala.');

        Notification::assertSentTo($provider, MaintenanceConnectionRequested::class);

        $this->assertDatabaseHas('landlord_maintenance', [
            'landlord_id' => $landlord->id,
            'maintenance_user_id' => $provider->id,
            'status' => 'pending',
            'approved_at' => null,
        ]);

        // Still pending, so still not assignable.
        $request = $this->requestFor($landlord);

        try {
            app(MaintenanceRequestService::class)->assignRequest($request, $provider->id);
            $this->fail('A pending provider must not be assignable.');
        } catch (\DomainException $e) {
            $this->assertStringContainsString('not approved', $e->getMessage());
        }

        // Provider accepts.
        $service->approveConnection($provider, $landlord);

        $this->assertDatabaseHas('landlord_maintenance', [
            'landlord_id' => $landlord->id,
            'maintenance_user_id' => $provider->id,
            'status' => 'approved',
        ]);

        $assigned = app(MaintenanceRequestService::class)->assignRequest($request->fresh(), $provider->id);

        $this->assertSame($provider->id, $assigned->assigned_to);
    }

    public function test_assignment_dropdown_is_populated_through_the_ui_flow(): void
    {
        Notification::fake();

        $landlord = $this->landlord();
        $provider = $this->providerWithProfile('Dropdown Pro', published: true);

        // The gap this feature closes: before the directory existed nothing
        // wrote landlord_maintenance, so this list was always empty.
        $this->assertCount(0, app(MaintenanceRequestService::class)->getMaintenanceUsers($landlord->id));

        Livewire::actingAs($landlord)
            ->test(Directory::class)
            ->call('startRequest', $provider->id)
            ->set('requestMessage', 'Please join.')
            ->call('sendRequest')
            ->assertHasNoErrors();

        Livewire::actingAs($provider)
            ->test(Inbox::class)
            ->call('accept', $landlord->id);

        $this->assertCount(1, app(MaintenanceRequestService::class)->getMaintenanceUsers($landlord->id));
    }

    public function test_repeated_invitations_do_not_spam_the_provider(): void
    {
        Notification::fake();

        $landlord = $this->landlord();
        $provider = $this->providerWithProfile('Once Pro', published: true);
        $service = app(MaintenanceProfileService::class);

        $service->requestConnection($landlord, $provider);
        $service->requestConnection($landlord, $provider);
        $service->requestConnection($landlord, $provider);

        Notification::assertSentToTimes($provider, MaintenanceConnectionRequested::class, 1);

        $this->assertSame(1, \DB::table('landlord_maintenance')
            ->where('landlord_id', $landlord->id)
            ->where('maintenance_user_id', $provider->id)
            ->count());
    }

    public function test_declined_invitation_leaves_the_worker_unassignable(): void
    {
        Notification::fake();

        $landlord = $this->landlord();
        $provider = $this->providerWithProfile('Busy Pro', published: true);
        $service = app(MaintenanceProfileService::class);

        $service->requestConnection($landlord, $provider);
        $service->declineConnection($provider, $landlord);

        $this->assertDatabaseHas('landlord_maintenance', [
            'landlord_id' => $landlord->id,
            'maintenance_user_id' => $provider->id,
            'status' => 'rejected',
            'approved_at' => null,
        ]);

        $this->assertCount(0, app(MaintenanceRequestService::class)->getMaintenanceUsers($landlord->id));
    }

    public function test_revoking_a_connection_removes_assignability(): void
    {
        Notification::fake();

        $landlord = $this->landlord();
        $provider = $this->providerWithProfile('Dropped Pro', published: true);
        $service = app(MaintenanceProfileService::class);

        $service->requestConnection($landlord, $provider);
        $service->approveConnection($provider, $landlord);
        $this->assertCount(1, app(MaintenanceRequestService::class)->getMaintenanceUsers($landlord->id));

        $service->revokeConnection($landlord, $provider);
        $this->assertCount(0, app(MaintenanceRequestService::class)->getMaintenanceUsers($landlord->id));
    }

    public function test_renaming_a_verified_business_clears_the_verified_badge(): void
    {
        $provider = $this->providerWithProfile('Trusted Pro', published: true);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $provider->maintenanceProfile->update([
            'verified_at' => now(),
            'verified_by' => $admin->id,
        ]);

        Livewire::actingAs($provider)
            ->test(Edit::class)
            ->set('business_name', 'Completely Different Co')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull($provider->fresh()->maintenanceProfile->verified_at);
    }

    public function test_a_provider_cannot_be_invited_by_a_non_landlord(): void
    {
        $tenantUser = User::factory()->create(['email_verified_at' => now()]);
        $tenantUser->assignRole('tenant');

        $this->expectException(\DomainException::class);

        app(MaintenanceProfileService::class)->requestConnection(
            $tenantUser,
            $this->providerWithProfile('Off Limits', published: true),
        );
    }

    public function test_every_new_page_renders_for_its_role(): void
    {
        Notification::fake();

        $landlord = $this->landlord();
        $provider = $this->providerWithProfile('Render Pro', published: true);
        $service = app(MaintenanceProfileService::class);

        // Exercise the populated state, not just the empty one — the pivot
        // timestamps these views format only exist once a link is approved.
        $service->requestConnection($landlord, $provider, 'Hello.');

        $this->actingAs($provider)->get(route('maintenance-network.inbox'))->assertOk();

        $service->approveConnection($provider, $landlord);

        $this->actingAs($provider)->get(route('maintenance-profile.edit'))->assertOk();
        $this->actingAs($provider)->get(route('maintenance-network.inbox'))->assertOk();
        $this->actingAs($provider)->get(route('maintenance.dashboard'))->assertOk();

        $this->actingAs($landlord)->get(route('maintenance-directory.index'))->assertOk();
        $this->actingAs($landlord)->get(route('maintenance-network.index'))->assertOk();
    }

    public function test_maintenance_dashboard_renders_without_a_profile(): void
    {
        $this->actingAs($this->provider())
            ->get(route('maintenance.dashboard'))
            ->assertOk()
            ->assertSee('Set up your provider profile');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function landlord(): User
    {
        $landlord = User::factory()->create(['email_verified_at' => now()]);
        $landlord->assignRole('landlord');

        return $landlord;
    }

    private function provider(): User
    {
        $provider = User::factory()->create(['email_verified_at' => now()]);
        $provider->assignRole('maintenance');

        return $provider;
    }

    /**
     * @param  array<int, string>  $trades
     */
    private function providerWithProfile(string $businessName, bool $published, array $trades = ['plumbing']): User
    {
        $provider = $this->provider();

        MaintenanceProfile::create([
            'user_id' => $provider->id,
            'business_name' => $businessName,
            'bio' => 'Reliable work.',
            'trades' => $trades,
            'service_areas' => ['Balbala'],
            'phone' => '+25377000123',
            'is_published' => $published,
        ]);

        return $provider->fresh();
    }

    private function requestFor(User $landlord)
    {
        $currency = Currency::firstOrCreate(
            ['code' => 'DJF'],
            ['name' => 'Djiboutian Franc', 'symbol' => 'Fdj', 'decimals' => 0, 'is_active' => true],
        );

        $property = Property::create([
            'landlord_id' => $landlord->id,
            'currency_id' => $currency->id,
            'name' => 'Directory Property',
            'type' => 'apartment',
            'address_line_1' => '1 Rue de la Paix',
            'city' => 'Djibouti',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'property_id' => $property->id,
            'unit_number' => 'D1',
            'type' => 'apartment',
            'monthly_rent' => 50000,
            'status' => 'occupied',
            'is_active' => true,
        ]);

        $tenantUser = User::factory()->create(['email_verified_at' => now()]);
        $tenantUser->assignRole('tenant');

        $tenant = Tenant::create([
            'landlord_id' => $landlord->id,
            'user_id' => $tenantUser->id,
            'first_name' => 'Dir',
            'last_name' => 'Tenant',
            'phone' => '+25377000002',
            'email' => $tenantUser->email,
            'status' => 'active',
        ]);

        return app(MaintenanceRequestService::class)->createRequest([
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $tenant->id,
            'title' => 'Leaking tap',
            'description' => 'Kitchen tap drips.',
            'priority' => 'medium',
        ], $landlord);
    }
}
