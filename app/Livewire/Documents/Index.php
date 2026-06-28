<?php

namespace App\Livewire\Documents;

use App\Models\Document;
use App\Services\DocumentService;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterType = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function documents()
    {
        $query = app(DocumentService::class)->getDocumentsForUser(auth()->user());

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('original_filename', 'like', "%{$this->search}%")
                  ->orWhereHas('tenant', function ($q) {
                      $q->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%");
                  });
            });
        }

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        return $query->paginate(15);
    }

    public function deleteDocument(int $id): void
    {
        $document = Document::findOrFail($id);
        $this->authorize('delete', $document);

        app(DocumentService::class)->deleteDocument($document);

        unset($this->documents);

        \Flux\Flux::toast('Document deleted.', 'success');
    }

    public function render()
    {
        return view('livewire.documents.index')
            ->layout('layouts.app')
            ->title(__('Documents'));
    }
}
