<div>
    <x-page-header title="Exit Documents" subtitle="Your final payslips and offboarding documents." />

    @if (! $resignation)
        <div class="glass-card">
            <p class="text-sm text-white/40">No exit documents are available yet.</p>
        </div>
    @else
        <div class="glass-card mb-6">
            <h2 class="text-base font-bold text-white">Offboarding Documents</h2>
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="glass-inset flex items-center justify-between gap-2 p-3 text-sm">
                    <span class="text-white/70">Experience Certificate</span>
                    @if ($resignation->experience_certificate_published_at)
                        <a href="{{ route('resignations.experience-certificate', $resignation) }}" target="_blank" class="text-xs font-semibold text-gold-300 hover:text-gold-200">Download</a>
                    @else
                        <span class="text-xs text-white/30">Not yet available</span>
                    @endif
                </div>
                <div class="glass-inset flex items-center justify-between gap-2 p-3 text-sm">
                    <span class="text-white/70">Relieving Letter</span>
                    @if ($resignation->relieving_letter_published_at)
                        <a href="{{ route('resignations.relieving-letter', $resignation) }}" target="_blank" class="text-xs font-semibold text-gold-300 hover:text-gold-200">Download</a>
                    @else
                        <span class="text-xs text-white/30">Not yet available</span>
                    @endif
                </div>
                <div class="glass-inset flex items-center justify-between gap-2 p-3 text-sm">
                    <span class="text-white/70">Form 16 <span class="text-white/35">(if applicable)</span></span>
                    @if ($resignation->form16_path)
                        <a href="{{ Storage::url($resignation->form16_path) }}" target="_blank" class="text-xs font-semibold text-gold-300 hover:text-gold-200">Download</a>
                    @else
                        <span class="text-xs text-white/30">Not available</span>
                    @endif
                </div>
                <div class="glass-inset flex items-center justify-between gap-2 p-3 text-sm">
                    <span class="text-white/70">UAN / PF Document <span class="text-white/35">(if applicable)</span></span>
                    @if ($resignation->uan_pf_path)
                        <a href="{{ Storage::url($resignation->uan_pf_path) }}" target="_blank" class="text-xs font-semibold text-gold-300 hover:text-gold-200">Download</a>
                    @else
                        <span class="text-xs text-white/30">Not available</span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="glass-panel relative overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="p-4 pb-0">
            <h2 class="text-base font-bold text-white">Last 6 Payslips</h2>
        </div>
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
                                    <span class="text-xs text-white/30">Not available</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-white/40">No payslips on file.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
