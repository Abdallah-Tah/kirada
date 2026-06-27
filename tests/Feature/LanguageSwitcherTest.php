<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageSwitcherTest extends TestCase
{
    use RefreshDatabase;

    public function test_language_switch_route_works(): void
    {
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'CountryCurrencySeeder']);
        $this->artisan('db:seed', ['--class' => 'PlanSeeder']);
        $this->artisan('db:seed', ['--class' => 'AdminUserSeeder']);

        $user = User::where('email', 'admin@kirada.dj')->first();

        // Switch to French
        $response = $this->actingAs($user)->get(route('language.switch', ['locale' => 'fr']));
        $response->assertRedirect();
        $this->assertEquals('fr', session('locale'));
        $this->assertEquals('fr', $user->fresh()->preferred_language);
    }

    public function test_invalid_locale_falls_back_to_en(): void
    {
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get(route('language.switch', ['locale' => 'xyz']));
        $response->assertRedirect();
        $this->assertEquals('en', session('locale'));
    }

    public function test_guest_can_switch_language(): void
    {
        $response = $this->get(route('language.switch', ['locale' => 'ar']));
        $response->assertRedirect();
        $this->assertEquals('ar', session('locale'));
    }

    public function test_arabic_sets_rtl_direction(): void
    {
        $this->get(route('language.switch', ['locale' => 'ar']));
        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee('dir="rtl"', false);
    }

    public function test_english_does_not_set_rtl(): void
    {
        $this->get(route('language.switch', ['locale' => 'en']));
        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertDontSee('dir="rtl"');
    }
}