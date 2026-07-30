<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\CountryCurrencySeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Events\WebhookHandled;
use Tests\TestCase;

class CashierSubscriptionSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_cashier_subscription_event_syncs_kirada_plan_access(): void
    {
        $this->seed(CountryCurrencySeeder::class);
        $this->seed(PlanSeeder::class);

        $user = User::factory()->create(['stripe_id' => 'cus_kirada']);
        $plan = Plan::where('slug', 'growth')->firstOrFail();
        $plan->update(['stripe_price_id' => 'price_growth']);

        $subscriptionId = DB::table('subscriptions')->insertGetId([
            'user_id' => $user->id,
            'type' => 'default',
            'stripe_id' => 'sub_kirada',
            'stripe_status' => 'active',
            'stripe_price' => 'price_growth',
            'status' => 'trialing',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        WebhookHandled::dispatch([
            'type' => 'customer.subscription.created',
            'data' => [
                'object' => [
                    'id' => 'sub_kirada',
                    'status' => 'active',
                    'metadata' => ['kirada_plan_id' => (string) $plan->id],
                    'items' => ['data' => [['price' => ['id' => 'price_growth']]]],
                ],
            ],
        ]);

        $subscription = Subscription::findOrFail($subscriptionId);

        $this->assertSame('active', $subscription->status);
        $this->assertSame('stripe', $subscription->gateway);
        $this->assertSame('card', $subscription->payment_method);
        $this->assertTrue($subscription->plan->is($plan));
    }

    public function test_non_subscription_webhooks_do_not_change_kirada_access(): void
    {
        WebhookHandled::dispatch([
            'type' => 'customer.updated',
            'data' => ['object' => ['id' => 'cus_other']],
        ]);

        $this->assertDatabaseCount('subscriptions', 0);
    }

    public function test_existing_stripe_subscription_cannot_start_duplicate_checkout(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(CountryCurrencySeeder::class);
        $this->seed(PlanSeeder::class);

        $user = User::factory()->create(['stripe_id' => 'cus_existing']);
        $user->assignRole('landlord');
        DB::table('subscriptions')->insert([
            'user_id' => $user->id,
            'type' => 'default',
            'stripe_id' => 'sub_existing',
            'stripe_status' => 'active',
            'stripe_price' => 'price_existing',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('subscription.checkout', 'growth'))
            ->assertRedirect(route('subscription.status'))
            ->assertSessionHas('error');
    }
}
