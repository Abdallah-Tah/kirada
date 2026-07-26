<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Fortify\Features;

abstract class TestCase extends BaseTestCase
{
    /**
     * Guard against a stale bootstrap/cache/config.php overriding phpunit.xml.
     *
     * A cached config pins DB_CONNECTION/SESSION_DRIVER at build time and wins
     * over phpunit's <env> entries, which silently points RefreshDatabase at
     * the real MySQL database and wipes it. Fail loudly instead.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $connection = config('database.default');

        if ($connection !== 'sqlite' || config("database.connections.{$connection}.database") !== ':memory:') {
            $this->fail(
                "Refusing to run tests against the [{$connection}] connection. "
                .'phpunit.xml requires sqlite/:memory: — a cached config is overriding it. '
                .'Run `php artisan optimize:clear` and try again.'
            );
        }
    }

    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }
}
