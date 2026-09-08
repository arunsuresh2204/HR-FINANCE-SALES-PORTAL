<div>
    <x-page-header title="Payroll Run" subtitle="Process monthly payroll and generate payslips.">
        <x-slot:actions>
            <select wire:model.live="month" class="input-glass w-36">
                @foreach (range(1, 12) as $m)
                    <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}</option>
                @endforeach
            </select>
            <select wire:model.live="year" class="input-glass w-28">
                @foreach (range(now()->year - 1, now()->year + 1) as $y)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endforeach
            </select>
            <button wire:click="generatePayroll" class="btn-glass-primary"><x-icon name="wallet" class="h-4 w-4" /> Generate Payroll</button>
        </x-slot:actions>
    </x-page-header>

    <div class="glass-panel relative overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead><tr><th>Employee</th><th>Gross</th><th>Deductions</th><th>Net</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($payrolls as $p)
                        <tr id="payslip-{{ $p->id }}">
                            <td class="font-medium text-white">{{ $p->user->name }}</td>
                            <td>{{ \App\Support\Currency::format($p->gross_salary, 'INR') }}</td>
                            <td>
                                @if ($p->status === 'draft')
                                    <input type="number" step="0.01" value="{{ $p->deductions }}" wire:change="updateDeduction({{ $p->id }}, $event.target.value)" class="input-glass w-28 py-1">
                                @else
                                    {{ \App\Support\Currency::format($p->deductions, 'INR') }}
                                @endif
                            </td>
                            <td class="font-semibold text-white">{{ \App\Support\Currency::format($p->net_salary, 'INR') }}</td>
                            <td><x-status-pill :status="$p->status" /></td>
                            <td class="text-right">
                                @if ($p->status === 'draft')
                                    <button wire:click="process({{ $p->id }})" class="rounded-lg bg-sky-400/15 px-2.5 py-1 text-xs font-semibold text-sky-300 hover:bg-sky-400/25">Generate Payslip</button>
                                @elseif ($p->status === 'processed')
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ Storage::url($p->payslip_file) }}" target="_blank" class="rounded-lg bg-white/10 px-2.5 py-1 text-xs font-semibold text-white/70 hover:bg-white/15">View</a>
                                        <button wire:click="markPaid({{ $p->id }})" class="rounded-lg bg-emerald-400/15 px-2.5 py-1 text-xs font-semibold text-emerald-300 hover:bg-emerald-400/25">Mark Paid</button>
                                    </div>
                                @else
                                    <a href="{{ Storage::url($p->payslip_file) }}" target="_blank" class="text-xs font-semibold text-gold-300 hover:text-gold-200">View Payslip</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-white/40">No payroll generated for this period yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
