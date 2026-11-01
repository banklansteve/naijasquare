<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Admin invitation</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f7f7;">
    <div style="display:none;max-height:0;overflow:hidden;mso-hide:all;">
        {{ $invitation->inviter->name }} invited you to help run {{ $invitation->community->name }}.
    </div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f7f7;">
        <tr>
            <td align="center" style="padding:40px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px;">
                    <tr>
                        <td style="padding:0 4px 18px;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:18px;line-height:1.2;font-weight:700;color:#1b5a61;">
                            {{ config('app.name') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color:#ffffff;border:1px solid #e2eaea;border-radius:24px;padding:36px 28px;">
                            <h1 style="margin:0;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:28px;line-height:1.2;font-weight:700;color:#1a2c2e;">
                                Help run a community
                            </h1>
                            <p style="margin:12px 0 0;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:16px;line-height:1.6;color:#4a5f61;">
                                {{ $invitation->inviter->name }} invited you to be an admin of
                                <strong style="color:#1a2c2e;">{{ $invitation->community->name }}</strong>.
                            </p>
                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:28px 0 8px;">
                                <tr>
                                    <td>
                                        <a href="{{ url('/invitations/admin/'.$invitation->token) }}" style="display:inline-block;background-color:#1b5a61;color:#ffffff;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:15px;font-weight:600;text-decoration:none;padding:14px 22px;border-radius:999px;">
                                            Review invitation
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:20px 0 0;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:13px;line-height:1.6;color:#8aa0a2;">
                                If you were not expecting this, you can ignore this email.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 4px 0;font-family:Segoe UI,Helvetica,Arial,sans-serif;font-size:12px;line-height:1.5;color:#8aa0a2;">
                            {{ config('app.name') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
