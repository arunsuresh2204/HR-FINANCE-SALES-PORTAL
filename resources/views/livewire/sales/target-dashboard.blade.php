<div>
    <x-page-header title="Sales Performance" subtitle="{{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }} target attainment." />

    <div class="space-y-4">
        @forelse ($rows as $row)
            <div class="glass-card">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gold-400/15 text-sm font-bold text-gold-300">{{ $row['user']->initials() }}</span>
                        <div>
                            <p class="font-semibold text-white">{{ $row['user']->name }}</p>
                            <p class="text-xs text-white/40">{{ $row['openLeads'] }} open leads &middot; {{ $row['conversionRate'] }}% conversion</p>
                        </div>
                    </div>

                    @if ($row['target'])
                        <div class="w-full sm:w-64">
                            <div class="mb-1 flex justify-between text-xs text-white/50">
                                <span>${{ number_format($row['achieved']) }}</span>
                                <span>${{ number_format($row['target']->target_amount) }}</span>
                            </div>
                            <div class="h-2.5 rounded-full bg-white/5">
                                <div class="h-2.5 rounded-full bg-gradient-to-r from-gold-500 to-gold-300" style="width: {{ min(100, $row['target']->target_amount > 0 ? $row['achieved'] / $row['target']->target_amount * 100 : 0) }}%"></div>
                            </div>
                        </div>
                    @else
                        <span class="text-xs text-white/40">No target set for this period</span>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-white/40">No sales data available.</p>
        @endforelse
    </div>
</div>
