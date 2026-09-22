<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; color: #1a1a1a; font-size: 11px; line-height: 1.5; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; }
        .no-border, .no-border td { border: none; padding: 0; }

        .letterhead td { vertical-align: top; }
        .logo-badge { width: 40px; height: 40px; }
        .company-name { font-size: 13px; font-weight: bold; margin-top: 6px; }
        .doc-title { font-size: 22px; font-weight: bold; text-align: right; color: #b03052; }

        .meta-row td { padding-top: 2px; }
        .meta-label { color: #666; }
        .meta-value { text-align: right; font-weight: bold; }

        .amount-box { margin-top: 10px; border: 1px solid #ccc; border-radius: 4px; padding: 10px 14px; text-align: right; }
        .amount-label { color: #666; font-size: 10px; }
        .amount-value { font-size: 17px; font-weight: bold; }

        .divider { border-bottom: 1px solid #ccc; margin: 18px 0; }

        .bill-to-label { color: #666; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px; }
        .bill-to-name { font-weight: bold; font-size: 12px; margin-top: 4px; }

        .reason-label { color: #666; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px; margin-top: 16px; }
        .reason-text { margin-top: 4px; }

        .note { margin-top: 16px; font-size: 10px; color: #777; }

        .footer-columns { margin-top: 40px; }
        .footer-columns td { vertical-align: top; }
        .signatory-title { font-size: 10px; font-weight: bold; letter-spacing: 0.5px; }
        .signatory-line { margin: 22px 0 4px; border-top: 1px solid #999; width: 140px; }
        .signatory-name { font-weight: bold; font-size: 11px; }
    </style>
</head>
<body>
    <table class="letterhead no-border">
        <tr>
            <td style="width: 55%;">
                <img class="logo-badge" src="{{ public_path('images/logo-mark.png') }}">
                <div class="company-name">{{ config('company.legal_name') }}</div>
                @foreach (config('company.address_lines') as $line)
                    <div class="muted">{{ $line }}</div>
                @endforeach
                @if ($financeSetting->gstin ?? config('company.gstin'))
                    <div class="muted" style="margin-top: 6px;">Tax ID: GSTIN - {{ $financeSetting->gstin ?? config('company.gstin') }}</div>
                @elseif (config('company.tax_id'))
                    <div class="muted" style="margin-top: 6px;">Tax ID ({{ config('company.tax_id_label') }}): {{ config('company.tax_id') }}</div>
                @endif
            </td>
            <td style="width: 45%;">
                <div class="doc-title">Refund Voucher</div>
                <table class="no-border meta-row" style="margin-top: 10px;">
                    <tr><td class="meta-label">Refund Voucher #</td><td class="meta-value">{{ $invoice->adjustment_document_number }}</td></tr>
                    <tr><td class="meta-label">Date issued</td><td class="meta-value">{{ $invoice->adjustment_at->format('d-M-Y') }}</td></tr>
                    <tr><td class="meta-label">Against Invoice</td><td class="meta-value">{{ $invoice->invoice_number }}</td></tr>
                </table>
                <div class="amount-box">
                    <div class="amount-label">Amount refunded:</div>
                    <div class="amount-value">{{ \App\Support\Currency::format($invoice->adjustment_amount, 'INR') }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="bill-to-label">Issued to</div>
    <div class="bill-to-name">{{ $invoice->client->business_name }}</div>
    @if ($invoice->client->owner_name)
        <div class="muted">{{ $invoice->client->owner_name }}</div>
    @endif
    @if ($invoice->client->business_address)
        <div class="muted">{{ $invoice->client->business_address }}</div>
    @endif

    <div class="reason-label">Reason</div>
    <div class="reason-text">{{ $invoice->adjustment_reason }}</div>

    <p class="note">This voucher confirms {{ \App\Support\Currency::format($invoice->adjustment_amount, 'INR') }} was refunded against Invoice {{ $invoice->invoice_number }}, paid out separately from the amounts recorded as received against it.</p>

    <table class="footer-columns no-border">
        <tr>
            <td>
                @if ($financeSetting->signature_path ?? null)
                    <img src="{{ public_path('storage/'.$financeSetting->signature_path) }}" alt="Signature" style="height: 40px; margin: 12px 0 4px;">
                @else
                    <div class="signatory-line"></div>
                @endif
                <div class="signatory-name">{{ $financeSetting->signer_name ?? config('company.signatory_name') }}</div>
                <div class="signatory-title">{{ $financeSetting->signer_designation ?? config('company.signatory_title') }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
