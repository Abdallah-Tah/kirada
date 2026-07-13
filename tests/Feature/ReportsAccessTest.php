<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_landlord_can_view_reports(): void
    {
        $landlord = $this->userWithRole('landlord');
        $plan = Plan::create(['name' => 'Growth', 'slug' => 'growth', 'monthly_price' => 15000, 'is_active' => true]);
        Subscription::create([
            'user_id' => $landlord->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $this->actingAs($landlord)
            ->get(route('reports.index'))
            ->assertOk();
    }

    public function test_admin_can_view_reports(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->get(route('reports.index'))
            ->assertOk();
    }

    public function test_tenant_cannot_view_reports(): void
    {
        $this->actingAs($this->userWithRole('tenant'))
            ->get(route('reports.index'))
            ->assertForbidden();
    }

    public function test_maintenance_cannot_view_reports(): void
    {
        $this->actingAs($this->userWithRole('maintenance'))
            ->get(route('reports.index'))
            ->assertForbidden();
    }
}
