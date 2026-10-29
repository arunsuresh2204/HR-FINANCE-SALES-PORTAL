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
                            <h2 style="margin:0 0 16px; font-size:18px;">Resignation Accepted</h2>
                            <p style="margin:0 0 16px; font-size:14px; line-height:1.6;">
                                Dear {{ $resignation->user->name }},
                            </p>
                            <p style="margin:0 0 16px; font-size:14px; line-height:1.6;">
                                This confirms that your resignation from {{ config('app.name') }} has been accepted by HR.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px; font-size:14px;">
                                <tr>
                                    <td style="padding:4px 0; color:#71717a;">Notice date</td>
                                    <td style="padding:4px 0; text-align:right;">{{ $resignation->notice_date->format('M j, Y') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:4px 0; color:#71717a;">Last working date</td>
                                    <td style="padding:4px 0; text-align:right;">{{ $resignation->last_working_date->format('M j, Y') }}</td>
                                </tr>
                            </table>

                            <p style="margin:0 0 16px; font-size:14px; line-height:1.6;">
                                Your experience certificate, relieving letter, and other exit documents will be made available for download from the portal once offboarding is complete.
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
