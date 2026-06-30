<?php

namespace Tests\Feature\Auth;

use App\Models\Plan;
use Database\Seeders\CountryCurrencySeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::registration());

        $this->seed(RolePermissionSeeder::class);
        $this->seed(CountryCurrencySeeder::class);
        $this->seed(PlanSeeder::class);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'John Doe',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms_accepted' => '1',
            'privacy_accepted' => '1',
        ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->onTrial());
    }

    public function test_new_users_can_register_with_a_selected_plan(): void
    {
        $plan = Plan::where('slug', 'growth')->firstOrFail();

        $response = $this->post(route('register.store'), [
            'name' => 'John Doe',
            'email' => 'growth@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'selected_plan' => 'growth',
            'terms_accepted' => '1',
            'privacy_accepted' => '1',
        ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->onTrial());
        $this->assertTrue(auth()->user()->subscription->plan->is($plan));
    }

    public function test_registration_requires_terms_acceptance(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'John Doe',
            'email' => 'noterms@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['terms_accepted', 'privacy_accepted']);
        $this->assertGuest();
    }

    public function test_registration_records_legal_acceptance(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Legal Test',
            'email' => 'legal@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms_accepted' => '1',
            'privacy_accepted' => '1',
        ]);

        $user = \App\Models\User::where('email', 'legal@example.com')->first();
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertNotNull($user->privacy_accepted_at);

        $this->assertDatabaseHas('legal_acceptances', [
            'user_id' => $user->id,
            'document_type' => 'terms-of-service',
        ]);
        $this->assertDatabaseHas('legal_acceptances', [
            'user_id' => $user->id,
            'document_type' => 'privacy-policy',
        ]);
    }
}
