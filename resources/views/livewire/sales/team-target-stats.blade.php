@php
    $chartW = 640;
    $chartH = 220;
    $marginL = 48;
    $marginR = 8;
    $marginT = 16;
    $marginB = 28;
    $plotW = $chartW - $marginL - $marginR;
    $plotH = $chartH - $marginT - $marginB;
    $groupW = $plotW / max(1, $trend->count());
    $barW = 24;
    $barGap = 6;

    $maxVal = ($trend->flatMap(fn ($m) => [$m['achieved'], $m['target']])->max() ?: 1000) * 1.15;

    $barTop = fn ($value) => $marginT + ($plotH - ($maxVal > 0 ? ($value / $maxVal) * $plotH : 0));
    $barHeight = fn ($value) => $maxVal > 0 ? ($value / $maxVal) * $plotH : 0;
    $abbr = fn ($n) => $n >= 100000 ? number_format($n / 100000, 1).'L' : ($n >= 1000 ? number_format($n / 1000, 0).'K' : number_format($n));
@endphp

<div>
    <a href="{{ route('sales.targets') }}" wire:navigate class="mb-1 flex w-fit items-center gap-2 text-sm text-white/50 hover:text-gold-300">
        <x-icon name="arrow-right" class="h-3.5 w-3.5 rotate-180" /> Back to Sales Performance
    </a>

    <x-page-header title="Team Target Statistics" :subtitle="'Full breakdown for '.$period->format('F Y')">
        <x-slot:actions>
            <div class="flex items-center gap-1.5">
                <a href="{{ route('sales.targets.team-stats', [$period->copy()->subMonthNoOverflow()->year, $period->copy()->subMonthNoOverflow()->month]) }}" wire:navigate class="glass rounded-lg p-1.5 text-white/50 hover:text-white" title="Previous month"><x-icon name="arrow-right" class="h-3.5 w-3.5 rotate-180" /></a>
                <a href="{{ route('sales.targets.team-stats', [$period->copy()->addMonthNoOverflow()->year, $period->copy()->addMonthNoOverflow()->month]) }}" wire:navigate class="glass rounded-lg p-1.5 text-white/50 hover:text-white" title="Next month"><x-icon name="arrow-right" class="h-3.5 w-3.5" /></a>
            </div>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="glass-card text-center">
            <p class="text-xl font-extrabold text-white">{{ \App\Support\Currency::format($teamTarget, 'INR') }}</p>
            <p class="mt-1 text-[11px] text-white/40">Target</p>
        </div>
        <div class="glass-card text-center">
            <p class="text-xl font-extrabold text-emerald-300">{{ \App\Support\Currency::format($teamAchieved, 'INR') }}</p>
            <p class="mt-1 text-[11px] text-white/40">Achieved</p>
        </div>
        <div class="glass-card text-center">
            <p class="text-xl font-extrabold text-rose-300">{{ \App\Support\Currency::format($teamPending, 'INR') }}</p>
            <p class="mt-1 text-[11px] text-white/40">Pending</p>
        </div>
        <div class="glass-card text-center">
            <p class="text-xl font-extrabold text-gold-300">{{ $teamAttainment }}%</p>
            <p class="mt-1 text-[11px] text-white/40">Attainment</p>
        </div>
    </div>

    <div class="glass-card relative mt-6 overflow-hidden">
        <div class="glass-sheen"></div>
        <h2 class="text-sm font-bold text-white">Team Target vs. Achieved &middot; last 6 months</h2>
        <p class="mt-0.5 text-xs text-white/40">The current month is still in progress.</p>
        <div class="mt-3 flex gap-4 text-[11px] text-white/50">
            <span class="flex items-center gap-1.5"><i class="inline-block h-2.5 w-2.5 rounded-sm bg-white/25"></i>Target</span>
            <span class="flex items-center gap-1.5"><i class="inline-block h-2.5 w-2.5 rounded-sm bg-gold-400"></i>Achieved</span>
        </div>
        <svg viewBox="0 0 {{ $chartW }} {{ $chartH }}" class="mt-2 w-full" preserveAspectRatio="xMidYMid meet" role="img" aria-label="Team target versus achieved for the last six months">
            @for ($g = 0; $g <= 4; $g++)
                @php $gy = $marginT + ($plotH / 4) * $g; @endphp
                <line x1="{{ $marginL }}" y1="{{ $gy }}" x2="{{ $chartW - $marginR }}" y2="{{ $gy }}" stroke="{{ $g === 4 ? 'rgba(255,255,255,0.18)' : 'rgba(255,255,255,0.07)' }}" />
                <text x="{{ $marginL - 6 }}" y="{{ $gy + 3 }}" text-anchor="end" font-size="9" fill="rgba(255,255,255,0.35)">{{ $abbr($maxVal * (4 - $g) / 4) }}</text>
            @endfor

            @foreach ($trend as $i => $m)
                @php
                    $groupX = $marginL + $i * $groupW;
                    $pairW = ($barW * 2) + $barGap;
                    $offset = ($groupW - $pairW) / 2;
                    $targetX = $groupX + $offset;
                    $achievedX = $targetX + $barW + $barGap;
                @endphp
                <rect x="{{ $targetX }}" y="{{ $barTop($m['target']) }}" width="{{ $barW }}" height="{{ $barHeight($m['target']) }}" rx="3" fill="rgba(255,255,255,0.22)" />
                <rect x="{{ $achievedX }}" y="{{ $barTop($m['achieved']) }}" width="{{ $barW }}" height="{{ $barHeight($m['achieved']) }}" rx="3" fill="{{ $m['isCurrent'] ? '#fbe285' : '#f7c81e' }}">
                    <title>{{ $m['label'] }}: {{ \App\Support\Currency::format($m['achieved'], 'INR') }} achieved of {{ \App\Support\Currency::format($m['target'], 'INR') }} target</title>
                </rect>
                <text x="{{ $achievedX + $barW / 2 }}" y="{{ $barTop($m['achieved']) - 5 }}" text-anchor="middle" font-size="9" fill="#fbe285" font-weight="600">{{ $abbr($m['achieved']) }}</text>
                <text x="{{ $groupX + $groupW / 2 }}" y="{{ $marginT + $plotH + 16 }}" text-anchor="middle" font-size="10" fill="{{ $m['isCurrent'] ? '#fbe285' : 'rgba(255,255,255,0.5)' }}" font-weight="{{ $m['isCurrent'] ? '700' : '400' }}">{{ $m['label'] }}</text>
            @endforeach
        </svg>
    </div>

    <div class="mb-3 mt-6 flex items-center justify-between">
        <h2 class="text-sm font-bold text-white">Per-Member Breakdown &middot; {{ $period->format('F Y') }}</h2>
    </div>
    <div class="glass-panel relative overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead>
                    <tr><th>Member</th><th class="text-right">Target</th><th class="text-right">Achieved</th><th class="text-right">Pending</th><th class="text-right">Attainment</th><th class="text-right">Clients</th></tr>
                </thead>
                <tbody>
                    @forelse ($members as $m)
                        <tr onclick="Livewire.navigate('{{ route('sales.targets.report', [$m['user'], $period->year, $period->month]) }}')" class="cursor-pointer">
                            <td class="font-medium text-white">{{ $m['user']->name }}</td>
                            <td class="text-right">{{ \App\Support\Currency::format($m['target'], 'INR') }}</td>
                            <td class="text-right text-emerald-300">{{ \App\Support\Currency::format($m['achieved'], 'INR') }}</td>
                            <td class="text-right">{{ \App\Support\Currency::format($m['pending'], 'INR') }}</td>
                            <td class="text-right font-bold {{ $m['attainment'] >= 100 ? 'text-emerald-300' : ($m['attainment'] >= 60 ? 'text-gold-300' : 'text-rose-300') }}">{{ $m['attainment'] }}%</td>
                            <td class="text-right">{{ $m['clientsAcquired'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-white/40">No team members found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
