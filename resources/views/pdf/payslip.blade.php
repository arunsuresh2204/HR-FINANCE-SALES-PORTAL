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
        .logo-badge { width: 36px; height: 36px; }
        .company-name { font-size: 15px; font-weight: bold; margin-top: 6px; }
        .payslip-title { text-align: right; color: #666; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        .payslip-period { text-align: right; font-size: 15px; font-weight: bold; margin-top: 2px; }

        .divider { border-bottom: 2px solid #111; margin: 14px 0 18px; }

        .summary-heading { color: #666; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px; margin-bottom: 8px; }
        .summary-row td { padding: 2px 0; }
        .summary-label { color: #666; width: 40%; }
        .summary-value { font-weight: bold; }

        .netpay-box { border: 1px solid #b7e4c7; background: #eefaf1; border-radius: 4px; padding: 12px 16px; border-left: 4px solid #1e9e5a; }
        .netpay-label { color: #666; font-size: 10px; margin-top: 2px; }
        .netpay-value { font-size: 19px; font-weight: bold; color: #14532d; }
        .netpay-divider { border-bottom: 1px dashed #b7e4c7; margin: 10px 0; }
        .netpay-meta td { padding: 2px 0; font-size: 10px; }
        .netpay-meta .label { color: #666; }
        .netpay-meta .value { text-align: right; font-weight: bold; }

        .components-table { margin-top: 22px; }
        .components-table th { text-align: left; padding: 6px 8px; border-bottom: 1px solid #999; font-size: 10px; text-transform: uppercase; color: #444; }
        .components-table th.amount, .components-table td.amount { text-align: right; }
        .components-table td { padding: 7px 8px; border-bottom: 1px solid #eee; }
        .components-table .total-row td { font-weight: bold; background: #f7f7f7; border-top: 1px solid #ccc; }
        .col-divider { width: 16px; }

        .net-payable-box { margin-top: 20px; border: 1px solid #ddd; border-radius: 4px; }
        .net-payable-box td { padding: 12px 16px; vertical-align: middle; }
        .net-payable-formula { color: #666; font-size: 10px; margin-top: 2px; }
        .net-payable-amount-cell { background: #eefaf1; text-align: right; font-size: 17px; font-weight: bold; color: #14532d; width: 200px; }

        .amount-words { margin-top: 16px; text-align: right; font-size: 10px; color: #444; }
        .amount-words strong { color: #1a1a1a; }

        .footer-note { margin-top: 50px; text-align: center; color: #999; font-size: 10px; border-top: 1px solid #eee; padding-top: 12px; }
    </style>
</head>
<body>
    <table class="letterhead no-border">
        <tr>
            <td style="width: 60%;">
                <img class="logo-badge" src="{{ public_path('images/logo-mark.png') }}">
                <div class="company-name">{{ config('company.brand_name') }}</div>
                @foreach (config('company.address_lines') as $line)
                    <div class="muted">{{ $line }}</div>
                @endforeach
            </td>
            <td style="width: 40%;">
                <div class="payslip-title">Payslip for the Month</div>
                <div class="payslip-period">{{ \Carbon\Carbon::create($payroll->year, $payroll->month, 1)->format('F Y') }}</div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <table class="no-border">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div class="summary-heading">Employee Summary</div>
                <table class="no-border summary-row">
                    <tr><td class="summary-label">Employee Name</td><td class="summary-value">{{ $payroll->user->name }}</td></tr>
                    <tr><td class="summary-label">Employee ID</td><td class="summary-value">{{ $payroll->user->employee_code }}</td></tr>
                    <tr><td class="summary-label">Designation</td><td class="summary-value">{{ $payroll->user->designation ?? '—' }}</td></tr>
                    <tr><td class="summary-label">Pay Period</td><td class="summary-value">{{ \Carbon\Carbon::create($payroll->year, $payroll->month, 1)->format('F Y') }}</td></tr>
                    <tr><td class="summary-label">Pay Date</td><td class="summary-value">{{ $payroll->updated_at->format('d/m/Y') }}</td></tr>
                </table>
            </td>
            <td style="width: 45%; vertical-align: top;">
                <div class="netpay-box">
                    <div class="netpay-value">{{ \App\Support\Currency::format($payroll->net_salary, 'INR') }}</div>
                    <div class="netpay-label">Total Net Pay</div>
                    <div class="netpay-divider"></div>
                    <table class="no-border netpay-meta">
                        <tr><td class="label">Paid Days</td><td class="value">{{ $payroll->paidDays() }}</td></tr>
                        <tr><td class="label">LOP Days</td><td class="value">{{ $payroll->lop_days }}</td></tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="no-border">
        <tr>
            <td style="width: 48%; vertical-align: top;">
                <table class="components-table">
                    <thead><tr><th>Earnings</th><th class="amount">Amount</th></tr></thead>
                    <tbody>
                        <tr><td>Basic Salary</td><td class="amount">{{ \App\Support\Currency::format($payroll->basic_salary, 'INR') }}</td></tr>
                        <tr><td>House Rent Allowance</td><td class="amount">{{ \App\Support\Currency::format($payroll->hra, 'INR') }}</td></tr>
                        <tr><td>Dearness Allowance</td><td class="amount">{{ \App\Support\Currency::format($payroll->da, 'INR') }}</td></tr>
                        @if ($payroll->other_allowances > 0)
                            <tr><td>Other Allowances</td><td class="amount">{{ \App\Support\Currency::format($payroll->other_allowances, 'INR') }}</td></tr>
                        @endif
                        <tr class="total-row"><td>Gross Earnings</td><td class="amount">{{ \App\Support\Currency::format($payroll->gross_salary, 'INR') }}</td></tr>
                    </tbody>
                </table>
            </td>
            <td class="col-divider"></td>
            <td style="width: 48%; vertical-align: top;">
                <table class="components-table">
                    <thead><tr><th>Deductions</th><th class="amount">Amount</th></tr></thead>
                    <tbody>
                        <tr><td>Income Tax / TDS</td><td class="amount">{{ \App\Support\Currency::format($payroll->income_tax, 'INR') }}</td></tr>
                        <tr><td>Provident Fund</td><td class="amount">{{ \App\Support\Currency::format($payroll->provident_fund, 'INR') }}</td></tr>
                        <tr><td>Loss of Pay</td><td class="amount">{{ \App\Support\Currency::format($payroll->loss_of_pay, 'INR') }}</td></tr>
                        @if ($payroll->other_deductions > 0)
                            <tr><td>Other Deductions</td><td class="amount">{{ \App\Support\Currency::format($payroll->other_deductions, 'INR') }}</td></tr>
                        @endif
                        <tr class="total-row"><td>Total Deductions</td><td class="amount">{{ \App\Support\Currency::format($payroll->deductions, 'INR') }}</td></tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <table class="net-payable-box no-border">
        <tr>
            <td>
                <div style="font-weight: bold; text-transform: uppercase; font-size: 11px;">Total Net Payable</div>
                <div class="net-payable-formula">Gross Earnings &minus; Total Deductions</div>
            </td>
            <td class="net-payable-amount-cell">{{ \App\Support\Currency::format($payroll->net_salary, 'INR') }}</td>
        </tr>
    </table>

    <div class="amount-words">Amount In Words: <strong>{{ $payroll->amountInWords() }}</strong></div>

    <div class="footer-note">This is a system-generated document and does not require a signature.</div>
</body>
</html>
