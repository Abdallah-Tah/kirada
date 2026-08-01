<?php

namespace Tests\Feature;

use Tests\TestCase;

class WelcomePricingTest extends TestCase
{
    public function test_welcome_pricing_offers_monthly_and_annual_billing_in_djf(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Simple plans that grow with you')
            ->assertSee('Start with a 30-day free trial. No credit card required.')
            ->assertSee('Monthly')
            ->assertSee('Annual')
            ->assertSee('-20%')
            ->assertSee('48,000')
            ->assertSee('144,000')
            ->assertSee('384,000')
            ->assertSee('DJF')
            ->assertSee('$9 USD', false)
            ->assertSee('$29 USD', false)
            ->assertSee('$79 USD', false)
            ->assertSee('$86.40 USD', false)
            ->assertSee('$278.40 USD', false)
            ->assertSee('$758.40 USD', false);
    }
}
