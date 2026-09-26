<?php

namespace App\Livewire\Hr;

use App\Models\Attendance;
use App\Models\AttendanceStatusRequest;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Timesheet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceIndex extends Component
{
    use WithPagination;

    public const REASON_CATEGORIES = ['Forgot to clockin', 'Forgot to clockout', 'Onsite duty', 'Business travel'];

    public ?int $requestingAttendanceId = null;

    public bool $showRequestForm = false;

    public string $reason_category = '';

    public string $request_reason = '';

    public string $requested_clock_in = '';

    public string $requested_clock_out = '';

    // Prompted right after clocking out, for anyone with timesheet access.
    public bool $showTimesheetPrompt = false;

    public ?int $ts_project_id = null;

    public string $ts_project_name = '';

    public string $ts_work_date = '';

    public string $ts_task_description = '';

    public string $ts_hours = '';

    public string $ts_status = 'completed';

    public string $ts_blocked_reason = '';

    public function clockIn(): void
    {
        $user = Auth::user();
        $today = $user->localNow()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)->whereDate('work_date', $today)->first()
            ?? new Attendance(['user_id' => $user->id, 'work_date' => $today]);

        if ($attendance->exists && $attendance->clock_in) {
            $this->dispatch('toast', message: 'You have already clocked in today.', type: 'error');

            return;
        }

        $now = now();
        $previousAutoLeaveRequestId = $attendance->auto_leave_request_id;

        $attendance->clock_in = $now;
        $attendance->ip_address = request()->ip();
        $attendance->scheduled_login_time = $attendance->scheduled_login_time ?? $user->scheduled_login_time;
        $attendance->status = Attendance::deriveStatusFromClockIn($user, $today, $now);

        if ($attendance->status === 'half_day') {
            $attendance->auto_leave_request_id = LeaveRequest::create([
                'user_id' => $user->id,
                'type' => 'vacation',
                'start_date' => $today,
                'end_date' => $today,
                'days' => 0.5,
                'reason' => 'Auto-marked: clocked in more than '.Attendance::HALF_DAY_CUTOFF_HOURS.' hours late.',
                'status' => 'approved',
                'reviewed_at' => now(),
            ])->id;
        } else {
            $attendance->auto_leave_request_id = null;
        }

        $attendance->save();

        // A full day was already auto-marked as leave (the end-of-day sync ran before they
        // finally showed up) — they did work part of the day after all, so give it back.
        if ($previousAutoLeaveRequestId && $previousAutoLeaveRequestId !== $attendance->auto_leave_request_id) {
            LeaveRequest::where('id', $previousAutoLeaveRequestId)->update(['status' => 'cancelled']);
        }

        $this->dispatch('toast', message: 'Clocked in at '.$now->copy()->setTimezone($user->tz())->format('g:i A'), type: 'success');
    }

    public function clockOut(): void
    {
        $user = Auth::user();
        $today = $user->localNow()->toDateString();

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

        $this->dispatch('toast', message: 'Clocked out at '.$attendance->clock_out->copy()->setTimezone($user->tz())->format('g:i A'), type: 'success');

        if ($user->can('access_timesheets') && ! Timesheet::where('user_id', $user->id)->whereDate('work_date', $today)->exists()) {
            $minutes = $attendance->clock_in->diffInMinutes($attendance->clock_out);
            $suggestedHours = max(0.25, min(24, round($minutes / 60 * 4) / 4));

            $this->ts_project_id = null;
            $this->ts_project_name = '';
            $this->ts_work_date = $today;
            $this->ts_task_description = '';
            $this->ts_hours = (string) $suggestedHours;
            $this->ts_status = 'completed';
            $this->ts_blocked_reason = '';
            $this->resetValidation();
            $this->showTimesheetPrompt = true;
        }
    }

    public function logTimesheet(): void
    {
        $this->validate([
            'ts_project_id' => ['nullable', Rule::exists('project_developers', 'project_id')->where('user_id', Auth::id())],
            'ts_project_name' => 'nullable|string|max:255',
            'ts_work_date' => 'required|date|before_or_equal:today',
            'ts_task_description' => 'required|string|max:1000',
            'ts_hours' => 'required|numeric|min:0.25|max:24',
            'ts_status' => 'required|in:in_progress,completed,blocked',
            'ts_blocked_reason' => 'required_if:ts_status,blocked|nullable|string|max:500',
        ]);

        $project = $this->ts_project_id ? Project::find($this->ts_project_id) : null;

        Timesheet::create([
            'user_id' => Auth::id(),
            'project_id' => $this->ts_project_id,
            'client_id' => $project?->client_id,
            'project_name' => $project?->name ?? $this->ts_project_name,
            'work_date' => $this->ts_work_date,
            'task_description' => $this->ts_task_description,
            'hours' => $this->ts_hours,
            'status' => $this->ts_status,
            'blocked_reason' => $this->ts_status === 'blocked' ? $this->ts_blocked_reason : null,
        ]);

        $this->showTimesheetPrompt = false;
        $this->dispatch('toast', message: 'Timesheet entry logged.', type: 'success');
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
        $this->validate([
            'reason_category' => 'required|in:Forgot to clockin,Forgot to clockout,Onsite duty,Business travel',
            'request_reason' => 'required|string|max:1000',
            'requested_clock_in' => 'nullable|date_format:H:i',
            'requested_clock_out' => 'nullable|date_format:H:i',
        ]);

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
        $today = $user->localNow()->toDateString();

        $todayAttendance = Attendance::where('user_id', $user->id)->whereDate('work_date', $today)->first();

        $history = Attendance::where('user_id', $user->id)->orderByDesc('work_date')->paginate(10);

        // Once the employee's scheduled login time has arrived, today should be visible on
        // the list — blank times, live-computed status — even before any row exists for it,
        // so they can see the clock running rather than the day silently not appearing.
        if (! $todayAttendance && $this->getPage() === 1 && $user->scheduled_login_time) {
            $scheduledAt = Carbon::parse($today.' '.$user->scheduled_login_time, $user->tz());

            if ($user->localNow()->gte($scheduledAt)) {
                $history->getCollection()->prepend(new Attendance([
                    'user_id' => $user->id,
                    'work_date' => $today,
                    'scheduled_login_time' => $user->scheduled_login_time,
                ]));
            }
        }

        $latestRequests = AttendanceStatusRequest::where('user_id', $user->id)
            ->whereIn('attendance_id', $history->pluck('id'))
            ->latest()
            ->get()
            ->groupBy('attendance_id')
            ->map(fn ($group) => $group->first());

        return view('livewire.hr.attendance-index', [
            'todayAttendance' => $todayAttendance,
            'todayStatus' => Attendance::computeStatus($user, $today, $todayAttendance),
            'history' => $history,
            'statusFor' => fn (Attendance $att) => Attendance::computeStatus($user, $att->work_date->toDateString(), $att),
            'tz' => $user->tz(),
            'latestRequests' => $latestRequests,
            'assignedProjects' => $this->showTimesheetPrompt ? $user->developerProjects()->with('client')->orderBy('name')->get() : collect(),
        ]);
    }
}
