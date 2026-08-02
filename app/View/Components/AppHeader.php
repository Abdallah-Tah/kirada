<?php

namespace App\View\Components;

use App\Models\User;
use App\Services\AttentionService;
use App\Support\AttentionItem;
use Illuminate\View\Component;
use Illuminate\View\View;

class AppHeader extends Component
{
    public ?User $user;

    /** @var array<int, AttentionItem> */
    public array $attentionItems;

    public int $attentionCount;

    /** @var array<string, string> */
    public array $locales = [
        'en' => 'English',
        'fr' => 'Français',
        'ar' => 'العربية',
        'so' => 'Soomaali',
        'am' => 'አማርኛ',
    ];

    public string $currentLocale;

    public function __construct(AttentionService $attention)
    {
        $this->user = auth()->user();
        $this->attentionItems = $attention->itemsFor($this->user);
        $this->attentionCount = array_sum(array_map(
            static fn (AttentionItem $item): int => $item->count,
            $this->attentionItems,
        ));
        $this->currentLocale = app()->getLocale();
    }

    public function render(): View
    {
        return view('components.app-header');
    }
}
