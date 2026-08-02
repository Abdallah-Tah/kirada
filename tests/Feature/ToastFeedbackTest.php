<?php

namespace Tests\Feature;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class ToastFeedbackTest extends TestCase
{
    public function test_flux_feedback_uses_variants_instead_of_status_headings(): void
    {
        $misconfiguredToasts = [];
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(app_path('Livewire')),
        );

        foreach ($files as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $source = file_get_contents($file->getPathname());

            if (preg_match("/Flux::toast\\(.*?,\\s*'(?:success|danger|error|warning|info)'\\s*,?\\s*\\);/s", $source)) {
                $misconfiguredToasts[] = $file->getPathname();
            }
        }

        $this->assertSame(
            [],
            $misconfiguredToasts,
            'Toast status must be passed with the named variant argument so its icon and color are rendered.',
        );
    }

    public function test_invitation_feedback_has_a_success_icon_variant(): void
    {
        $source = file_get_contents(app_path('Livewire/TenantInvitations/Index.php'));

        $this->assertIsString($source);
        $this->assertStringContainsString(
            "Flux::toast(text: __('Invitation created and delivery queued.'), variant: 'success');",
            $source,
        );
    }

    public function test_toaster_uses_kirada_theme_tokens_for_light_and_dark_modes(): void
    {
        $toast = file_get_contents(resource_path('views/flux/toast/index.blade.php'));
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertIsString($toast);
        $this->assertIsString($css);
        $this->assertStringContainsString('kirada-toast-card', $toast);
        $this->assertStringContainsString('kirada-toast-status-success', $toast);
        $this->assertStringContainsString('background: color-mix(in srgb, var(--kirada-surface)', $css);
        $this->assertStringContainsString('color: var(--kirada-text);', $css);
        $this->assertStringContainsString('.dark .kirada-toast-card', $css);
        $this->assertStringContainsString('var(--color-kirada-teal-bright)', $css);
    }
}
