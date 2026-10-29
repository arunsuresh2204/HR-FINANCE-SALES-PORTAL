<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; color: #1a1a1a; font-size: 11px; line-height: 1.6; margin: 0; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; }
        .no-border, .no-border td { border: none; padding: 0; }
        .page-pad { padding: 28px 32px 0; }

        .letterhead td { vertical-align: top; }
        .wordmark-cell { vertical-align: middle; padding-left: 8px; }
        .wordmark { font-size: 21px; font-weight: bold; letter-spacing: -0.3px; color: #0a0a0b; }
        .wordmark .sub { font-size: 13px; font-weight: 600; color: #0a0a0b; }
        .wordmark-rule { margin-top: 5px; width: 110px; height: 2px; background: #f7c81e; font-size: 1px; line-height: 1px; }
        .tagline { font-size: 7px; letter-spacing: 1.5px; color: #888; margin-top: 5px; text-transform: uppercase; }

        .contact-box { position: relative; background: #f7c81e; border-radius: 0 0 0 36px; padding: 14px 20px 20px; text-align: right; }
        .contact-label { font-size: 7px; font-weight: bold; letter-spacing: 1px; color: #3a2e00; text-transform: uppercase; }
        .contact-value { font-size: 11px; font-weight: bold; color: #0a0a0b; margin: 1px 0 8px; }

        .doc-title { margin-top: 36px; text-align: center; font-size: 15px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; color: #0a0a0b; }
        .doc-date { margin-top: 3px; text-align: center; font-size: 9px; color: #888; }

        .doc-body { margin-top: 28px; font-size: 11.5px; }
        .doc-body p { margin: 0 0 12px; }
        .doc-body strong { font-weight: bold; }
        .doc-body ul, .doc-body ol { margin: 0 0 12px; padding-left: 20px; }

        .signature-block { margin-top: 40px; }
        .signature-img { height: 36px; margin-bottom: 4px; }
        .signatory-name { font-weight: bold; font-size: 11px; }
        .signatory-title { font-size: 9px; color: #666; }

        .footer { margin-top: 30px; background: #0a0a0b; color: #fff; padding: 14px 32px; }
        .footer-tagline { font-size: 8.5px; color: #cfcfcf; vertical-align: middle; padding-left: 8px; }
        .footer-web { text-align: right; font-size: 9px; font-weight: bold; color: #fff; vertical-align: middle; }
        .footer-divider { border-top: 1px solid #2c2c2c; margin: 10px 0 8px; font-size: 1px; line-height: 1px; }
        .footer-address { text-align: center; font-size: 7.5px; color: #9a9a9a; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="page-pad">
        <table class="letterhead no-border">
            <tr>
                <td style="width: 55%;">
                    <table class="no-border">
                        <tr>
                            <td style="width: 34px;">
                                <img src="{{ public_path('images/logo-mark.svg') }}" width="34" height="34">
                            </td>
                            <td class="wordmark-cell">
                                <span class="wordmark">nexstarc <span class="sub">technologies</span></span>
                                <div class="wordmark-rule"></div>
                            </td>
                        </tr>
                    </table>
                    <div class="tagline">Web Development &amp; Applied AI Studio</div>
                </td>
                <td style="width: 45%;">
                    <div class="contact-box">
                        <div class="contact-label">Email</div>
                        <div class="contact-value">hello@nexstarc.com</div>
                        <div class="contact-label">WhatsApp &amp; Phone</div>
                        <div class="contact-value" style="margin-bottom: 0;">+91 90614 30404</div>
                    </div>
                </td>
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
    </div>

    <div class="footer">
        <table class="no-border">
            <tr>
                <td style="width: 20px;">
                    <img src="{{ public_path('images/logo-mark.svg') }}" width="18" height="18">
                </td>
                <td class="footer-tagline">Websites made to be found. By people. By search engines. By AI.</td>
                <td class="footer-web">www.nexstarc.com</td>
            </tr>
        </table>
        <div class="footer-divider"></div>
        <div class="footer-address">
            TC-2/3884, Sree Shylam Road, Sainika Nagar, Vadakkumbhagam,<br>
            Madathivila, Kazhakuttom, Thiruvananthapuram, Kerala 695582, India
        </div>
    </div>
</body>
</html>
