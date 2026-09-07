<div>
    <x-page-header title="Resignations & Offboarding" subtitle="Review resignation notices and track offboarding checklists." />

    <div class="space-y-4">
        @forelse ($resignations as $r)
            <div class="glass-card">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-semibold text-white">{{ $r->user->name }}</p>
                        <p class="text-sm text-white/50">Notice: {{ $r->notice_date->format('M j, Y') }} &middot; Last day: {{ $r->last_working_date->format('M j, Y') }}</p>
                        @if ($r->reason)
                            <p class="mt-2 text-sm text-white/60">"{{ $r->reason }}"</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <x-status-pill :status="$r->status" />
                        @if ($r->status === 'pending')
                            <button wire:click="accept({{ $r->id }})" class="rounded-lg bg-emerald-400/15 px-2.5 py-1 text-xs font-semibold text-emerald-300 hover:bg-emerald-400/25">Accept</button>
                            <button wire:click="reject({{ $r->id }})" class="rounded-lg bg-rose-400/15 px-2.5 py-1 text-xs font-semibold text-rose-300 hover:bg-rose-400/25">Reject</button>
                        @endif
                    </div>
                </div>

                @if ($r->offboardingTasks->isNotEmpty())
                    <div class="mt-4 border-t border-white/10 pt-4">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-white/40">Offboarding Checklist</p>
                        <div class="space-y-1.5">
                            @foreach ($r->offboardingTasks as $task)
                                <label class="flex items-center gap-2 text-sm {{ $task->completed ? 'text-white/40 line-through' : 'text-white/75' }}">
                                    <input type="checkbox" wire:click="toggleTask({{ $task->id }})" @checked($task->completed) class="rounded border-white/20 bg-white/5 text-gold-400 focus:ring-gold-400/40">
                                    {{ $task->task }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <p class="text-sm text-white/40">No resignation notices on file.</p>
        @endforelse
    </div>
</div>
