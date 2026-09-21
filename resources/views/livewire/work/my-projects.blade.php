<div>
    <x-page-header title="My Projects" subtitle="Projects assigned to you, their developers, and progress." />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Assigned Projects" :value="$assignedProjectsCount" icon="briefcase" accent="sky" />
        <x-stat-card label="Total Hours Logged" :value="number_format($totalHoursAll, 1)" icon="clock" accent="emerald" />
        <x-stat-card label="Blocked Entries" :value="$blockedEntriesAll" icon="bell" accent="rose" />
    </div>

    <div class="glass-card mt-6">
        <div class="flex flex-wrap items-center gap-3">
            <div class="relative min-w-[200px] flex-1">
                <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-white/30"><x-icon name="search" class="h-4 w-4" /></span>
                <input wire:model.live.debounce.400ms="search" type="text" class="input-glass !pl-9" placeholder="Search by project or client…">
            </div>
            <div class="flex flex-wrap gap-2">
                <button wire:click="setStatusFilter('all')" class="rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $statusFilter === 'all' ? 'border-gold-400/30 bg-gold-400/15 text-gold-300' : 'border-white/10 bg-white/5 text-white/50 hover:bg-white/10' }}">All</button>
                <button wire:click="setStatusFilter('active')" class="rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $statusFilter === 'active' ? 'border-gold-400/30 bg-gold-400/15 text-gold-300' : 'border-white/10 bg-white/5 text-white/50 hover:bg-white/10' }}">Active</button>
                <button wire:click="setStatusFilter('awaiting_estimate')" class="rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $statusFilter === 'awaiting_estimate' ? 'border-gold-400/30 bg-gold-400/15 text-gold-300' : 'border-white/10 bg-white/5 text-white/50 hover:bg-white/10' }}">Awaiting Estimate</button>
                <button wire:click="setStatusFilter('on_hold')" class="rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $statusFilter === 'on_hold' ? 'border-gold-400/30 bg-gold-400/15 text-gold-300' : 'border-white/10 bg-white/5 text-white/50 hover:bg-white/10' }}">On Hold</button>
                <button wire:click="setStatusFilter('completed')" class="rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $statusFilter === 'completed' ? 'border-gold-400/30 bg-gold-400/15 text-gold-300' : 'border-white/10 bg-white/5 text-white/50 hover:bg-white/10' }}">Completed</button>
            </div>
        </div>
    </div>

    <div class="mt-6 space-y-4">
        @forelse ($rows as $row)
            @php($project = $row['project'])
            <div onclick="window.location='{{ route('work.project-show', $project) }}'" class="glass-card-hover cursor-pointer{{ $row['pendingTaskCount'] > 0 ? ' border-violet-400/25' : '' }}">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <p class="text-base font-bold text-white">{{ $project->name }}</p>
                        <p class="text-xs text-white/40">{{ $project->client->business_name }}</p>
                    </div>
                    <x-status-pill :status="$project->status" />
                </div>
                @if ($project->description)
                    <p class="mt-3 text-sm text-white/45">{{ $project->description }}</p>
                @endif

                @if ($row['pendingTaskCount'] > 0)
                    <div class="mt-3 flex items-center gap-2 rounded-lg border border-violet-400/30 bg-violet-400/10 px-3 py-2 text-xs font-semibold text-violet-200">
                        <x-icon name="bell" class="h-4 w-4 shrink-0" />
                        {{ $row['pendingTaskCount'] }} new task{{ $row['pendingTaskCount'] > 1 ? 's' : '' }} requested — needs your review &amp; pricing
                    </div>
                @endif

                <div class="mt-4 flex items-center justify-between border-t border-white/10 pt-4">
                    <p class="text-xs font-semibold text-gold-300">Open project &rarr;</p>
                    @if ($project->requirement_file)
                        <a href="{{ Storage::url($project->requirement_file) }}" target="_blank" onclick="event.stopPropagation()" class="text-xs font-semibold text-white/40 hover:text-white">View Requirement</a>
                    @endif
                </div>
            </div>
        @empty
            <div class="glass-card text-center text-sm text-white/40">
                {{ $assignedProjectsCount === 0 ? 'No projects have been assigned to you yet.' : 'No projects match this filter.' }}
            </div>
        @endforelse
    </div>
</div>
