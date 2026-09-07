<div>
    <x-page-header title="Resignation" subtitle="Submit your resignation notice to HR.">
        @if ($resignations->where('status', 'pending')->isEmpty())
            <x-slot:actions>
                <button wire:click="openForm" class="btn-glass-danger"><x-icon name="exit" class="h-4 w-4" /> Submit Resignation</button>
            </x-slot:actions>
        @endif
    </x-page-header>

    <div class="space-y-4">
        @forelse ($resignations as $r)
            <div class="glass-card">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-semibold text-white">Notice given {{ $r->notice_date->format('M j, Y') }}</p>
                        <p class="text-sm text-white/50">Last working day: {{ $r->last_working_date->format('M j, Y') }}</p>
                        @if ($r->reason)
                            <p class="mt-2 text-sm text-white/60">{{ $r->reason }}</p>
                        @endif
                    </div>
                    <x-status-pill :status="$r->status" />
                </div>

                @if ($r->status !== 'pending' && $r->offboardingTasks->isNotEmpty())
                    <div class="mt-4 border-t border-white/10 pt-4">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-white/40">Offboarding Checklist</p>
                        <ul class="space-y-1.5">
                            @foreach ($r->offboardingTasks as $task)
                                <li class="flex items-center gap-2 text-sm {{ $task->completed ? 'text-white/40 line-through' : 'text-white/75' }}">
                                    <x-icon name="check" class="h-3.5 w-3.5 {{ $task->completed ? 'text-emerald-400' : 'text-white/20' }}" />
                                    {{ $task->task }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @empty
            <p class="text-sm text-white/40">You have not submitted a resignation.</p>
        @endforelse
    </div>

    <x-modal-glass wire-model="showForm" title="Submit Resignation">
        <form wire:submit="submit" class="space-y-4">
            <div>
                <x-input-label for="last_working_date" value="Proposed Last Working Date" />
                <x-text-input wire:model="last_working_date" id="last_working_date" type="date" class="mt-0" />
                <x-input-error :messages="$errors->get('last_working_date')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="reason" value="Reason (optional)" />
                <textarea wire:model="reason" id="reason" rows="3" class="input-glass"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Submit</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
