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

        $subscription = $landlord->fresh()->kiradaSubscription;

        $this->assertSame('trialing', $subscription->status);
        $this->assertTrue($subscription->plan->is($growth));
    }

    public function test_expired_trial_opens_secure_card_checkout_for_the_selected_plan(): void
    {
        $landlord = User::factory()->create();
        $landlord->assignRole('landlord');
        $landlord->kiradaSubscription()->create([
            'status' => 'trialing',
            'trial_ends_at' => now()->subDay(),
        ]);

        $business = Plan::where('slug', 'business')->firstOrFail();

        Livewire::actingAs($landlord)
            ->test(Status::class)
            ->call('selectPlan', 'business')
            ->assertSet('selectedPlanSlug', 'business');

        $subscription = $landlord->fresh()->kiradaSubscription;
        $this->assertSame('trialing', $subscription->status);
        $this->assertFalse($subscription->plan?->is($business) ?? false);
    }

    public function test_plan_selection_renders_the_shared_confirmation_contract(): void
    {
        $landlord = User::factory()->create();
        $landlord->assignRole('landlord');

        app(SubscriptionService::class)->startTrial(
            $landlord,
            Plan::where('slug', 'starter')->firstOrFail(),
        );

        Livewire::actingAs($landlord)
            ->test(Status::class)
            ->assertSee('data-confirm-title="Confirm plan selection"', false)
            ->assertSee('data-confirm-variant="warning"', false);
    }
}
