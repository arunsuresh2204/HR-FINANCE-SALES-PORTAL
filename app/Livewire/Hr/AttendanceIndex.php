<?php

namespace App\Livewire\Hr;

use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceIndex extends Component
{
    use WithPagination;

    public function clockIn(): void
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $attendance = Attendance::firstOrNew(['user_id' => $user->id, 'work_date' => $today]);

        if ($attendance->exists && $attendance->clock_in) {
            $this->dispatch('toast', message: 'You have already clocked in today.', type: 'error');

            return;
        }

        $attendance->clock_in = now();
        $attendance->ip_address = request()->ip();
        $attendance->status = now()->hour >= 10 ? 'late' : 'present';
        $attendance->save();

        $this->dispatch('toast', message: 'Clocked in at '.now()->format('g:i A'), type: 'success');
    }

    public function clockOut(): void
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)->where('work_date', $today)->first();

        if (! $attendance || ! $attendance->clock_in) {
            $this->dispatch('toast', message: 'You need to clock in first.', type: 'error');

            return;
        }

        if ($attendance->clock_out) {
            $this->dispatch('toast', message: 'You have already clocked out today.', type: 'error');

            return;
        }

        $attendance->clock_out = now();
        $attendance->save();

        $this->dispatch('toast', message: 'Clocked out at '.now()->format('g:i A'), type: 'success');
    }

    public function render()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        return view('livewire.hr.attendance-index', [
            'todayAttendance' => Attendance::where('user_id', $user->id)->where('work_date', $today)->first(),
            'history' => Attendance::where('user_id', $user->id)->orderByDesc('work_date')->paginate(10),
        ]);
    }
}
