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
        ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->onTrial());
        $this->assertTrue(auth()->user()->subscription->plan->is($plan));
    }
}
