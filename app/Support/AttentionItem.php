<?php

namespace App\Support;

/**
 * One actionable row in the header's "Needs attention" menu.
 *
 * An item only exists when it has a non-zero count, so the menu never renders
 * a line the user cannot act on.
 */
class AttentionItem
{
    public function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly string $url,
        public readonly string $icon,
        public readonly int $count,
        public readonly string $countClass,
        /** Public token pages sit outside the app shell, so they need a full load. */
        public readonly bool $navigate = true,
    ) {}
}
