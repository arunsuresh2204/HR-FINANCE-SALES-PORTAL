<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; color: #1a1a1a; font-size: 12px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #111; padding-bottom: 16px; margin-bottom: 24px; }
        .brand { font-size: 20px; font-weight: bold; }
        .brand .accent { color: #f0bb0b; }
        .muted { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #ddd; }
        th { background: #111; color: #fff; font-size: 11px; text-transform: uppercase; }
        .grand { font-size: 15px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">nexstarc<span class="accent">.</span> <span style="font-size:11px; font-weight: normal; color:#888;">technologies</span></div>
        <div style="text-align:right;">
            <div style="font-size: 18px; font-weight: bold;">PAYSLIP</div>
            <div class="muted">{{ \Carbon\Carbon::create($payroll->year, $payroll->month, 1)->format('F Y') }}</div>
        </div>
    </div>

    <table style="border: none;">
        <tr style="border: none;">
            <td style="border: none; width: 50%; vertical-align: top;">
                <div class="muted">Employee</div>
                <div style="font-weight: bold; font-size: 14px;">{{ $payroll->user->name }}</div>
                <div class="muted">{{ $payroll->user->employee_code }} &middot; {{ $payroll->user->designation }}</div>
            </td>
        </tr>
    </table>

    <table>
        <thead><tr><th>Description</th><th style="text-align:right;">Amount</th></tr></thead>
        <tbody>
            <tr><td>Gross Salary</td><td style="text-align:right;">{{ \App\Support\Currency::format($payroll->gross_salary, 'INR') }}</td></tr>
            <tr><td>Deductions</td><td style="text-align:right;">-{{ \App\Support\Currency::format($payroll->deductions, 'INR') }}</td></tr>
            <tr class="grand"><td>Net Pay</td><td style="text-align:right;">{{ \App\Support\Currency::format($payroll->net_salary, 'INR') }}</td></tr>
        </tbody>
    </table>

    <div style="margin-top: 60px; text-align: center; color: #999; font-size: 10px;">
        Nexstarc Technologies &middot; This is a computer-generated payslip.
    </div>
</body>
</html>
