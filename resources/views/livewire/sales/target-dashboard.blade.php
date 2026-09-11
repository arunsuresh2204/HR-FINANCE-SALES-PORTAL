<div>
    <x-page-header title="Sales Performance" subtitle="Target attainment and monthly achievements.">
        <x-slot:actions>
            <div class="flex items-center gap-1.5">
                <button type="button" wire:click="shiftAnchor(-5)" class="glass rounded-lg p-1.5 text-white/50 hover:text-white" title="Previous 5 months"><x-icon name="arrow-right" class="h-3.5 w-3.5 rotate-180" /></button>
                <input wire:model.live="monthPicker" type="month" class="input-glass w-40" title="Jump to a month">
                <button type="button" wire:click="shiftAnchor(5)" class="glass rounded-lg p-1.5 text-white/50 hover:text-white" title="Next 5 months"><x-icon name="arrow-right" class="h-3.5 w-3.5" /></button>
            </div>
        </x-slot:actions>
    </x-page-header>

    @if ($teamSummary)
        <div class="mb-6">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-white/40">Team Total &middot; {{ $teamSummary['label'] }}</p>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <x-stat-card label="Team Target" value="${{ number_format($teamSummary['target']) }}" icon="target" accent="gold" />
                <x-stat-card label="Team Achieved" value="${{ number_format($teamSummary['achieved']) }}" icon="cash" accent="emerald" />
                <x-stat-card label="Attainment" value="{{ $teamSummary['target'] > 0 ? min(100, round($teamSummary['achieved'] / $teamSummary['target'] * 100)) : 0 }}%" icon="chart" accent="sky" />
                <x-stat-card label="Clients Acquired" value="{{ $teamSummary['clientsAcquired'] }}" icon="briefcase" accent="violet" :hint="$teamSummary['memberCount'].' team member'.($teamSummary['memberCount'] === 1 ? '' : 's')" />
            </div>
        </div>
    @endif

    <div class="space-y-6">
        @forelse ($rows as $row)
            <div class="glass-card">
                <a href="{{ route('sales.targets.report', [$row['user'], $row['months'][0]['year'], $row['months'][0]['month']]) }}" wire:navigate class="mb-4 flex items-center gap-3 hover:opacity-80">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gold-400/15 text-sm font-bold text-gold-300">{{ $row['user']->initials() }}</span>
                    <div>
                        <p class="flex items-center gap-2 font-semibold text-white">
                            {{ $row['user']->name }}
                            @if ($row['isSelf'])
                                <span class="badge-glass !text-[10px]">You</span>
                            @endif
                        </p>
                        <p class="text-xs text-white/40">{{ $row['openLeads'] }} open leads &middot; {{ $row['conversionRate'] }}% conversion</p>
                    </div>
                </a>

                <div class="space-y-2">
                    @foreach ($row['months'] as $m)
                        <div class="glass-inset flex flex-col gap-3 p-3 sm:flex-row sm:items-center sm:justify-between">
                            <a href="{{ route('sales.targets.report', [$row['user'], $m['year'], $m['month']]) }}" wire:navigate class="text-left text-sm font-semibold text-white hover:text-gold-300 sm:w-36 sm:shrink-0">
                                {{ $m['label'] }}
                            </a>

                            <div class="flex flex-1 items-center gap-4">
                                @if ($m['target'])
                                    <div class="w-full">
                                        <div class="mb-1 flex justify-between text-xs text-white/50">
                                            <span>${{ number_format($m['achieved']) }}</span>
                                            <span>${{ number_format($m['effectiveTarget']) }}</span>
                                        </div>
                                        <div class="h-2 rounded-full bg-white/5">
                                            <div class="h-2 rounded-full bg-gradient-to-r from-gold-500 to-gold-300" style="width: {{ min(100, $m['effectiveTarget'] > 0 ? $m['achieved'] / $m['effectiveTarget'] * 100 : 0) }}%"></div>
                                        </div>
                                        @if ($m['deficitCarried'] > 0)
                                            <p class="mt-1 text-[11px] text-rose-300/80">Includes ${{ number_format($m['deficitCarried']) }} carried from last month's shortfall</p>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-white/40">No target set for this period</span>
                                @endif
                            </div>

                            <div class="flex shrink-0 items-center gap-3">
                                <span class="text-xs text-white/40">{{ $m['clientsAcquired'] }} client{{ $m['clientsAcquired'] === 1 ? '' : 's' }} acquired</span>
                                @if ($canSetTargets)
                                    <button wire:click="openTargetForm({{ $row['user']->id }}, {{ $m['month'] }}, {{ $m['year'] }})" class="btn-glass-secondary !px-3 !py-1.5 !text-xs">{{ $m['target'] ? 'Edit Target' : 'Set Target' }}</button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <p class="text-sm text-white/40">No sales data available.</p>
        @endforelse
    </div>

    <x-modal-glass wire-model="showTargetForm" title="Set Monthly Target" max-width="sm">
        <form wire:submit="saveTarget" class="space-y-4">
            <div>
                <x-input-label for="target_amount" value="Target Amount ($)" />
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
