<div>
    <x-page-header :title="$period->format('F Y').' Financial Report'" subtitle="Detailed revenue, expenses and sales performance for the period.">
        <x-slot:actions>
            <a href="{{ route('finance.reports') }}" wire:navigate class="btn-glass-secondary"><x-icon name="arrow-right" class="h-4 w-4 rotate-180" /> Back to Reports</a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Revenue" :value="\App\Support\Currency::format($revenue, 'INR')" icon="cash" accent="emerald" />
        <x-stat-card label="Expenses" :value="\App\Support\Currency::format($expenses, 'INR')" icon="receipt" accent="rose" />
        <x-stat-card label="Payroll" :value="\App\Support\Currency::format($payroll, 'INR')" icon="wallet" accent="violet" />
        <x-stat-card label="Net (P&L)" :value="\App\Support\Currency::format($net, 'INR')" icon="chart" :accent="$net >= 0 ? 'emerald' : 'rose'" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="glass-card">
            <h2 class="mb-4 text-base font-bold text-white">Revenue by Client</h2>
            <div class="space-y-3">
                @forelse ($revenueByClient as $client)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-white/70">{{ $client->business_name }}</span>
                        <span class="font-semibold text-white">{{ \App\Support\Currency::format($client->revenue, 'INR') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-white/40">No revenue recorded this month.</p>
                @endforelse
            </div>
        </div>

        <div class="glass-card">
            <h2 class="mb-4 text-base font-bold text-white">Sales-to-Revenue Reconciliation</h2>
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <p class="text-xs text-white/40">Won Deals Value</p>
                    <p class="mt-1 text-xl font-extrabold text-white">{{ \App\Support\Currency::format($wonDealsValue, 'INR') }}</p>
                </div>
                <div>
                    <p class="text-xs text-white/40">Total Invoiced</p>
                    <p class="mt-1 text-xl font-extrabold text-white">{{ \App\Support\Currency::format($invoicedValue, 'INR') }}</p>
                </div>
                <div>
                    <p class="text-xs text-white/40">Variance</p>
                    <p class="mt-1 text-xl font-extrabold {{ $wonDealsValue - $invoicedValue >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">{{ \App\Support\Currency::format($wonDealsValue - $invoicedValue, 'INR') }}</p>
                </div>
            </div>
        </div>

        <div class="glass-card lg:col-span-2">
            <h2 class="mb-4 text-base font-bold text-white">Sales Achievement by Salesperson</h2>
            <div class="overflow-x-auto">
                <table class="table-glass">
                    <thead>
                        <tr>
                            <th>Salesperson</th>
                            <th>Target</th>
                            <th>Achieved</th>
                            <th>Achievement</th>
                            <th>Deals Won</th>
                            <th>Commission</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($salesAchievements as $row)
                            <tr>
                                <td class="font-medium text-white">{{ $row['user']->name }}</td>
                                <td class="whitespace-nowrap text-white/60">{{ $row['target'] > 0 ? \App\Support\Currency::format($row['target'], 'INR') : '—' }}</td>
                                <td class="whitespace-nowrap font-semibold text-white">{{ \App\Support\Currency::format($row['achieved'], 'INR') }}</td>
                                <td class="whitespace-nowrap">
                                    @if ($row['achievementPct'] !== null)
                                        <span class="badge-glass {{ $row['achievementPct'] >= 100 ? '!border-emerald-400/25 !bg-emerald-400/10 !text-emerald-200' : '' }}">{{ $row['achievementPct'] }}%</span>
                                    @else
                                        <span class="text-white/25">No target set</span>
                                    @endif
                                </td>
                                <td class="text-white/60">{{ $row['dealsWon'] }}</td>
                                <td class="whitespace-nowrap text-white/60">{{ $row['commissionPercent'] > 0 ? \App\Support\Currency::format($row['commissionEarned'], 'INR') : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-8 text-center text-white/40">No sales executives found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="glass-card lg:col-span-2">
            <h2 class="mb-4 text-base font-bold text-white">Invoices Issued This Month</h2>
            <div class="overflow-x-auto">
                <table class="table-glass">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Invoice #</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($invoicesThisMonth as $invoice)
                            <tr>
                                <td class="text-white/70">{{ $invoice->client->business_name }}</td>
                                <td class="text-white/60">{{ $invoice->invoice_number }}</td>
                                <td class="font-semibold text-white">{{ $invoice->money($invoice->total_amount) }}</td>
                                <td class="text-white/60">{{ $invoice->money($invoice->amount_paid) }}</td>
                                <td><x-status-pill :status="$invoice->isOverdue() ? 'overdue' : $invoice->status" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-white/40">No invoices issued this month.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
