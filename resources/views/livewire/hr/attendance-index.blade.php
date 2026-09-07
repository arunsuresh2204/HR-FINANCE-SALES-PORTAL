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
                    <div class="mt-2"><x-status-pill :status="$todayAttendance->status" /></div>
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
                    </tr>
                </thead>
                <tbody>
                    @forelse ($history as $record)
                        <tr>
                            <td class="font-medium text-white">{{ $record->work_date->format('M j, Y') }}</td>
                            <td>{{ $record->clock_in?->format('g:i A') ?? '—' }}</td>
                            <td>{{ $record->clock_out?->format('g:i A') ?? '—' }}</td>
                            <td>{{ $record->clock_in && $record->clock_out ? number_format($record->clock_in->diffInMinutes($record->clock_out) / 60, 1).'h' : '—' }}</td>
                            <td><x-status-pill :status="$record->status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-white/40">No attendance records yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $history->links() }}</div>
    </div>
</div>
