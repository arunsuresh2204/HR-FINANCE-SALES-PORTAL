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
                            <td>{{ \App\Support\Currency::format($p->deductions, 'INR') }}</td>
                            <td class="font-semibold text-white">{{ \App\Support\Currency::format($p->net_salary, 'INR') }}</td>
                            <td><x-status-pill :status="$p->status" /></td>
                            <td class="text-right">
                                <div class="flex justify-end items-center gap-3">
                                    <button wire:click="openEditForm({{ $p->id }})" class="text-xs font-semibold text-white/50 hover:text-white">Edit</button>
                                    @if ($p->status === 'draft')
                                        <button wire:click="process({{ $p->id }})" class="rounded-lg bg-sky-400/15 px-2.5 py-1 text-xs font-semibold text-sky-300 hover:bg-sky-400/25">Generate Payslip</button>
                                    @elseif ($p->status === 'processed')
                                        <a href="{{ Storage::url($p->payslip_file) }}" target="_blank" class="rounded-lg bg-white/10 px-2.5 py-1 text-xs font-semibold text-white/70 hover:bg-white/15">View</a>
                                        <button wire:click="markPaid({{ $p->id }})" class="rounded-lg bg-emerald-400/15 px-2.5 py-1 text-xs font-semibold text-emerald-300 hover:bg-emerald-400/25">Mark Paid</button>
                                    @else
                                        <a href="{{ Storage::url($p->payslip_file) }}" target="_blank" class="text-xs font-semibold text-gold-300 hover:text-gold-200">View Payslip</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-white/40">No payroll generated for this period yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-modal-glass wire-model="showEditForm" title="Edit Payslip Components" max-width="xl">
        <form wire:submit="saveEdit" class="space-y-5">
            <div>
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-white/40">Earnings</p>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div>
                        <x-input-label for="basic_salary" value="Basic Salary" class="flex min-h-[2rem] items-end" />
                        <x-text-input wire:model.live="basic_salary" id="basic_salary" type="number" step="0.01" class="mt-0" />
                        <x-input-error :messages="$errors->get('basic_salary')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="hra" value="House Rent Allowance" class="flex min-h-[2rem] items-end" />
                        <x-text-input wire:model.live="hra" id="hra" type="number" step="0.01" class="mt-0" />
                        <x-input-error :messages="$errors->get('hra')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="da" value="Dearness Allowance" class="flex min-h-[2rem] items-end" />
                        <x-text-input wire:model.live="da" id="da" type="number" step="0.01" class="mt-0" />
                        <x-input-error :messages="$errors->get('da')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="other_allowances" value="Other Allowances" class="flex min-h-[2rem] items-end" />
                        <x-text-input wire:model.live="other_allowances" id="other_allowances" type="number" step="0.01" class="mt-0" />
                        <x-input-error :messages="$errors->get('other_allowances')" class="mt-1" />
                    </div>
                </div>
            </div>

            <div>
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-white/40">Deductions</p>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div>
                        <x-input-label for="income_tax" value="Income Tax / TDS" class="flex min-h-[2rem] items-end" />
                        <x-text-input wire:model.live="income_tax" id="income_tax" type="number" step="0.01" class="mt-0" />
                        <x-input-error :messages="$errors->get('income_tax')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="provident_fund" value="Provident Fund" class="flex min-h-[2rem] items-end" />
                        <x-text-input wire:model.live="provident_fund" id="provident_fund" type="number" step="0.01" class="mt-0" />
                        <x-input-error :messages="$errors->get('provident_fund')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="loss_of_pay" value="Loss of Pay" class="flex min-h-[2rem] items-end" />
                        <x-text-input wire:model.live="loss_of_pay" id="loss_of_pay" type="number" step="0.01" class="mt-0" />
                        <x-input-error :messages="$errors->get('loss_of_pay')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="other_deductions" value="Other Deductions" class="flex min-h-[2rem] items-end" />
                        <x-text-input wire:model.live="other_deductions" id="other_deductions" type="number" step="0.01" class="mt-0" />
                        <x-input-error :messages="$errors->get('other_deductions')" class="mt-1" />
                    </div>
                </div>
            </div>

            <div class="max-w-[10rem]">
                <x-input-label for="lop_days" value="LOP Days" />
                <x-text-input wire:model.live="lop_days" id="lop_days" type="number" step="1" min="0" class="mt-0" />
                <x-input-error :messages="$errors->get('lop_days')" class="mt-1" />
            </div>

            @php
                $previewGross = (float) $basic_salary + (float) $hra + (float) $da + (float) $other_allowances;
                $previewDeductions = (float) $income_tax + (float) $provident_fund + (float) $loss_of_pay + (float) $other_deductions;
                $previewNet = $previewGross - $previewDeductions;
            @endphp
            <div class="grid grid-cols-3 gap-4 rounded-xl border border-white/10 bg-white/5 p-4 text-center">
                <div>
                    <p class="text-xs text-white/40">Gross Earnings</p>
                    <p class="mt-1 text-sm font-bold text-white">{{ \App\Support\Currency::format($previewGross, 'INR') }}</p>
                </div>
                <div>
                    <p class="text-xs text-white/40">Total Deductions</p>
                    <p class="mt-1 text-sm font-bold text-white">{{ \App\Support\Currency::format($previewDeductions, 'INR') }}</p>
                </div>
                <div>
                    <p class="text-xs text-white/40">Net Pay</p>
                    <p class="mt-1 text-sm font-bold text-gold-300">{{ \App\Support\Currency::format($previewNet, 'INR') }}</p>
                </div>
            </div>

            @if ($editingHasPayslip)
                <p class="text-xs text-white/40">This payslip was already generated &mdash; saving will resubmit it with the updated figures.</p>
            @endif
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>{{ $editingHasPayslip ? 'Save & Resubmit Payslip' : 'Save Components' }}</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
