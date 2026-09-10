<div>
    <x-page-header title="Payslips" subtitle="View and download your payment history." />

    <div class="glass-panel relative overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>Period</th><th>Gross</th><th>Deductions</th><th>Net Pay</th><th>Status</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payrolls as $p)
                        <tr>
                            <td class="font-medium text-white">{{ \Carbon\Carbon::create($p->year, $p->month, 1)->format('F Y') }}</td>
                            <td>{{ \App\Support\Currency::format($p->gross_salary, 'INR') }}</td>
                            <td>{{ \App\Support\Currency::format($p->deductions, 'INR') }}</td>
                            <td class="font-semibold text-white">{{ \App\Support\Currency::format($p->net_salary, 'INR') }}</td>
                            <td><x-status-pill :status="$p->status" /></td>
                            <td class="text-right">
                                @if (in_array($p->status, ['processed', 'paid']) && $p->payslip_file)
                                    <a href="{{ route('payslips.download', $p) }}" class="text-xs font-semibold text-gold-300 hover:text-gold-200">Download</a>
                                @else
                                    <span class="text-xs text-white/30">Not yet generated</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-white/40">No payslips yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $payrolls->links() }}</div>
    </div>
</div>
