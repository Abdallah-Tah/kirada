<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @group volt-settings
     * Settings pages use Volt single-file components from the starter kit.
     * These will be converted to pure Livewire in a future phase.
     */
    public function test_security_settings_page_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('security.edit'));

        $response
            ->assertOk()
            ->assertSee('Two-factor authentication')
            ->assertSee('Passkeys')
            ->assertSee('lg:grid-cols-[13rem_minmax(0,1fr)]', false)
            ->assertSee('grid-cols-2', false)
            ->assertSee('lg:sticky', false);
    }

    public function test_security_settings_page_renders_recovery_codes_for_two_factor_user(): void
    {
        $user = User::factory()->withTwoFactor()->create();

        $response = $this
            ->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('security.edit'));

        $response
            ->assertOk()
            ->assertSee('2FA recovery codes')
            ->assertSee('View recovery codes');
    }

    public function test_security_settings_page_renders_without_two_factor_when_feature_is_disabled(): void
    {
        $this->markTestSkipped('Volt settings pages — to be converted to pure Livewire.');
    }

    public function test_two_factor_authentication_can_be_enabled(): void
    {
        $this->markTestSkipped('Volt settings pages — to be converted to pure Livewire.');
    }

    public function test_two_factor_authentication_can_be_disabled(): void
    {
        $this->markTestSkipped('Volt settings pages — to be converted to pure Livewire.');
    }

    public function test_two_factor_authentication_can_be_disabled_with_valid_password(): void
    {
        $this->markTestSkipped('Volt settings pages — to be converted to pure Livewire.');
    }
}
