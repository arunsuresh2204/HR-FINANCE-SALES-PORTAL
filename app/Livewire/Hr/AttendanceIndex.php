<?php

namespace App\Livewire\Hr;

use App\Models\Attendance;
use App\Models\AttendanceStatusRequest;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceIndex extends Component
{
    use WithPagination;

    public const REASON_CATEGORIES = ['Forgot to clockin', 'Forgot to clockout', 'Onsite duty', 'Business travel'];

    public ?int $requestingAttendanceId = null;

    public bool $showRequestForm = false;

    #[Validate('required|in:Forgot to clockin,Forgot to clockout,Onsite duty,Business travel')]
    public string $reason_category = '';

    #[Validate('required|string|max:1000')]
    public string $request_reason = '';

    #[Validate('nullable|date_format:H:i')]
    public string $requested_clock_in = '';

    #[Validate('nullable|date_format:H:i')]
    public string $requested_clock_out = '';

    public function clockIn(): void
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)->whereDate('work_date', $today)->first()
            ?? new Attendance(['user_id' => $user->id, 'work_date' => $today]);

        if ($attendance->exists && $attendance->clock_in) {
            $this->dispatch('toast', message: 'You have already clocked in today.', type: 'error');

            return;
        }

        $now = now();
        $attendance->clock_in = $now;
        $attendance->ip_address = request()->ip();
        $attendance->scheduled_login_time = $attendance->scheduled_login_time ?? $user->scheduled_login_time;

        if ($attendance->scheduled_login_time) {
            $scheduledAt = Carbon::parse($today.' '.$attendance->scheduled_login_time);
            $attendance->status = $now->lte($scheduledAt) ? 'present' : 'late';
        } else {
            $attendance->status = $now->hour >= 10 ? 'late' : 'present';
        }

        $attendance->save();

        $this->dispatch('toast', message: 'Clocked in at '.now()->format('g:i A'), type: 'success');
    }

    public function clockOut(): void
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)->whereDate('work_date', $today)->first();

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

    public function openRequestForm(int $attendanceId): void
    {
        $attendance = Attendance::where('user_id', Auth::id())->findOrFail($attendanceId);

        if ($attendance->statusRequests()->where('status', 'pending')->exists()) {
            $this->dispatch('toast', message: 'You already have a pending request for this day.', type: 'error');

            return;
        }

        $this->requestingAttendanceId = $attendanceId;
        $this->reason_category = '';
        $this->request_reason = '';
        $this->requested_clock_in = $attendance->clock_in?->format('H:i') ?? '';
        $this->requested_clock_out = $attendance->clock_out?->format('H:i') ?? '';
        $this->resetValidation();
        $this->showRequestForm = true;
    }

    public function cancelRequestForm(): void
    {
        $this->reset(['requestingAttendanceId', 'reason_category', 'request_reason', 'requested_clock_in', 'requested_clock_out', 'showRequestForm']);
    }

    public function submitStatusRequest(): void
    {
        $this->validate();

        $attendance = Attendance::where('user_id', Auth::id())->findOrFail($this->requestingAttendanceId);

        if ($attendance->statusRequests()->where('status', 'pending')->exists()) {
            $this->dispatch('toast', message: 'You already have a pending request for this day.', type: 'error');

            return;
        }

        AttendanceStatusRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => Auth::id(),
            // Every reason category here is the employee asserting they were working that
            // day, just without a proper clock event — so the target status is always Present.
            'requested_status' => 'present',
            'reason_category' => $this->reason_category,
            'requested_clock_in' => $this->requested_clock_in ?: null,
            'requested_clock_out' => $this->requested_clock_out ?: null,
            'reason' => $this->request_reason,
        ]);

        Notification::sendToMany(
            User::role(['hr_admin', 'super_admin'])->get(),
            'attendance_request_submitted',
            Auth::user()->name.' requested an attendance change',
            'For '.$attendance->work_date->format('M j, Y').' — review it on the Attendance Oversight page.',
            route('hradmin.attendance')
        );

        $this->reset(['requestingAttendanceId', 'reason_category', 'request_reason', 'requested_clock_in', 'requested_clock_out', 'showRequestForm']);
        $this->dispatch('toast', message: 'Request sent to HR for review.', type: 'success');
    }

    public function render()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $history = Attendance::where('user_id', $user->id)->orderByDesc('work_date')->paginate(10);

        $latestRequests = AttendanceStatusRequest::where('user_id', $user->id)
            ->whereIn('attendance_id', $history->pluck('id'))
            ->latest()
            ->get()
            ->groupBy('attendance_id')
            ->map(fn ($group) => $group->first());

        return view('livewire.hr.attendance-index', [
            'todayAttendance' => Attendance::where('user_id', $user->id)->whereDate('work_date', $today)->first(),
            'history' => $history,
            'statusFor' => fn (Attendance $att) => Attendance::computeStatus($user, $att->work_date->toDateString(), $att),
            'latestRequests' => $latestRequests,
        ]);
    }
}
