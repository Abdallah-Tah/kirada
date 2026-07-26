<?php

namespace App\Mail;

use App\Models\LandlordTeamMembership;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LandlordTeamInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public LandlordTeamMembership $membership,
        public string $acceptUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('You are invited to a Kirada property team'));
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.team.invitation',
            with: [
                'landlordName' => $this->membership->landlord->name,
                'role' => __(str($this->membership->role)->replace('-', ' ')->title()->toString()),
                'acceptUrl' => $this->acceptUrl,
                'expiresAt' => $this->membership->expires_at->format('M j, Y'),
            ],
        );
    }
}
