@php
    $chartW = 640;
    $chartH = 240;
    $marginL = 48;
    $marginR = 8;
    $marginT = 16;
    $marginB = 28;
    $plotW = $chartW - $marginL - $marginR;
    $plotH = $chartH - $marginT - $marginB;
    $groupW = $plotW / max(1, count($achievement));
    $barW = 24;
    $barGap = 6;

    $maxVal = $achievement->flatMap(fn ($m) => [$m['achieved'], $m['target']])->max() ?: 1000;
    $maxVal = $maxVal * 1.2;

    $barTop = fn ($value) => $marginT + ($plotH - ($maxVal > 0 ? ($value / $maxVal) * $plotH : 0));
    $barHeight = fn ($value) => $maxVal > 0 ? ($value / $maxVal) * $plotH : 0;
    $abbr = fn ($n) => $n >= 1000 ? '$'.rtrim(rtrim(number_format($n / 1000, 1), '0'), '.').'k' : '$'.number_format($n);
@endphp

<div>
    <x-page-header :title="$user->name" subtitle="{{ $period->format('F Y') }} sales report">
        <x-slot:actions>
            <a href="{{ route('sales.targets') }}" wire:navigate class="btn-glass-secondary"><x-icon name="arrow-right" class="h-4 w-4 rotate-180" /> Back to Targets</a>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="glass-card text-center">
            <p class="text-2xl font-extrabold text-white">{{ $totalContacted }}</p>
            <p class="text-[11px] text-white/40">Leads Contacted</p>
        </div>
        <div class="glass-card text-center">
            <p class="text-2xl font-extrabold text-emerald-300">{{ $positivePct }}%</p>
            <p class="text-[11px] text-white/40">Positive Response</p>
        </div>
        <div class="glass-card text-center">
            <p class="text-2xl font-extrabold text-rose-300">{{ $negativePct }}%</p>
            <p class="text-[11px] text-white/40">Negative Response</p>
        </div>
        <div class="glass-card text-center">
            <p class="text-2xl font-extrabold text-white">{{ $closedPct }}%</p>
            <p class="text-[11px] text-white/40">Deals Closed</p>
        </div>
        <div class="glass-card text-center">
            <p class="text-2xl font-extrabold text-white">{{ $totalClients }}</p>
            <p class="text-[11px] text-white/40">Clients Acquired</p>
        </div>
        <div class="glass-card text-center">
            <p class="text-2xl font-extrabold text-gold-300">${{ number_format($totalDealValue) }}</p>
            <p class="text-[11px] text-white/40">Total Deal Value</p>
        </div>
        <div class="glass-card text-center">
            <p class="text-2xl font-extrabold text-white">{{ $totalProjects }}</p>
            <p class="text-[11px] text-white/40">Projects Created</p>
        </div>
        <div class="glass-card text-center">
            <p class="text-2xl font-extrabold text-white">{{ $winRatePct }}%</p>
            <p class="text-[11px] text-white/40">Win Rate</p>
        </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-5">
        <div class="glass-card flex flex-col lg:col-span-3">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-base font-bold text-white">Sales Achievement</h2>
                <div class="flex items-center gap-4 text-[11px] text-white/50">
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm" style="background-color:#f7c81e"></span> Achieved</span>
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm" style="background-color:rgba(255,255,255,0.25)"></span> Target</span>
                </div>
            </div>
            <svg viewBox="0 0 {{ $chartW }} {{ $chartH }}" class="w-full flex-1" preserveAspectRatio="xMidYMid meet" role="img" aria-label="Achieved versus target amount for the last six months">
                @for ($g = 0; $g < 4; $g++)
                    @php $gy = $marginT + ($plotH / 4) * $g; @endphp
                    <line x1="{{ $marginL }}" y1="{{ $gy }}" x2="{{ $chartW - $marginR }}" y2="{{ $gy }}" stroke="rgba(255,255,255,0.08)" stroke-width="1" />
                @endfor
                <line x1="{{ $marginL }}" y1="{{ $marginT + $plotH }}" x2="{{ $chartW - $marginR }}" y2="{{ $marginT + $plotH }}" stroke="rgba(255,255,255,0.15)" stroke-width="1" />

                @for ($g = 0; $g <= 4; $g++)
                    @php $gv = $maxVal * (4 - $g) / 4; @endphp
                    <text x="{{ $marginL - 6 }}" y="{{ $marginT + ($plotH / 4) * $g + 3 }}" text-anchor="end" font-size="9" fill="rgba(255,255,255,0.35)">{{ $abbr((int) $gv) }}</text>
                @endfor

                @foreach ($achievement as $i => $m)
                    @php
                        $groupX = $marginL + $i * $groupW;
                        $pairW = ($barW * 2) + $barGap;
                        $offset = ($groupW - $pairW) / 2;
                        $targetX = $groupX + $offset;
                        $achievedX = $targetX + $barW + $barGap;
                    @endphp
                    <rect x="{{ $targetX }}" y="{{ $barTop($m['target']) }}" width="{{ $barW }}" height="{{ $barHeight($m['target']) }}" rx="3" fill="rgba(255,255,255,0.25)" />
                    <rect x="{{ $achievedX }}" y="{{ $barTop($m['achieved']) }}" width="{{ $barW }}" height="{{ $barHeight($m['achieved']) }}" rx="3" fill="#f7c81e">
                        <title>{{ $m['label'] }}: {{ $abbr((int) $m['achieved']) }} achieved of {{ $abbr((int) $m['target']) }} target</title>
                    </rect>
                    <text x="{{ $achievedX + $barW / 2 }}" y="{{ $barTop($m['achieved']) - 5 }}" text-anchor="middle" font-size="9" fill="#fbe285" font-weight="600">{{ $abbr((int) $m['achieved']) }}</text>
                    <text x="{{ $groupX + $groupW / 2 }}" y="{{ $marginT + $plotH + 16 }}" text-anchor="middle" font-size="10" fill="rgba(255,255,255,0.5)">{{ $m['label'] }}</text>
                @endforeach
            </svg>
        </div>

        <div class="glass-card lg:col-span-2">
            <h2 class="mb-3 text-base font-bold text-white">Contact Outcome Breakdown</h2>
            <p class="mb-3 text-xs text-white/40">{{ $totalContacted }} leads contacted this month</p>
            <div class="space-y-2.5">
                @forelse ($breakdown as $item)
                    <div>
                        <div class="mb-1 flex items-center justify-between text-xs">
                            <span class="font-medium text-white/70">{{ $item['label'] }}</span>
                            <span class="text-white/40">{{ $item['count'] }} &middot; {{ $item['pct'] }}%</span>
                        </div>
                        <div class="h-2 rounded-full bg-white/5">
                            <div class="h-2 rounded-full" style="width: {{ $item['pct'] }}%; background-color: {{ $item['color'] }}"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-white/40">No leads contacted this month.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="glass-panel relative mb-6 overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="p-5 pb-0">
            <h2 class="text-base font-bold text-white">Channel Source Breakdown</h2>
            <p class="mt-1 text-xs text-white/40">Where this month's contacts came from, and what they converted to.</p>
        </div>
        <div class="overflow-x-auto p-5">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>Source</th>
                        <th>Leads Contacted</th>
                        <th>Share of Contacts</th>
                        <th>Deals Won</th>
                        <th>Deal Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sourceStats as $s)
                        <tr>
                            <td class="font-medium text-white">{{ $s['source'] }}</td>
                            <td class="text-white/60">{{ $s['contacted'] }}</td>
                            <td class="min-w-[10rem]">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-24 rounded-full bg-white/5">
                                        <div class="h-2 rounded-full bg-sky-400" style="width: {{ $s['contactedPct'] }}%"></div>
                                    </div>
                                    <span class="text-xs text-white/50">{{ $s['contactedPct'] }}%</span>
                                </div>
                            </td>
                            <td class="text-white/60">{{ $s['won'] }}</td>
                            <td class="font-semibold text-gold-300">${{ number_format($s['dealValue']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-white/40">No contacts logged this month.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="glass-panel relative overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="p-5">
            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-white/40">Clients Won This Month</p>
        </div>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Deal Value</th>
                        <th>Source</th>
                        <th>Contacts</th>
                        <th>Projects</th>
                        <th>Project Billing</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clientRows as $row)
                        <tr>
                            <td>
                                <p class="font-medium text-white">{{ $row['lead']->client_name }}</p>
                                @if ($row['lead']->company_name)
                                    <p class="text-xs text-white/40">{{ $row['lead']->company_name }}</p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap font-semibold text-gold-300">${{ number_format($row['lead']->budget ?? 0) }}</td>
                            <td class="text-white/60">{{ $row['lead']->source }}</td>
                            <td class="text-white/60">{{ $row['contacts'] }}</td>
                            <td class="text-white/60">
                                @forelse ($row['projects'] as $project)
                                    <p>{{ $project->name }}</p>
                                @empty
                                    <span class="text-white/25">None yet</span>
                                @endforelse
                            </td>
                            <td class="text-white/60">
                                @forelse ($row['projects'] as $project)
                                    @php $byCurrency = $project->billingRequests->groupBy('currency'); @endphp
                                    @if ($byCurrency->isEmpty())
                                        <p class="text-white/25">—</p>
                                    @else
                                        @foreach ($byCurrency as $currency => $requests)
                                            <p>{{ \App\Support\Currency::format($requests->sum('amount'), $currency) }}</p>
                                        @endforeach
                                    @endif
                                @empty
                                    <span class="text-white/25">—</span>
                                @endforelse
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-white/40">No clients won this month.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
