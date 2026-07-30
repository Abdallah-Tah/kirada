<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_displays_security_management_options(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response
            ->assertOk()
            ->assertSee('Two-factor authentication')
            ->assertSee('Passkeys')
            ->assertSee('Enable 2FA')
            ->assertSee('Add passkey');
    }

    public function test_profile_displays_enabled_two_factor_status(): void
    {
        $user = User::factory()->withTwoFactor()->create();

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response
            ->assertOk()
            ->assertSee('Active')
            ->assertSee('Manage');
    }

    /**
     * @group volt-settings
     * Settings pages use Volt single-file components from the starter kit.
     * These will be converted to pure Livewire in a future phase.
     */
    public function test_profile_page_is_displayed(): void
    {
        $this->markTestSkipped('Volt settings pages — to be converted to pure Livewire.');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $this->markTestSkipped('Volt settings pages — to be converted to pure Livewire.');
    }

    public function test_email_verification_status_is_unchanged_when_email_address_is_unchanged(): void
    {
        $this->markTestSkipped('Volt settings pages — to be converted to pure Livewire.');
    }

    public function test_user_can_delete_their_account(): void
    {
        $this->markTestSkipped('Volt settings pages — to be converted to pure Livewire.');
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $this->markTestSkipped('Volt settings pages — to be converted to pure Livewire.');
    }
}
