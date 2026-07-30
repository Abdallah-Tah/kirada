<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_public_pages_send_browser_security_headers(): void
    {
        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
            ->assertHeader('Cross-Origin-Resource-Policy', 'same-origin');

        $policy = (string) $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("object-src 'none'", $policy);
        $this->assertStringContainsString("frame-ancestors 'none'", $policy);
        $this->assertStringContainsString("base-uri 'self'", $policy);
        $this->assertStringContainsString("form-action 'self'", $policy);
    }

    public function test_sensitive_browser_capabilities_are_disabled_by_default(): void
    {
        $this->get(route('home'))
            ->assertHeader(
                'Permissions-Policy',
                'camera=(), microphone=(), geolocation=(self), payment=(self)',
            );
    }
}
