<?php

namespace Tests\Feature;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tests\TestCase;

class FrenchTranslationCompletenessTest extends TestCase
{
    public function test_shared_navigation_labels_resolve_without_leaking_translation_keys(): void
    {
        app()->setLocale('fr');

        $this->assertSame('Messages', __('Messages'));
        $this->assertSame('Documents', __('Documents'));
        $this->assertSame('Abonnement', __('Subscription'));

        $translations = json_decode(
            file_get_contents(lang_path('fr.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $this->assertArrayNotHasKey('messages.Messages', $translations);
        $this->assertArrayNotHasKey('messages.Documents', $translations);
        $this->assertArrayNotHasKey('messages.Subscription', $translations);
    }

    public function test_unit_type_dropdown_labels_resolve_in_french(): void
    {
        app()->setLocale('fr');

        $this->assertSame([
            'Appartement',
            'Maison',
            'Villa',
            'Studio',
            'Duplex',
            'Pièce',
            'Bureau',
            'Boutique',
            'Entrepôt',
            'Autre',
        ], array_map(
            static fn (string $type): string => __($type),
            ['Apartment', 'House', 'Villa', 'Studio', 'Duplex', 'Room', 'Office', 'Shop', 'Warehouse', 'Other'],
        ));
    }

    public function test_french_locale_covers_all_literal_user_facing_strings(): void
    {
        $translations = json_decode(
            file_get_contents(lang_path('fr.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $missing = [];
        $placeholderMismatches = [];

        foreach ($this->translationKeys() as $key => $sourcePath) {
            if (! array_key_exists($key, $translations)) {
                $missing[$key] = $sourcePath;

                continue;
            }

            $sourcePlaceholders = $this->placeholders($key);
            $translatedPlaceholders = $this->placeholders($translations[$key]);

            if ($sourcePlaceholders !== $translatedPlaceholders) {
                $placeholderMismatches[$key] = [
                    'source' => $sourcePlaceholders,
                    'translation' => $translatedPlaceholders,
                ];
            }
        }

        $this->assertSame(
            [],
            $missing,
            "Missing French translations:\n".$this->formatFailures($missing),
        );

        $this->assertSame(
            [],
            $placeholderMismatches,
            "French translation placeholders do not match their source strings:\n".
                $this->formatFailures($placeholderMismatches),
        );
    }

    /**
     * @return array<string, string>
     */
    private function translationKeys(): array
    {
        $keys = array_fill_keys([
            'D-Money',
            'Waafi Pay',
            'Cac Bank',
            'Plumbing',
            'Electrical',
            'AC / Heating',
            'Appliance',
            'Door / Lock',
            'Pest',
            'Cleaning',
            'Safety',
        ], 'dynamic application label');

        foreach ([app_path(), resource_path('views'), base_path('routes')] as $directory) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory),
            );

            /** @var SplFileInfo $file */
            foreach ($files as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }

                $source = file_get_contents($file->getPathname());
                $patterns = [
                    "/__\\(\\s*'((?:\\\\.|[^'])*)'/",
                    '/__\\(\\s*"((?:\\\\.|[^"])*)"/',
                    "/@lang\\(\\s*'((?:\\\\.|[^'])*)'/",
                    "/trans\\(\\s*'((?:\\\\.|[^'])*)'/",
                    "/trans_choice\\(\\s*'((?:\\\\.|[^'])*)'/",
                    '/trans_choice\\(\s*"((?:\\\\.|[^"])*)"/',
                ];

                foreach ($patterns as $pattern) {
                    preg_match_all($pattern, $source, $matches);

                    foreach ($matches[1] as $match) {
                        $key = str_replace(["\\'", '\\\\'], ["'", '\\'], $match);

                        if ($this->isJsonTranslationKey($key)) {
                            $keys[$key] = str_replace(base_path().'/', '', $file->getPathname());
                        }
                    }
                }
            }
        }

        ksort($keys);

        return $keys;
    }

    private function isJsonTranslationKey(string $key): bool
    {
        if (str_contains($key, '::') || str_contains($key, '$')) {
            return false;
        }

        return preg_match('/^[A-Za-z0-9_-]+\.[A-Za-z0-9_.-]+$/', $key) !== 1;
    }

    /**
     * @return list<string>
     */
    private function placeholders(string $value): array
    {
        preg_match_all('/:[A-Za-z_][A-Za-z0-9_]*/', $value, $matches);
        $placeholders = array_values(array_unique($matches[0]));
        sort($placeholders);

        return $placeholders;
    }

    /**
     * @param  array<mixed>  $failures
     */
    private function formatFailures(array $failures): string
    {
        return json_encode(
            $failures,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        ) ?: 'Unable to format translation failures.';
    }
}
