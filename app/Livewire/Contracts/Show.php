<?php

namespace App\Livewire\Contracts;

use App\Models\Contract;
use App\Services\ContractService;
use DOMDocument;
use DOMNode;
use Flux\Flux;
use Livewire\Component;

class Show extends Component
{
    public Contract $contract;

    // ── Editor state ────────────────────────────────────────────────────
    public bool $editing = false;

    /** Everything before the first <h2> (title + subtitle h1/p) */
    public string $headerHtml = '';

    /** Content of the contract-closing <p> */
    public string $closingHtml = '';

    /** [ ['heading' => string, 'paragraphs' => string[]], … ] */
    public array $sections = [];

    // ── Lifecycle ────────────────────────────────────────────────────────

    public function mount(Contract $contract): void
    {
        $this->authorize('view', $contract);

        $this->contract = $contract->load(['signatures', 'tenant', 'lease', 'document']);
    }

    // ── Editor actions ───────────────────────────────────────────────────

    public function startEditing(): void
    {
        $this->authorize('update', $this->contract);

        $this->parseBody($this->contract->body_html ?? '');
        $this->editing = true;
    }

    public function cancelEditing(): void
    {
        $this->editing = false;
        $this->sections   = [];
        $this->headerHtml = '';
        $this->closingHtml = '';
    }

    public function saveBody(): void
    {
        $this->authorize('update', $this->contract);

        $this->contract->update(['body_html' => $this->buildBody()]);
        $this->refreshContract();
        $this->cancelEditing();

        Flux::toast(__('Contract saved.'), 'success');
    }

    public function addArticle(): void
    {
        $articleCount = \count(\array_filter(
            $this->sections,
            fn ($s) => str_starts_with(trim($s['heading'] ?? ''), 'Article')
        ));

        $this->sections[] = [
            'heading'    => 'Article ' . ($articleCount + 1) . ' — ',
            'paragraphs' => [''],
        ];
    }

    public function removeSection(int $index): void
    {
        array_splice($this->sections, $index, 1);
        $this->sections = array_values($this->sections);
    }

    public function addParagraph(int $sectionIndex): void
    {
        $this->sections[$sectionIndex]['paragraphs'][] = '';
    }

    public function removeParagraph(int $sectionIndex, int $paraIndex): void
    {
        array_splice($this->sections[$sectionIndex]['paragraphs'], $paraIndex, 1);
        $this->sections[$sectionIndex]['paragraphs'] = array_values(
            $this->sections[$sectionIndex]['paragraphs']
        );
    }

    // ── Contract lifecycle actions ───────────────────────────────────────

    public function send(): void
    {
        $this->authorize('update', $this->contract);

        app(ContractService::class)->send($this->contract);
        $this->refreshContract();

        Flux::toast(__('Contract sent for signature.'), 'success');
    }

    public function cancel(): void
    {
        $this->authorize('update', $this->contract);

        app(ContractService::class)->cancel($this->contract);
        $this->refreshContract();

        Flux::toast(__('Contract cancelled.'), 'success');
    }

    public function resend(int $signatureId): void
    {
        $this->authorize('update', $this->contract);

        $signature = $this->contract->signatures()->whereKey($signatureId)->firstOrFail();

        if ($signature->status !== 'pending' || blank($signature->email)) {
            return;
        }

        if (! $this->contract->isSent()) {
            return;
        }

        app(ContractService::class)->sendSignatureRequest($signature);

        Flux::toast(__('Signing link emailed to :name.', ['name' => $signature->name]), 'success');
    }

    public function signingUrl(string $token): string
    {
        return route('contracts.sign', $token);
    }

    // ── HTML parser / builder ─────────────────────────────────────────────

    private function parseBody(string $html): void
    {
        $this->headerHtml  = '';
        $this->closingHtml = '';
        $this->sections    = [];

        if (blank($html)) {
            return;
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>' . $html . '</body></html>'
        );
        libxml_clear_errors();

        $body = $dom->getElementsByTagName('body')->item(0);
        if (! $body) {
            return;
        }

        $current = null;

        foreach ($body->childNodes as $node) {
            if (! ($node instanceof \DOMElement)) {
                continue;
            }

            $tag   = strtolower($node->tagName);
            $class = $node->getAttribute('class');

            if ($tag === 'h1' || in_array($class, ['contract-title', 'contract-subtitle'])) {
                // Preserve the auto-generated header block unchanged.
                $this->headerHtml .= $dom->saveHTML($node);

            } elseif ($tag === 'h2') {
                if ($current !== null) {
                    $this->sections[] = $current;
                }
                $current = ['heading' => trim($node->textContent), 'paragraphs' => []];

            } elseif ($tag === 'p' && $class === 'contract-closing') {
                if ($current !== null) {
                    $this->sections[] = $current;
                    $current = null;
                }
                $this->closingHtml = $this->innerHtml($dom, $node);

            } elseif ($tag === 'p') {
                if ($current !== null) {
                    $current['paragraphs'][] = $this->innerHtml($dom, $node);
                }
            }
        }

        if ($current !== null) {
            $this->sections[] = $current;
        }
    }

    private function buildBody(): string
    {
        $html = $this->headerHtml;

        foreach ($this->sections as $section) {
            $heading = e($section['heading'] ?? '');
            $html   .= "\n<h2>{$heading}</h2>";

            foreach ($section['paragraphs'] as $para) {
                $content = trim($para ?? '');
                if ($content === '') {
                    continue;
                }
                // Tiptap outputs block HTML (<p>, <ul>, <ol>…); use as-is.
                // Legacy inline content (no outer block tag) gets wrapped.
                if (preg_match('/^<(p|ul|ol)\b/', $content)) {
                    $html .= "\n" . $content;
                } else {
                    $html .= "\n<p>{$content}</p>";
                }
            }
        }

        if (filled($this->closingHtml)) {
            $html .= "\n<p class=\"contract-closing\">{$this->closingHtml}</p>";
        }

        return trim($html);
    }

    private function innerHtml(DOMDocument $dom, DOMNode $node): string
    {
        $html = '';
        foreach ($node->childNodes as $child) {
            $html .= $dom->saveHTML($child);
        }

        return trim($html);
    }

    protected function refreshContract(): void
    {
        $this->contract = $this->contract->fresh(['signatures', 'tenant', 'lease', 'document']);
    }

    public function render()
    {
        return view('livewire.contracts.show')
            ->layout('layouts.app')
            ->title($this->contract->reference);
    }
}
