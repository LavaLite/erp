<?php

namespace App\Mail;

use App\Models\TeamInvitation as TeamInvitationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeamInvitation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public TeamInvitationModel $invitation
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invitation to join ' . $this->invitation->team->name . ' team',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $confirmUrl = route('team.invitation.show', ['token' => $this->invitation->token]);

        return new Content(
            view: 'emails.team-invitation',
            with: [
                'teamName' => $this->invitation->team->name,
                'organizationName' => $this->invitation->team->organization->name,
                'inviterName' => $this->invitation->inviter->name,
                'role' => $this->invitation->role,
                'confirmUrl' => $confirmUrl,
                'expiresAt' => $this->invitation->expires_at->format('F j, Y g:i A'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
