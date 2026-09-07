<div>
    <x-page-header title="Payslips" subtitle="View and download your payment history." />

    <div class="glass-panel relative overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>Period</th><th>Gross</th><th>Deductions</th><th>Net Pay</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payrolls as $p)
                        <tr>
                            <td class="font-medium text-white">{{ \Carbon\Carbon::create($p->year, $p->month, 1)->format('F Y') }}</td>
                            <td>${{ number_format($p->gross_salary, 2) }}</td>
                            <td>${{ number_format($p->deductions, 2) }}</td>
                            <td class="font-semibold text-white">${{ number_format($p->net_salary, 2) }}</td>
                            <td><x-status-pill :status="$p->status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-white/40">No payslips yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $payrolls->links() }}</div>
    </div>
</div>
