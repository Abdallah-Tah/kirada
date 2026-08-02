<?php

namespace App\Mail;

use App\Models\Contract;
use App\Models\ContractSignature;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The countersigned contract, delivered to every party once the last signature
 * lands. The signed PDF is attached so each party holds their own copy without
 * needing to log in.
 */
class ContractCompleted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Contract $contract,
        public ContractSignature $signature,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Signed contract: :title', ['title' => $this->contract->title]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contracts.completed',
            with: [
                'signerName' => $this->signature->name,
                'contractTitle' => $this->contract->title,
                'reference' => $this->contract->reference,
                'completedAt' => $this->contract->completed_at,
                'signers' => $this->contract->signatures,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $document = $this->contract->document;

        if (! $document) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('private', $document->file_path)
                ->as($document->original_filename)
                ->withMime($document->mime_type ?? 'application/pdf'),
        ];
    }
}
