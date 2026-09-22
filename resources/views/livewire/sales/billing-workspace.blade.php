<div>
    <x-page-header title="Ready to Bill" subtitle="Tasks engineering has flagged as ready — pick the ones to bundle into a billing request." />

    @if (! $viewingClient)
        {{-- ================= LEVEL 1: CLIENTS ================= --}}
        <div class="mt-6 space-y-3">
            @forelse ($clients as $row)
                <div wire:click="viewClient({{ $row['client']->id }})" class="glass-card-hover cursor-pointer flex items-center justify-between">
                    <div>
                        <p class="text-base font-bold text-white">{{ $row['client']->business_name }}</p>
                        <p class="mt-0.5 text-xs text-white/40">{{ $row['readyCount'] }} task{{ $row['readyCount'] === 1 ? '' : 's' }} ready to bill</p>
                    </div>
                    <x-icon name="arrow-right" class="h-4 w-4 text-white/30" />
                </div>
            @empty
                <div class="glass-card py-10 text-center text-sm text-white/40">
                    Nothing waiting on you right now — tasks show up here once an engineering manager marks them ready to bill.
                </div>
            @endforelse
        </div>
    @elseif (! $viewingProject)
        {{-- ================= LEVEL 2: PROJECTS ================= --}}
        <div class="mt-6">
            <button wire:click="backToClients" class="text-xs font-semibold text-white/50 hover:text-white">&larr; Clients</button>
            <h2 class="mt-2 text-lg font-bold text-white">{{ $viewingClient->business_name }}</h2>
        </div>
        <div class="mt-4 space-y-3">
            @forelse ($projects as $row)
                <div wire:click="viewProject({{ $row['project']->id }})" class="glass-card-hover cursor-pointer flex items-center justify-between">
                    <div>
                        <p class="text-base font-bold text-white">{{ $row['project']->name }}</p>
                        <p class="mt-0.5 text-xs text-white/40">{{ $row['readyCount'] }} task{{ $row['readyCount'] === 1 ? '' : 's' }} ready to bill</p>
                    </div>
                    <x-icon name="arrow-right" class="h-4 w-4 text-white/30" />
                </div>
            @empty
                <div class="glass-card py-10 text-center text-sm text-white/40">No projects with ready-to-bill tasks for this client.</div>
            @endforelse
        </div>
    @else
        {{-- ================= LEVEL 3: TASKS ================= --}}
        <div class="mt-6">
            <button wire:click="backToProjects" class="text-xs font-semibold text-white/50 hover:text-white">&larr; {{ $viewingClient->business_name }}</button>
            <h2 class="mt-2 text-lg font-bold text-white">{{ $viewingProject->name }}</h2>
        </div>

        <div class="glass-card mt-4 !p-0 overflow-hidden">
            <table class="table-glass">
                <thead><tr><th class="w-10"></th><th>Task</th><th>Category</th><th class="text-right">Amount</th></tr></thead>
                <tbody>
                    @foreach ($readyTasks as $task)
                        <tr>
                            <td><input type="checkbox" wire:model.live="selectedTaskIds" value="{{ $task->id }}" class="rounded border-white/20 bg-white/5"></td>
                            <td class="font-medium text-white">{{ $task->title }}</td>
                            <td class="text-white/60">{{ $task->category->name ?? '—' }}</td>
                            <td class="text-right text-white/70">{{ \App\Support\Currency::format($task->effectiveAmount(), $task->currency) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="glass-card mt-4 flex flex-wrap items-center justify-between gap-3">
            <div class="text-sm text-white/60">
                @forelse ($selectedTotals as $currencyCode => $sum)
                    <span class="mr-3 font-bold text-gold-300">{{ \App\Support\Currency::format($sum, $currencyCode) }}</span>
                @empty
                    Select tasks above to bundle into one billing request.
                @endforelse
            </div>
            <button wire:click="createBillingRequest" @if (empty($selectedTaskIds)) disabled @endif class="btn-glass-primary text-sm disabled:cursor-not-allowed disabled:opacity-40"><x-icon name="cash" class="h-4 w-4" /> Create Billing Request</button>
        </div>
    @endif
</div>
