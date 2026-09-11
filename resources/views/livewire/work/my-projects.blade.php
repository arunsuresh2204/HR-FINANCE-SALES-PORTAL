<div>
    <x-page-header title="My Projects" subtitle="Projects assigned to you, their developers, and progress." />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Assigned Projects" :value="$rows->count()" icon="briefcase" accent="sky" />
        <x-stat-card label="Total Hours Logged" :value="number_format($rows->sum('totalHours'), 1)" icon="clock" accent="emerald" />
        <x-stat-card label="Blocked Entries" :value="$rows->sum('blockedEntries')" icon="bell" accent="rose" />
    </div>

    <div class="mt-6 space-y-4">
        @forelse ($rows as $row)
            @php($project = $row['project'])
            <div class="glass-card">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <p class="text-base font-bold text-white">{{ $project->name }}</p>
                        <p class="text-xs text-white/40">{{ $project->client->business_name }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-status-pill :status="$project->status" />
                        <button wire:click="openDeveloperForm({{ $project->id }})" class="btn-glass-secondary text-xs"><x-icon name="users" class="h-4 w-4" /> Manage Developers</button>
                    </div>
                </div>
                @if ($project->description)
                    <p class="mt-2 text-sm text-white/50">{{ $project->description }}</p>
                @endif
                @if ($project->requirement_file)
                    <a href="{{ Storage::url($project->requirement_file) }}" target="_blank" class="mt-2 inline-block text-xs font-semibold text-gold-300 hover:text-gold-200">View Requirement</a>
                @endif

                <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                    <div class="glass-inset p-2"><p class="text-lg font-bold text-white">{{ number_format($row['totalHours'], 1) }}</p><p class="text-[11px] text-white/40">Hours Logged</p></div>
                    <div class="glass-inset p-2"><p class="text-lg font-bold text-emerald-300">{{ $row['completedEntries'] }}</p><p class="text-[11px] text-white/40">Completed</p></div>
                    <div class="glass-inset p-2"><p class="text-lg font-bold text-rose-300">{{ $row['blockedEntries'] }}</p><p class="text-[11px] text-white/40">Blocked</p></div>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="table-glass">
                        <thead>
                            <tr><th>Developer</th><th>Hours</th><th>Completed</th><th>In Progress</th><th>Blocked</th><th>Last Update</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($row['developers'] as $dev)
                                <tr>
                                    <td class="text-white">{{ $dev['user']->name }}</td>
                                    <td>{{ number_format($dev['hours'], 1) }}h</td>
                                    <td>{{ $dev['completed'] }}</td>
                                    <td>{{ $dev['inProgress'] }}</td>
                                    <td>{{ $dev['blocked'] }}</td>
                                    <td>{{ $dev['lastEntry']?->work_date?->format('M j, Y') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="py-4 text-center text-white/40">No developers assigned to this project yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="glass-card text-center text-sm text-white/40">No projects have been assigned to you yet.</div>
        @endforelse
    </div>

    <x-modal-glass wire-model="showDeveloperForm" title="Manage Developers" max-width="sm">
        <form wire:submit="saveDevelopers" class="space-y-4">
            <div class="max-h-64 space-y-2 overflow-y-auto">
                @forelse ($developersList as $dev)
                    <label class="glass-inset flex items-center gap-2 p-3 text-sm text-white/80">
                        <input type="checkbox" wire:model="developer_ids" value="{{ $dev->id }}" class="rounded border-white/20 bg-white/5 text-gold-400 focus:ring-gold-400/40">
                        {{ $dev->name }}
                    </label>
                @empty
                    <p class="text-sm text-white/40">No developers on file yet.</p>
                @endforelse
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Save</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
