<?php

namespace App\Livewire\Search;

use App\Services\GlobalSearchService;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(as: 'q')]
    public string $query = '';

    public function render(GlobalSearchService $search)
    {
        $groups = $search->search(auth()->user(), $this->query);
        $resultCount = collect($groups)->sum(fn (array $group) => $group['results']->count());

        return view('livewire.search.index', compact('groups', 'resultCount'))
            ->layout('layouts.app')
            ->title(__('Global Search'));
    }
}
