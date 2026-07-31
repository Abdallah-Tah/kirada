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
}
