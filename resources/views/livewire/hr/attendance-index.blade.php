<div>
    <x-page-header title="Time & Attendance" subtitle="Clock in and out, and review your attendance history." />

    <div class="glass-panel relative mb-6 overflow-hidden p-6">
        <div class="glass-sheen"></div>
        <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <p class="text-sm text-white/50">{{ now()->format('l, F j, Y') }}</p>
                <p class="mt-1 text-2xl font-extrabold text-white">
                    @if ($todayAttendance?->clock_in && $todayAttendance?->clock_out)
                        Completed
                    @elseif ($todayAttendance?->clock_in)
                        Clocked In &middot; {{ $todayAttendance->clock_in->format('g:i A') }}
                    @else
                        Not Clocked In
                    @endif
                </p>
                @if ($todayAttendance)
                    <div class="mt-2"><x-attendance-status-pill :info="$statusFor($todayAttendance)" /></div>
                @endif
            </div>
            <div class="flex gap-3">
                <button wire:click="clockIn" @disabled($todayAttendance?->clock_in) class="btn-glass-primary disabled:cursor-not-allowed disabled:opacity-40">
                    <x-icon name="clock" class="h-4 w-4" /> Clock In
                </button>
                <button wire:click="clockOut" @disabled(!$todayAttendance?->clock_in || $todayAttendance?->clock_out) class="btn-glass-secondary disabled:cursor-not-allowed disabled:opacity-40">
                    <x-icon name="exit" class="h-4 w-4" /> Clock Out
                </button>
            </div>
        </div>
    </div>

    <div class="glass-panel relative overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th>Hours</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($history as $record)
                        <tr>
                            <td class="font-medium text-white">{{ $record->work_date->format('M j, Y') }}</td>
                            <td>{{ $record->clock_in?->format('g:i A') ?? '—' }}</td>
                            <td>{{ $record->clock_out?->format('g:i A') ?? '—' }}</td>
                            <td>{{ $record->clock_in && $record->clock_out ? number_format($record->clock_in->diffInMinutes($record->clock_out) / 60, 1).'h' : '—' }}</td>
                            <td><x-attendance-status-pill :info="$statusFor($record)" /></td>
                            <td class="text-right">
                                @php $existingRequest = $latestRequests->get($record->id); @endphp
                                @if ($existingRequest && $existingRequest->status === 'pending')
                                    <span class="badge-glass !border-amber-400/25 !bg-amber-400/10 !text-amber-200">Review Pending</span>
                                @elseif ($existingRequest && $existingRequest->status === 'approved')
                                    <span class="badge-glass !border-emerald-400/25 !bg-emerald-400/10 !text-emerald-200">Request Approved</span>
                                @elseif ($existingRequest && $existingRequest->status === 'rejected')
                                    <button wire:click="openRequestForm({{ $record->id }})" class="text-xs font-semibold text-white/40 hover:text-white">Request Declined &middot; Resubmit</button>
                                @else
                                    <button wire:click="openRequestForm({{ $record->id }})" class="text-xs font-semibold text-gold-300 hover:text-gold-200">Request Change</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-white/40">No attendance records yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $history->links() }}</div>
    </div>

    <x-modal-glass wire-model="showRequestForm" title="Request Status Change">
        <form wire:submit="submitStatusRequest" class="space-y-4">
            <p class="text-sm text-white/50">Ask HR to review and correct this day's attendance status.</p>
            <div>
                <x-input-label for="reason_category" value="What should it be?" />
                <select wire:model="reason_category" id="reason_category" class="input-glass">
                    <option value="">Select a reason...</option>
                    @foreach (\App\Livewire\Hr\AttendanceIndex::REASON_CATEGORIES as $categoryOption)
                        <option value="{{ $categoryOption }}">{{ $categoryOption }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('reason_category')" class="mt-1" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="requested_clock_in" value="Actual Login Time" />
                    <x-text-input wire:model="requested_clock_in" id="requested_clock_in" type="time" class="mt-0" />
                    <x-input-error :messages="$errors->get('requested_clock_in')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="requested_clock_out" value="Actual Logoff Time" />
                    <x-text-input wire:model="requested_clock_out" id="requested_clock_out" type="time" class="mt-0" />
                    <x-input-error :messages="$errors->get('requested_clock_out')" class="mt-1" />
                </div>
            </div>
            <p class="text-xs text-white/40">Optional &mdash; if you forgot to clock in or out, enter the actual times so HR can update your record accurately.</p>
            <div>
                <x-input-label for="request_reason" value="Reason" />
                <textarea wire:model="request_reason" id="request_reason" rows="3" class="input-glass" placeholder="e.g. My login was delayed due to a network outage."></textarea>
                <x-input-error :messages="$errors->get('request_reason')" class="mt-1" />
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Submit Request</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
