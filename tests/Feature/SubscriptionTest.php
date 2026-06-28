<?php

namespace Tests\Feature;

use App\Livewire\Subscriptions\Status;
use App\Models\Plan;
use App\Models\User;
use App\Services\SubscriptionService;
use Database\Seeders\CountryCurrencySeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(CountryCurrencySeeder::class);
        $this->seed(PlanSeeder::class);
    }

    public function test_landlord_can_select_a_trial_plan(): void
    {
        $landlord = User::factory()->create();
        $landlord->assignRole('landlord');

        app(SubscriptionService::class)->startTrial(
            $landlord,
            Plan::where('slug', 'starter')->firstOrFail(),
        );

        $growth = Plan::where('slug', 'growth')->firstOrFail();

        Livewire::actingAs($landlord)
            ->test(Status::class)
            ->call('selectPlan', 'growth');

        $subscription = $landlord->fresh()->subscription;

        $this->assertSame('trialing', $subscription->status);
        $this->assertTrue($subscription->plan->is($growth));
    }

    public function test_landlord_can_activate_a_plan_after_trial_expiration(): void
    {
        $landlord = User::factory()->create();
        $landlord->assignRole('landlord');
        $landlord->subscription()->create([
            'status' => 'trialing',
            'trial_ends_at' => now()->subDay(),
        ]);

        $business = Plan::where('slug', 'business')->firstOrFail();

        Livewire::actingAs($landlord)
            ->test(Status::class)
            ->call('selectPlan', 'business');

        $subscription = $landlord->fresh()->subscription;

        $this->assertSame('active', $subscription->status);
        $this->assertSame('manual', $subscription->payment_method);
        $this->assertTrue($subscription->plan->is($business));
        $this->assertNotNull($subscription->starts_at);
        $this->assertNotNull($subscription->ends_at);
    }
}
