<div>
    <x-page-header title="Daily Timesheet" subtitle="Log your daily tasks and hours.">
        <x-slot:actions>
            <button wire:click="openForm" class="btn-glass-primary"><x-icon name="plus" class="h-4 w-4" /> Log Entry</button>
        </x-slot:actions>
    </x-page-header>

    @if ($canViewTeam)
        <div class="mb-4 flex gap-2">
            <button wire:click="setTab('mine')" class="rounded-lg px-3 py-1.5 text-sm font-semibold transition {{ $tab === 'mine' ? 'bg-gold-400/15 text-gold-200' : 'text-white/50 hover:text-white/80' }}">My Timesheets</button>
            <button wire:click="setTab('team')" class="rounded-lg px-3 py-1.5 text-sm font-semibold transition {{ $tab === 'team' ? 'bg-gold-400/15 text-gold-200' : 'text-white/50 hover:text-white/80' }}">My Team</button>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-stat-card :label="$tab === 'team' ? 'Team Hours This Week' : 'Hours This Week'" :value="number_format($weekHours, 1)" icon="clock" accent="sky" />
        <x-stat-card :label="$tab === 'team' ? 'Team Hours This Month' : 'Hours This Month'" :value="number_format($monthHours, 1)" icon="chart" accent="emerald" />
    </div>

    <div class="glass-panel relative mt-6 overflow-hidden">
        <div class="glass-sheen"></div>

        @if ($tab === 'team')
            <div class="flex flex-wrap items-center gap-3 p-4">
                <select wire:model.live="memberFilter" class="input-glass !w-auto">
                    <option value="">All team members</option>
                    @foreach ($teamMembers as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead>
                    <tr>
                        @if ($tab === 'team')
                            <th>Employee</th>
                        @endif
                        <th>Date</th><th>Project</th><th>Task</th><th>Hours</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            @if ($tab === 'team')
                                <td class="text-white">{{ $entry->user->name }}</td>
                            @endif
                            <td>{{ $entry->work_date->format('M j, Y') }}</td>
                            <td class="text-white">{{ $entry->client->business_name ?? $entry->project_name ?? '—' }}</td>
                            <td class="max-w-sm truncate">{{ $entry->task_description }}</td>
                            <td class="font-semibold text-white">{{ number_format($entry->hours, 1) }}h</td>
                            <td><x-status-pill :status="$entry->status" :title="$entry->status === 'blocked' ? $entry->blocked_reason : null" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ $tab === 'team' ? 6 : 5 }}" class="py-8 text-center text-white/40">{{ $tab === 'team' ? 'No team timesheet entries yet.' : 'No timesheet entries yet.' }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $entries->links() }}</div>
    </div>

    <x-modal-glass wire-model="showForm" title="Log Timesheet Entry">
        <form wire:submit="submit" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="work_date" value="Date" />
                    <x-text-input wire:model="work_date" id="work_date" type="date" class="mt-0" />
                    <x-input-error :messages="$errors->get('work_date')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="hours" value="Hours" />
                    <x-text-input wire:model="hours" id="hours" type="number" step="0.25" class="mt-0" />
                    <x-input-error :messages="$errors->get('hours')" class="mt-1" />
                </div>
            </div>
            <div>
                <x-input-label for="project_name" value="Project Name/Task (optional)" />
                <x-text-input wire:model="project_name" id="project_name" type="text" class="mt-0" placeholder="e.g. In-house Pet Product" />
            </div>
            <div>
                <x-input-label for="task_description" value="Task Description" />
                <textarea wire:model="task_description" id="task_description" rows="3" class="input-glass"></textarea>
                <x-input-error :messages="$errors->get('task_description')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="status" value="Status" />
                <select wire:model.live="status" id="status" class="input-glass">
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="blocked">Blocked</option>
                </select>
            </div>
            @if ($status === 'blocked')
                <div>
                    <x-input-label for="blocked_reason" value="Reason for Blocker" />
                    <textarea wire:model="blocked_reason" id="blocked_reason" rows="2" class="input-glass" placeholder="e.g. Waiting on client requirement, blocked by another dependency"></textarea>
                    <x-input-error :messages="$errors->get('blocked_reason')" class="mt-1" />
                </div>
            @endif
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Save Entry</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
