<div>
    <x-page-header title="Financial Reports" subtitle="Revenue, expenses and reconciliation overview." />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Revenue This Month" :value="\App\Support\Currency::format($revenueThisMonth, 'INR')" icon="cash" accent="emerald" />
        <x-stat-card label="Expenses This Month" :value="\App\Support\Currency::format($expensesThisMonth, 'INR')" icon="receipt" accent="rose" />
        <x-stat-card label="Payroll This Month" :value="\App\Support\Currency::format($payrollThisMonth, 'INR')" icon="wallet" accent="violet" />
        <x-stat-card label="Net (P&L) This Month" :value="\App\Support\Currency::format($netThisMonth, 'INR')" icon="chart" :accent="$netThisMonth >= 0 ? 'emerald' : 'rose'" />
    </div>

    <div class="glass-card mt-6">
        <h2 class="mb-4 text-base font-bold text-white">Recent Months</h2>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
            @foreach ($recentMonths as $m)
                <a href="{{ route('finance.reports.month', ['year' => $m['year'], 'month' => $m['month']]) }}" wire:navigate class="glass-inset flex items-center justify-between p-4 transition hover:bg-white/[0.06]">
                    <div>
                        <p class="text-sm font-semibold text-white">{{ $m['label'] }}</p>
                        <p class="mt-1 text-xs text-white/40">Revenue: {{ \App\Support\Currency::format($m['revenue'], 'INR') }}</p>
                        <p class="text-xs {{ $m['net'] >= 0 ? 'text-emerald-300' : 'text-rose-300' }}">Net: {{ \App\Support\Currency::format($m['net'], 'INR') }}</p>
                    </div>
                    <x-icon name="arrow-right" class="h-4 w-4 shrink-0 text-white/30" />
                </a>
            @endforeach
        </div>
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
                    <p class="text-sm text-white/40">No revenue recorded yet.</p>
                @endforelse
            </div>
        </div>

        <div class="glass-card">
            <h2 class="mb-4 text-base font-bold text-white">Outstanding Invoices (Aging)</h2>
            <div class="space-y-3">
                @forelse ($agingInvoices as $invoice)
                    <div class="flex items-center justify-between text-sm">
                        <div>
                            <span class="text-white/70">{{ $invoice->client->business_name }}</span>
                            <span class="ml-2 text-xs text-white/30">{{ $invoice->due_date?->diffForHumans() }}</span>
                        </div>
                        <span class="font-semibold text-gold-300">{{ \App\Support\Currency::format($invoice->balanceDue(), 'INR') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-white/40">No outstanding invoices.</p>
                @endforelse
            </div>
            @if ($agingInvoices->isNotEmpty())
                <div class="mt-4 flex justify-between border-t border-white/10 pt-3 text-sm font-bold text-white">
                    <span>Total Outstanding</span><span>{{ \App\Support\Currency::format($totalOutstanding, 'INR') }}</span>
                </div>
            @endif
        </div>

        <div class="glass-card lg:col-span-2">
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
    </div>
</div>
