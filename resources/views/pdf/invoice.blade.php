<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Helvetica, Arial, sans-serif; color: #1a1a1a; font-size: 12px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #111; padding-bottom: 16px; margin-bottom: 24px; }
        .brand { font-size: 20px; font-weight: bold; }
        .brand .accent { color: #f0bb0b; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #ddd; }
        th { background: #111; color: #fff; font-size: 11px; text-transform: uppercase; }
        .totals { width: 300px; margin-left: auto; margin-top: 16px; }
        .totals td { border: none; padding: 4px 8px; }
        .totals .grand { font-size: 15px; font-weight: bold; border-top: 2px solid #111; }
        .status { display: inline-block; padding: 4px 10px; border-radius: 12px; background: #111; color: #f0bb0b; font-size: 11px; font-weight: bold; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">nexstarc<span class="accent">.</span> <span style="font-size:11px; font-weight: normal; color:#888;">technologies</span></div>
        <div style="text-align:right;">
            <div style="font-size: 18px; font-weight: bold;">INVOICE</div>
            <div class="muted">{{ $invoice->invoice_number }}</div>
        </div>
    </div>

    <table style="border: none;">
        <tr style="border: none;">
            <td style="border: none; width: 50%; vertical-align: top;">
                <div class="muted">Billed To</div>
                <div style="font-weight: bold; font-size: 14px;">{{ $invoice->client->business_name }}</div>
                <div class="muted">{{ $invoice->client->business_address }}</div>
                <div class="muted">{{ $invoice->client->owner_name }}</div>
            </td>
            <td style="border: none; width: 50%; vertical-align: top; text-align: right;">
                <div class="muted">Issue Date: {{ $invoice->created_at->format('M j, Y') }}</div>
                <div class="muted">Due Date: {{ $invoice->due_date?->format('M j, Y') ?? '—' }}</div>
                <div style="margin-top: 6px;"><span class="status">{{ str_replace('_', ' ', $invoice->status) }}</span></div>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr><th>Description</th><th style="text-align:right;">Amount</th></tr>
        </thead>
        <tbody>
            @foreach (($invoice->line_items ?: [['description' => 'Services rendered', 'amount' => $invoice->amount]]) as $item)
                <tr>
                    <td>{{ $item['description'] ?? 'Item' }}</td>
                    <td style="text-align:right;">${{ number_format($item['amount'] ?? 0, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td style="text-align:right;">${{ number_format($invoice->amount, 2) }}</td></tr>
        <tr><td>Tax ({{ $invoice->tax_percent }}%)</td><td style="text-align:right;">${{ number_format($invoice->total_amount - $invoice->amount, 2) }}</td></tr>
        <tr class="grand"><td>Total</td><td style="text-align:right;">${{ number_format($invoice->total_amount, 2) }}</td></tr>
        <tr><td>Amount Paid</td><td style="text-align:right;">${{ number_format($invoice->amount_paid, 2) }}</td></tr>
        <tr><td>Balance Due</td><td style="text-align:right;">${{ number_format($invoice->balanceDue(), 2) }}</td></tr>
    </table>

    <div style="margin-top: 60px; text-align: center; color: #999; font-size: 10px;">
        Nexstarc Technologies &middot; Thank you for your business.
    </div>
</body>
</html>
