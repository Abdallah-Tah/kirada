<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_user_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('admin');

        $this->actingAs($admin);
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_landlord_user_is_redirected_to_landlord_dashboard(): void
    {
        $landlord = User::factory()->create(['email_verified_at' => now()]);
        $landlord->assignRole('landlord');

        $this->actingAs($landlord);
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('landlord.dashboard'));
    }

    public function test_authenticated_shell_uses_compact_brand_header_sidebar_and_footer(): void
    {
        $landlord = User::factory()->create(['email_verified_at' => now()]);
        $landlord->assignRole('landlord');

        $response = $this->actingAs($landlord)->get(route('landlord.dashboard'));

        $response
            ->assertOk()
            ->assertSee('Smart Rent Management')
            ->assertSee('data-test="global-search"', false)
            ->assertSee('Property Team')
            ->assertSee('All rights reserved.');
    }

    public function test_tenant_user_is_redirected_to_tenant_dashboard(): void
    {
        $tenant = User::factory()->create(['email_verified_at' => now()]);
        $tenant->assignRole('tenant');

        $this->actingAs($tenant);
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('tenant.dashboard'));
    }

    public function test_maintenance_user_is_redirected_to_maintenance_dashboard(): void
    {
        $maintenance = User::factory()->create(['email_verified_at' => now()]);
        $maintenance->assignRole('maintenance');

        $this->actingAs($maintenance);
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('maintenance.dashboard'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }
}
