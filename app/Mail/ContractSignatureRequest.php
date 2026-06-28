<?php

namespace App\Mail;

use App\Models\ContractSignature;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractSignatureRequest extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public ContractSignature $signature) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Signature requested: :title', ['title' => $this->signature->contract->title]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contracts.signature-request',
            with: [
                'signerName' => $this->signature->name,
                'contractTitle' => $this->signature->contract->title,
                'reference' => $this->signature->contract->reference,
                'roleLabel' => $this->signature->role_label,
                'url' => route('contracts.sign', $this->signature->token),
            ],
        );
    }
}
