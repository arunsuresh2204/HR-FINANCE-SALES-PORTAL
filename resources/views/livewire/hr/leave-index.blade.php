<div>
    <x-page-header title="Time Off" subtitle="Request leave and track your balance.">
        <x-slot:actions>
            <button wire:click="openForm" class="btn-glass-primary"><x-icon name="plus" class="h-4 w-4" /> Request Leave</button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-stat-card label="Annual Entitlement" :value="\App\Livewire\Hr\LeaveIndex::ANNUAL_ENTITLEMENT" icon="calendar" accent="sky" />
        <x-stat-card label="Days Used (Approved)" :value="$usedDays" icon="calendar" accent="gold" />
        <x-stat-card label="Days Remaining" :value="$remainingDays" icon="calendar" accent="emerald" />
    </div>

    <div class="glass-panel relative mt-6 overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>Type</th><th>Dates</th><th>Days</th><th>Reason</th><th>Status</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $req)
                        <tr>
                            <td class="font-medium capitalize text-white">{{ $req->type }}</td>
                            <td>{{ $req->start_date->format('M j') }} – {{ $req->end_date->format('M j, Y') }}</td>
                            <td>{{ $req->days }}</td>
                            <td class="max-w-xs truncate">{{ $req->reason ?: '—' }}</td>
                            <td><x-status-pill :status="$req->status" /></td>
                            <td class="text-right">
                                @if ($req->status === 'pending')
                                    <button wire:click="cancel({{ $req->id }})" wire:confirm="Cancel this leave request?" class="text-xs font-semibold text-rose-300 hover:text-rose-200">Cancel</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-white/40">No leave requests yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $requests->links() }}</div>
    </div>

    <x-modal-glass wire-model="showForm" title="Request Leave">
        <form wire:submit="submit" class="space-y-4">
            <div>
                <x-input-label for="type" value="Leave Type" />
                <select wire:model="type" id="type" class="input-glass">
                    <option value="vacation">Vacation</option>
                    <option value="sick">Sick</option>
                    <option value="unpaid">Unpaid</option>
                    <option value="other">Other</option>
                </select>
                <x-input-error :messages="$errors->get('type')" class="mt-1" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="start_date" value="Start Date" />
                    <x-text-input wire:model="start_date" id="start_date" type="date" class="mt-0" />
                    <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="end_date" value="End Date" />
                    <x-text-input wire:model="end_date" id="end_date" type="date" class="mt-0" />
                    <x-input-error :messages="$errors->get('end_date')" class="mt-1" />
                </div>
            </div>
            <div>
                <x-input-label for="reason" value="Reason (optional)" />
                <textarea wire:model="reason" id="reason" rows="3" class="input-glass"></textarea>
                <x-input-error :messages="$errors->get('reason')" class="mt-1" />
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Submit Request</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
