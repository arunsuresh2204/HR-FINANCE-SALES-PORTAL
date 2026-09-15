<div>
    <x-page-header title="Sales Performance" :subtitle="$isTeamView ? 'Team target attainment for '.$period->format('F Y').'.' : 'Your target attainment and monthly achievements.'">
        <x-slot:actions>
            <div class="flex items-center gap-1.5">
                <button type="button" wire:click="shiftAnchor(-5)" class="glass rounded-lg p-1.5 text-white/50 hover:text-white" title="Previous 5 months"><x-icon name="arrow-right" class="h-3.5 w-3.5 rotate-180" /></button>
                <input wire:model.live="monthPicker" type="month" class="input-glass w-40" title="Jump to a month">
                <button type="button" wire:click="shiftAnchor(5)" class="glass rounded-lg p-1.5 text-white/50 hover:text-white" title="Next 5 months"><x-icon name="arrow-right" class="h-3.5 w-3.5" /></button>
            </div>
        </x-slot:actions>
    </x-page-header>

    @if ($isTeamView)
        @php
            $selfRow = $members->firstWhere('isSelf', true);
            $attainColor = fn ($pct) => $pct >= 100 ? 'emerald' : ($pct >= 60 ? 'gold' : 'rose');
        @endphp

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-[1.7fr_1fr]">
            <a href="{{ route('sales.targets.team-stats', [$period->year, $period->month]) }}" wire:navigate
               class="glass-card-hover block border border-white/10 hover:border-gold-400/30">
                <div class="flex items-start justify-between gap-3">
                    <p class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-widest text-gold-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-gold-400 shadow-[0_0_8px_1px_rgba(247,200,30,0.7)]"></span>
                        Team Target &middot; {{ $period->format('F') }}
                    </p>
                    <span class="rounded-full border px-2.5 py-1 text-xs font-bold
                        {{ $attainColor($teamAttainment) === 'emerald' ? 'border-emerald-400/25 bg-emerald-400/15 text-emerald-300' : ($attainColor($teamAttainment) === 'gold' ? 'border-gold-400/25 bg-gold-400/15 text-gold-300' : 'border-rose-400/25 bg-rose-400/15 text-rose-300') }}">
                        {{ $teamAttainment }}% attained
                    </span>
                </div>
                <div class="mt-3 flex flex-wrap items-baseline gap-2">
                    <span class="text-4xl font-extrabold text-white">{{ \App\Support\Currency::format($teamAchieved, 'INR') }}</span>
                    <span class="text-base font-medium text-white/45">of {{ \App\Support\Currency::format($teamTarget, 'INR') }} team target</span>
                </div>
                <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-white/5">
                    <div class="h-full rounded-full bg-gradient-to-r from-gold-500 via-gold-400 to-gold-200 shadow-[0_0_14px_0_rgba(247,200,30,0.45)]" style="width: {{ min(100, $teamAttainment) }}%"></div>
                </div>
                <div class="mt-3 flex items-center justify-between text-xs text-white/45">
                    <span>{{ $members->count() }} team member{{ $members->count() === 1 ? '' : 's' }} &middot; incl. your own quota</span>
                    <span class="{{ $attainmentDelta >= 0 ? 'text-emerald-300' : 'text-rose-300' }} font-semibold">{{ $attainmentDelta >= 0 ? '+' : '' }}{{ $attainmentDelta }} pts <span class="text-white/45 font-normal">vs last month</span></span>
                </div>
                <div class="mt-4 flex items-center gap-1.5 text-xs font-bold text-gold-300">
                    View full breakdown &amp; pending
                    <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                </div>
            </a>

            <div class="glass-card">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold uppercase tracking-widest text-white/40">Your Personal Quota</p>
                    <span class="badge-glass !bg-gold-400/15 !text-gold-300 !border-gold-400/20">You</span>
                </div>
                @if ($selfRow)
                    <p class="mt-3.5 text-2xl font-extrabold text-white">{{ \App\Support\Currency::format($selfRow['achieved'], 'INR') }} <span class="text-sm font-medium text-white/40">/ {{ \App\Support\Currency::format($selfRow['target'], 'INR') }}</span></p>
                    <div class="mt-3.5 h-1.5 overflow-hidden rounded-full bg-white/5">
                        <div class="h-full rounded-full bg-gradient-to-r from-emerald-600 to-emerald-400" style="width: {{ min(100, $selfRow['attainment']) }}%"></div>
                    </div>
                    <p class="mt-2.5 text-xs text-white/45">{{ $selfRow['attainment'] }}% attained &middot; {{ $selfRow['clientsAcquired'] }} client{{ $selfRow['clientsAcquired'] === 1 ? '' : 's' }} acquired</p>
                @else
                    <p class="mt-3.5 text-sm text-white/40">No personal quota this month.</p>
                @endif
                <button wire:click="openTargetForm({{ $selfRow['user']->id }}, {{ $period->month }}, {{ $period->year }})" class="btn-glass-secondary mt-4 w-full !py-2 !text-xs">{{ $selfRow && $selfRow['hasTarget'] ? 'Edit My Target' : 'Set My Target' }}</button>
            </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <x-stat-card label="Team Target" :value="\App\Support\Currency::format($teamTarget, 'INR')" icon="target" accent="gold" />
            <x-stat-card label="Team Achieved" :value="\App\Support\Currency::format($teamAchieved, 'INR')" icon="cash" accent="emerald" />
            <x-stat-card label="Pending This Month" :value="\App\Support\Currency::format($teamPending, 'INR')" icon="clock" accent="sky" />
            <x-stat-card label="Clients Acquired" :value="$teamClientsAcquired" icon="briefcase" accent="violet" />
        </div>

        <div x-data="{ q: '' }" class="mt-6">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-sm font-bold text-white">Team Members</h2>
                <div class="relative">
                    <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-white/30" />
                    <input x-model="q" type="text" placeholder="Find a teammate&hellip;" class="input-glass !w-56 !py-2 pl-9 text-xs">
                </div>
            </div>

            <div class="space-y-2.5">
                @foreach ($members as $i => $m)
                    <div x-show="q === '' || '{{ strtolower($m['user']->name) }}'.includes(q.toLowerCase())"
                         class="glass-card-hover flex flex-col gap-3 !p-4 sm:flex-row sm:items-center">
                        <a href="{{ route('sales.targets.report', [$m['user'], $period->year, $period->month]) }}" wire:navigate class="flex flex-1 items-center gap-4 min-w-0">
                            <span class="flex h-6 w-5 shrink-0 items-center justify-center text-[10px] font-bold text-white/30">{{ $i + 1 }}</span>
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gold-400/15 text-xs font-bold text-gold-300">{{ $m['user']->initials() }}</span>
                            <span class="min-w-[130px] shrink-0">
                                <span class="flex items-center gap-2 text-sm font-bold text-white">
                                    {{ $m['user']->name }}
                                    @if ($m['isSelf'])
                                        <span class="badge-glass !bg-gold-400/15 !text-gold-300 !border-gold-400/20 !py-0.5 !text-[10px]">You</span>
                                    @endif
                                </span>
                                <span class="text-xs text-white/40">{{ $m['user']->designation ?? 'Sales' }}</span>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="mb-1 flex justify-between text-xs text-white/45">
                                    <span><b class="font-bold text-white">{{ \App\Support\Currency::format($m['achieved'], 'INR') }}</b> achieved</span>
                                    <span>of {{ \App\Support\Currency::format($m['target'], 'INR') }}</span>
                                </span>
                                <span class="block h-1.5 overflow-hidden rounded-full bg-white/5">
                                    <span class="block h-full rounded-full
                                        @if ($m['attainment'] >= 100) bg-gradient-to-r from-emerald-600 to-emerald-400
                                        @elseif ($m['attainment'] >= 60) bg-gradient-to-r from-gold-600 to-gold-400
                                        @else bg-gradient-to-r from-rose-500 to-gold-400
                                        @endif"
                                        style="width: {{ min(100, $m['attainment']) }}%"></span>
                                </span>
                            </span>
                            <span class="w-14 shrink-0 text-right text-lg font-extrabold {{ $m['attainment'] >= 100 ? 'text-emerald-300' : ($m['attainment'] >= 60 ? 'text-gold-300' : 'text-rose-300') }}">{{ $m['attainment'] }}%</span>
                            <x-icon name="arrow-right" class="h-4 w-4 shrink-0 text-white/25" />
                        </a>
                        <button wire:click="openTargetForm({{ $m['user']->id }}, {{ $period->month }}, {{ $period->year }})" class="btn-glass-secondary shrink-0 !px-3 !py-1.5 !text-xs">{{ $m['hasTarget'] ? 'Edit Target' : 'Set Target' }}</button>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        @php($rows = $rows ?? collect())
        <div class="space-y-6">
            @forelse ($rows as $row)
                <div class="glass-card">
                    <div class="mb-4 flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gold-400/15 text-sm font-bold text-gold-300">{{ $row['user']->initials() }}</span>
                        <p class="font-semibold text-white">{{ $row['user']->name }}</p>
                    </div>
                    <div class="space-y-2">
                        @foreach ($row['months'] as $m)
                            <div class="glass-inset flex flex-col gap-3 p-3 sm:flex-row sm:items-center sm:justify-between">
                                <span class="text-left text-sm font-semibold text-white sm:w-36 sm:shrink-0">{{ $m['label'] }}</span>
                                <div class="flex flex-1 items-center gap-4">
                                    @if ($m['target'])
                                        <div class="w-full">
                                            <div class="mb-1 flex justify-between text-xs text-white/50">
                                                <span>{{ \App\Support\Currency::format($m['achieved'], 'INR') }}</span>
                                                <span>{{ \App\Support\Currency::format($m['effectiveTarget'], 'INR') }}</span>
                                            </div>
                                            <div class="h-2 rounded-full bg-white/5">
                                                <div class="h-2 rounded-full bg-gradient-to-r from-gold-500 to-gold-300" style="width: {{ min(100, $m['effectiveTarget'] > 0 ? $m['achieved'] / $m['effectiveTarget'] * 100 : 0) }}%"></div>
                                            </div>
                                            @if ($m['deficitCarried'] > 0)
                                                <p class="mt-1 text-[11px] text-rose-300/80">Includes {{ \App\Support\Currency::format($m['deficitCarried'], 'INR') }} carried from last month's shortfall</p>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-xs text-white/40">No target set for this period</span>
                                    @endif
                                </div>
                                <div class="flex shrink-0 items-center gap-3">
                                    <span class="text-xs text-white/40">{{ $m['clientsAcquired'] }} client{{ $m['clientsAcquired'] === 1 ? '' : 's' }} acquired</span>
                                    <a href="{{ route('sales.targets.report', [$row['user'], $m['year'], $m['month']]) }}" wire:navigate class="btn-glass-secondary !px-3 !py-1.5 !text-xs">View Report</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-sm text-white/40">No sales data available.</p>
            @endforelse
        </div>
    @endif

    <x-modal-glass wire-model="showTargetForm" title="Set Monthly Target" max-width="sm">
        <form wire:submit="saveTarget" class="space-y-4">
            <div>
                <x-input-label for="target_amount" value="Target Amount (₹)" />
                <x-text-input wire:model="target_amount" id="target_amount" type="number" step="0.01" class="mt-0" />
                <x-input-error :messages="$errors->get('target_amount')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="commission_percent" value="Commission (%)" />
                <x-text-input wire:model="commission_percent" id="commission_percent" type="number" step="0.01" class="mt-0" />
                <x-input-error :messages="$errors->get('commission_percent')" class="mt-1" />
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" wire:click="$set('showTargetForm', false)">Cancel</x-secondary-button>
                <x-primary-button>Save Target</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
