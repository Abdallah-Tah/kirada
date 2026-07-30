<?php

namespace Tests\Feature;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class BrandedErrorPagesTest extends TestCase
{
    public function test_forbidden_page_uses_kirada_brand_and_preserves_safe_context(): void
    {
        $html = view('errors.403', [
            'exception' => new HttpException(403, 'This contract is not available for signature.'),
        ])->render();

        $this->assertStringContainsString('Kirada', $html);
        $this->assertStringContainsString('ERROR 403', $html);
        $this->assertStringContainsString('This contract is not available for signature.', $html);
        $this->assertStringNotContainsString('Symfony', $html);
    }

    public function test_server_error_page_does_not_render_internal_exception_details(): void
    {
        $html = view('errors.500', [
            'exception' => new HttpException(500, 'Sensitive database failure details'),
        ])->render();

        $this->assertStringContainsString('ERROR 500', $html);
        $this->assertStringContainsString('Your data is safe', $html);
        $this->assertStringNotContainsString('Sensitive database failure details', $html);
    }
}
