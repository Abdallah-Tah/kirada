<?php

namespace App\Mail;

use App\Models\TenantInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public TenantInvitation $invitation,
        public string $tenantName,
        public string $landlordName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('You\'re invited to join Kirada'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tenant.invitation',
            with: [
                'tenantName' => $this->tenantName,
                'landlordName' => $this->landlordName,
                'acceptUrl' => $this->invitation->accept_url,
                'expiresAt' => $this->invitation->expires_at->format('M d, Y'),
            ],
        );
    }
}