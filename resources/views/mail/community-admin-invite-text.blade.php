Help run a community

{{ $invitation->inviter->name }} invited you to be an admin of {{ $invitation->community->name }}.

Review the invitation:
{{ url('/invitations/admin/'.$invitation->token) }}

If you were not expecting this, you can ignore this email.
