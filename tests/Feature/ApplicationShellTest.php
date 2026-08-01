<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApplicationShellTest extends TestCase
{
    public function test_shared_application_shell_owns_viewport_and_scroll_boundaries(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($css);
        $this->assertMatchesRegularExpression(
            '/\.kirada-app-shell\s*\{[^}]*min-height:\s*100dvh;[^}]*height:\s*100dvh;/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.kirada-app-body-region\s*\{[^}]*min-width:\s*0;[^}]*min-height:\s*0;[^}]*height:\s*calc\(100dvh - var\(--kirada-header-height\)\);[^}]*overflow:\s*hidden;/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.kirada-app-main\s*\{[^}]*min-width:\s*0;[^}]*min-height:\s*0;[^}]*overflow-y:\s*auto;/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.kirada-sidebar-nav\s*\{[^}]*overflow-y:\s*auto\s*!important;[^}]*min-height:\s*0\s*!important;/s',
            $css,
        );
        $this->assertMatchesRegularExpression(
            '/\.kirada-footer\s*\{(?=[^}]*position:\s*sticky)(?=[^}]*bottom:\s*0)(?=[^}]*flex:\s*0\s+0\s+auto)[^}]*\}/s',
            $css,
        );
    }

    public function test_light_is_the_default_appearance_without_overriding_explicit_choices(): void
    {
        $head = file_get_contents(resource_path('views/partials/head.blade.php'));

        $this->assertIsString($head);
        $this->assertStringContainsString('@fluxAppearance', $head);
        $this->assertStringContainsString("window.localStorage.setItem('flux.appearance', 'light');", $head);
        $this->assertStringContainsString('kirada.appearance-preference.user.', $head);
        $this->assertStringContainsString('window.KIRADA_APPEARANCE_KEY', $head);
        $this->assertStringContainsString(':root:not(.dark) { color-scheme: light; }', $head);
        $this->assertStringContainsString(':root.dark { color-scheme: dark; }', $head);
        $this->assertStringNotContainsString(':root { color-scheme: light; }', $head);
    }
}
