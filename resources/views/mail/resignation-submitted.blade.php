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
                            <h2 style="margin:0 0 16px; font-size:18px;">Resignation Notice</h2>
                            <p style="margin:0 0 16px; font-size:14px; line-height:1.6;">
                                <strong>{{ $resignation->user->name }}</strong> ({{ $resignation->user->email }}) has submitted a resignation notice via the HR portal.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px; font-size:14px;">
                                <tr>
                                    <td style="padding:4px 0; color:#71717a;">Notice date</td>
                                    <td style="padding:4px 0; text-align:right;">{{ $resignation->notice_date->format('M j, Y') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:4px 0; color:#71717a;">Proposed last working date</td>
                                    <td style="padding:4px 0; text-align:right;">{{ $resignation->last_working_date->format('M j, Y') }}</td>
                                </tr>
                                @if ($resignation->cc_email)
                                    <tr>
                                        <td style="padding:4px 0; color:#71717a;">CC'd</td>
                                        <td style="padding:4px 0; text-align:right;">{{ $resignation->cc_email }}</td>
                                    </tr>
                                @endif
                            </table>

                            @if ($resignation->reason)
                                <h3 style="margin:0 0 8px; font-size:13px; text-transform:uppercase; letter-spacing:0.05em; color:#71717a;">Resignation Letter</h3>
                                <div style="font-size:14px; line-height:1.6; border-left:3px solid #e4e4e7; padding-left:16px;">
                                    {!! $resignation->reason !!}
                                </div>
                            @endif

                            <p style="margin:24px 0 0; font-size:12px; color:#a1a1aa;">
                                This is an automated notification from {{ config('app.name') }}.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
