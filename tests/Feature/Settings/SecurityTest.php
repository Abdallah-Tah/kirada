<?php

namespace Tests\Feature\Settings;

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
        $this->markTestSkipped('Volt settings pages — to be converted to pure Livewire.');
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