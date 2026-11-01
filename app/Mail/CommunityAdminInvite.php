<?php

namespace App\Mail;

use App\Models\CommunityAdminInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CommunityAdminInvite extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public CommunityAdminInvitation $invitation)
    {
        $this->invitation->loadMissing(['community', 'inviter']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You are invited to help run '.$this->invitation->community->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.community-admin-invite',
            text: 'mail.community-admin-invite-text',
        );
    }
}
