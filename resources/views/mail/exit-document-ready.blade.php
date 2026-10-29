<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="margin:0; padding:24px; background-color:#f4f4f5; font-family:Helvetica,Arial,sans-serif; color:#18181b;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width:600px;" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="background-color:#ffffff; border-radius:8px; padding:32px; border:1px solid #e4e4e7;">
                            <h2 style="margin:0 0 16px; font-size:18px;">{{ $documentLabel }} Available</h2>
                            <p style="margin:0 0 16px; font-size:14px; line-height:1.6;">
                                Dear {{ $resignation->user->name }},
                            </p>
                            <p style="margin:0 0 16px; font-size:14px; line-height:1.6;">
                                Your <strong>{{ $documentLabel }}</strong> has been added to your account on {{ config('app.name') }} and is ready to download.
                            </p>
                            <p style="margin:0 0 16px; font-size:14px; line-height:1.6;">
                                Log in to the portal with your usual work email and password, then go to <strong>Exit Documents</strong> to download it, along with any other documents already issued.
                            </p>

                            <p style="margin:24px 0 0; font-size:12px; color:#a1a1aa;">
                                This is an automated notification from {{ config('app.name') }}, sent to your personal email on file since this account may no longer have portal access after your last working date.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
