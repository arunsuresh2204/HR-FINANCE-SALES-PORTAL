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
        .invoice-title { font-size: 22px; font-weight: bold; text-align: right; }

        .meta-row td { padding-top: 2px; }
        .meta-label { color: #666; }
        .meta-value { text-align: right; font-weight: bold; }

        .amount-due-box { margin-top: 10px; border: 1px solid #ccc; border-radius: 4px; padding: 10px 14px; text-align: right; }
        .amount-due-label { color: #666; font-size: 10px; }
        .amount-due-value { font-size: 17px; font-weight: bold; }

        .divider { border-bottom: 1px solid #ccc; margin: 18px 0; }

        .bill-to-label { color: #666; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px; }
        .bill-to-name { font-weight: bold; font-size: 12px; margin-top: 4px; }

        .items-table { margin-top: 20px; }
        .items-table th { text-align: left; padding: 6px 8px; border-bottom: 1px solid #999; font-size: 10px; text-transform: uppercase; color: #444; }
        .items-table th.amount, .items-table td.amount { text-align: right; }
        .items-table td { padding: 8px; border-bottom: 1px solid #eee; vertical-align: top; }
        .item-desc-sub { color: #777; font-size: 10px; margin-top: 2px; }

        .totals-table { width: 260px; margin-left: auto; margin-top: 4px; }
        .totals-table td { padding: 4px 8px; border: none; }
        .totals-table .label { color: #555; }
        .totals-table .value { text-align: right; }
        .totals-table .total-row td { border-top: 1px solid #999; font-weight: bold; font-size: 12px; padding-top: 8px; }

        .export-note { margin-top: 10px; font-size: 10px; color: #777; }

        .footer-columns { margin-top: 40px; }
        .footer-columns td { vertical-align: top; width: 50%; padding-right: 20px; }
        .footer-heading { font-weight: bold; margin-bottom: 8px; }
        .signatory-title { font-size: 10px; font-weight: bold; letter-spacing: 0.5px; }
        .signatory-line { margin: 22px 0 4px; border-top: 1px solid #999; width: 140px; }
        .signatory-name { font-weight: bold; font-size: 11px; }
        .bank-line { margin-bottom: 3px; }
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
                @if (config('company.gstin'))
                    <div class="muted" style="margin-top: 6px;">Tax ID: GSTIN - {{ config('company.gstin') }}</div>
                @elseif (config('company.tax_id'))
                    <div class="muted" style="margin-top: 6px;">Tax ID ({{ config('company.tax_id_label') }}): {{ config('company.tax_id') }}</div>
                @endif
                @if (config('company.phone'))
                    <div class="muted" style="margin-top: 6px;">Phone: {{ config('company.phone') }}</div>
                @endif
                @if (config('company.email'))
                    <div class="muted">{{ config('company.email') }}</div>
                @endif
                @if (config('company.website'))
                    <div class="muted">{{ config('company.website') }}</div>
                @endif
            </td>
            <td style="width: 45%;">
                <div class="invoice-title">Invoice</div>
                <table class="no-border meta-row" style="margin-top: 10px;">
                    <tr><td class="meta-label">Invoice #</td><td class="meta-value">{{ $invoice->invoice_number }}</td></tr>
                    <tr><td class="meta-label">Invoice date</td><td class="meta-value">{{ $invoice->created_at->format('d-M-Y') }}</td></tr>
                    @if ($invoice->due_date)
                        <tr><td class="meta-label">Due date</td><td class="meta-value">{{ $invoice->due_date->format('d-M-Y') }}</td></tr>
                    @endif
                </table>
                <div class="amount-due-box">
                    <div class="amount-due-label">Amount due:</div>
                    <div class="amount-due-value">{{ $invoice->money($invoice->balanceDue()) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="bill-to-label">Bill to</div>
    <div class="bill-to-name">{{ $invoice->client->business_name }}</div>
    @if ($invoice->client->owner_name)
        <div class="muted">{{ $invoice->client->owner_name }}</div>
    @endif
    @if ($invoice->client->business_address)
        <div class="muted">{{ $invoice->client->business_address }}</div>
    @endif
    @if ($invoice->client->owner_contact)
        <div class="muted" style="margin-top: 6px;">{{ $invoice->client->owner_contact }}</div>
    @endif
    @if ($invoice->client->tax_id)
        <div class="muted">{{ $invoice->clientTaxIdLabel() }}: {{ $invoice->client->tax_id }}</div>
    @endif

    <table class="items-table">
        <thead>
            <tr><th>Description</th><th class="amount">Amount</th></tr>
        </thead>
        <tbody>
            @foreach (($invoice->line_items ?: [['description' => 'Services rendered', 'amount' => $invoice->amount]]) as $item)
                <tr>
                    <td>
                        {{ $item['description'] ?? 'Item' }}
                        @if (! empty($item['note']))
                            <div class="item-desc-sub">{{ $item['note'] }}</div>
                        @endif
                    </td>
                    <td class="amount">{{ $invoice->money($item['amount'] ?? 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr><td class="label">Subtotal</td><td class="value">{{ $invoice->money($invoice->amount) }}</td></tr>
        @if ((float) $invoice->tax_percent > 0)
            <tr><td class="label">{{ $invoice->taxLabel() }} ({{ rtrim(rtrim(number_format((float) $invoice->tax_percent, 2), '0'), '.') }}%)</td><td class="value">{{ $invoice->money($invoice->total_amount - $invoice->amount) }}</td></tr>
        @endif
        <tr class="total-row"><td class="label">Total</td><td class="value">{{ $invoice->money($invoice->total_amount) }} {{ $invoice->currency }}</td></tr>
    </table>

    @if ($invoice->isExport())
        <p class="export-note" style="text-align: right;">{{ $invoice->taxLabel() }}: 0% &mdash; Export of IT services is treated as zero-rated supply (Section 16, IGST Act).</p>
    @endif

    <table class="footer-columns no-border">
        <tr>
            <td>
                <div class="footer-heading">Notes</div>
                <div class="signatory-title">{{ config('company.signatory_title') }}</div>
                <div class="signatory-line"></div>
                <div class="signatory-name">{{ config('company.signatory_name') }}</div>
            </td>
            <td>
                <div class="footer-heading">Terms and Conditions</div>
                <div class="bank-line" style="font-weight: bold;">Bank Details</div>
                <div class="bank-line muted">Account Name: {{ config('company.bank.account_name') }}</div>
                <div class="bank-line muted">Account Number / IBAN: {{ config('company.bank.account_number') }}</div>
                <div class="bank-line muted">SWIFT: {{ config('company.bank.swift') }}, IFSC: {{ config('company.bank.ifsc') }}</div>
                @if (config('company.bank.bank_name'))
                    <div class="bank-line muted">Bank: {{ config('company.bank.bank_name') }}</div>
                @endif
                @if (config('company.bank.bank_address'))
                    <div class="bank-line muted">{{ config('company.bank.bank_address') }}</div>
                @endif
                @if (config('company.phone') || config('company.gstin') || config('company.tax_id'))
                    <div class="bank-line muted" style="margin-top: 6px;">
                        @if (config('company.phone'))Contact Number: {{ config('company.phone') }}@endif
                        @if (config('company.gstin'))
                            @if (config('company.phone')), @endif GSTIN - {{ config('company.gstin') }}
                        @elseif (config('company.tax_id'))
                            @if (config('company.phone')), @endif {{ config('company.tax_id_label') }} - {{ config('company.tax_id') }}
                        @endif
                    </div>
                @endif
                <div class="bank-line muted" style="margin-top: 6px;">
                    {{ $invoice->taxLabel() }}: {{ rtrim(rtrim(number_format((float) $invoice->tax_percent, 2), '0'), '.') }}%
                    @if ($invoice->isExport())
                        (Section 16 of the IGST Act places Export of IT services shall be treated as zero-rated supply.)
                    @endif
                </div>
                @if ($invoice->isExport() && config('company.paypal_email'))
                    <div class="bank-line muted" style="margin-top: 6px;">PayPal email: {{ config('company.paypal_email') }}</div>
                    @if (config('company.merchant_id'))
                        <div class="bank-line muted">Merchant ID: {{ config('company.merchant_id') }}</div>
                    @endif
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
