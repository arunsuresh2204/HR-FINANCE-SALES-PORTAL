<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; color: #1a1a1a; font-size: 11px; line-height: 1.6; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; }
        .no-border, .no-border td { border: none; padding: 0; }

        .letterhead td { vertical-align: top; }
        .logo-badge { width: 36px; height: 36px; vertical-align: middle; }
        .wordmark-cell { padding-left: 10px; vertical-align: middle; }
        .wordmark { font-size: 20px; font-weight: bold; letter-spacing: 0.5px; }
        .tagline { font-size: 7px; letter-spacing: 1.5px; color: #888; margin-top: 1px; }
        .contact-block { text-align: right; font-size: 9px; color: #333; }
        .contact-block div { margin-bottom: 2px; }

        .accent-bar td { height: 4px; font-size: 1px; line-height: 1px; padding: 0; }
        .accent-bar .gold { background: #f7c81e; width: 18%; }
        .accent-bar .dark { background: #0a0a0b; }

        .doc-title { margin-top: 30px; text-align: center; font-size: 14px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
        .doc-date { margin-top: 2px; text-align: center; font-size: 9px; color: #888; }

        .doc-body { margin-top: 26px; font-size: 11.5px; }
        .doc-body p { margin: 0 0 12px; }
        .doc-body strong { font-weight: bold; }
        .doc-body ul, .doc-body ol { margin: 0 0 12px; padding-left: 20px; }

        .signature-block { margin-top: 50px; }
        .signature-img { height: 36px; margin-bottom: 4px; }
        .signatory-name { font-weight: bold; font-size: 11px; }
        .signatory-title { font-size: 9px; color: #666; }

        .footer { margin-top: 60px; border-top: 1px solid #ddd; padding-top: 10px; text-align: center; }
        .footer-company { font-weight: bold; font-size: 9px; }
        .footer-line { font-size: 8px; color: #888; margin-top: 2px; }
    </style>
</head>
<body>
    <table class="letterhead no-border">
        <tr>
            <td style="width: 60%;">
                <table class="no-border">
                    <tr>
                        <td style="width: 36px;"><img class="logo-badge" src="{{ public_path('images/logo-mark.png') }}"></td>
                        <td class="wordmark-cell">
                            <div class="wordmark">NEXSTARC</div>
                            <div class="tagline">WEB &amp; DIGITAL SOLUTIONS STUDIO</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 40%;" class="contact-block">
                <div><strong>Email:</strong> hello@nexstarc.com</div>
                <div><strong>Phone / WhatsApp:</strong> +91 9061430404</div>
                <div><strong>Web:</strong> www.nexstarc.com</div>
            </td>
        </tr>
    </table>

    <table class="accent-bar no-border" style="margin-top: 8px;">
        <tr>
            <td class="gold"></td>
            <td class="dark"></td>
        </tr>
    </table>

    <div class="doc-title">{{ $title }}</div>
    <div class="doc-date">{{ now()->format('F j, Y') }}</div>

    <div class="doc-body">
        {!! $content !!}
    </div>

    <div class="signature-block">
        <div class="muted" style="margin-bottom: 8px;">For {{ config('company.brand_name') }},</div>
        @if ($financeSetting->signature_path ?? null)
            <img class="signature-img" src="{{ public_path('storage/'.$financeSetting->signature_path) }}">
        @endif
        <div class="signatory-name">{{ $financeSetting->signer_name ?? config('company.signatory_name') }}</div>
        <div class="signatory-title">{{ $financeSetting->signer_designation ?? config('company.signatory_title') }}</div>
    </div>

    <div class="footer">
        <div class="footer-company">{{ config('company.brand_name') }}</div>
        @foreach (config('company.address_lines') as $line)
            <div class="footer-line">{{ $line }}</div>
        @endforeach
        <div class="footer-line">For more information, visit nexstarc.com</div>
    </div>
</body>
</html>
