<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingGuideTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_new_landlord_sees_the_landlord_onboarding_guide(): void
    {
        $landlord = User::factory()->create(['email_verified_at' => now()]);
        $landlord->assignRole('landlord');

        $this->actingAs($landlord)
            ->get(route('landlord.dashboard'))
            ->assertOk()
            ->assertSee('data-test="onboarding-guide"', false)
            ->assertSee(__('Welcome to Kirada'))
            ->assertSee(__('Set up your portfolio'));
    }

    public function test_onboarding_content_is_role_aware(): void
    {
        $maintenance = User::factory()->create(['email_verified_at' => now()]);
        $maintenance->assignRole('maintenance');

        $this->actingAs($maintenance)
            ->get(route('maintenance.dashboard'))
            ->assertOk()
            ->assertSee(__('Publish your provider profile'))
            ->assertDontSee(__('Set up your portfolio'));

        $tenant = User::factory()->create(['email_verified_at' => now()]);
        $tenant->assignRole('tenant');

        $this->actingAs($tenant)
            ->get(route('tenant.dashboard'))
            ->assertOk()
            ->assertSee(__('Review your lease and rent'))
            ->assertDontSee(__('Publish your provider profile'));
    }

    public function test_completed_user_does_not_see_the_guide_again(): void
    {
        $landlord = User::factory()->create([
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ]);
        $landlord->assignRole('landlord');

        $this->actingAs($landlord)
            ->get(route('landlord.dashboard'))
            ->assertOk()
            ->assertDontSee('data-test="onboarding-guide"', false);
    }

    public function test_user_can_complete_onboarding(): void
    {
        $landlord = User::factory()->create(['email_verified_at' => now()]);
        $landlord->assignRole('landlord');

        $this->actingAs($landlord)
            ->post(route('onboarding.complete'))
            ->assertRedirect();

        $this->assertNotNull($landlord->fresh()->onboarding_completed_at);
    }
}
