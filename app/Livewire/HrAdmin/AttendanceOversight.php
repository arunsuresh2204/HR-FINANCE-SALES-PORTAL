<?php

namespace App\Livewire\HrAdmin;

use App\Models\Attendance;
use App\Models\AttendanceStatusRequest;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceOversight extends Component
{
    use WithPagination;

    public string $tab = 'today';

    public string $date = '';

    public string $search = '';

    public string $requestFilter = 'pending';

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['today', 'requests'], true) ? $tab : 'today';
        $this->resetPage();
    }

    public function prevDay(): void
    {
        $this->date = Carbon::parse($this->date)->subDay()->toDateString();
    }

    public function nextDay(): void
    {
        $next = Carbon::parse($this->date)->addDay();
        $this->date = $next->gt(now()) ? now()->toDateString() : $next->toDateString();
    }

    public function goToday(): void
    {
        $this->date = now()->toDateString();
    }

    public function approveRequest(AttendanceStatusRequest $attendanceStatusRequest): void
    {
        $attendance = $attendanceStatusRequest->attendance;
        $employee = $attendanceStatusRequest->user;
        $workDate = $attendance->work_date->toDateString();
        $autoLeaveRequestId = $attendance->auto_leave_request_id;

        if ($attendanceStatusRequest->requested_clock_in) {
            $attendance->clock_in = Carbon::parse($workDate.' '.$attendanceStatusRequest->requested_clock_in, $employee->tz());
        }

        if ($attendanceStatusRequest->requested_clock_out) {
            $attendance->clock_out = Carbon::parse($workDate.' '.$attendanceStatusRequest->requested_clock_out, $employee->tz());
        }

        if ($attendanceStatusRequest->requested_status === 'on_leave') {
            $attendance->status = 'on_leave';
        } elseif ($attendance->clock_in) {
            // A real clock-in time was supplied — let the schedule decide on-time vs. late vs.
            // half-day, same as a normal self-service clock-in, rather than trusting the raw
            // dropdown.
            $attendance->status = Attendance::deriveStatusFromClockIn($employee, $workDate, $attendance->clock_in);
        } else {
            $attendance->status = $attendanceStatusRequest->requested_status;
        }

        // Approving a correction means HR is accepting a different account of the day than
        // whatever got auto-deducted (a full day for never clocking in, a half day for
        // clocking in very late) — so that deduction is reinstated regardless of what the
        // corrected status turns out to be.
        if ($autoLeaveRequestId) {
            LeaveRequest::where('id', $autoLeaveRequestId)->update(['status' => 'cancelled']);
        }

        $attendance->auto_leave_request_id = null;

        // The corrected account can itself still amount to a half-day (clock-in more than 4
        // hours late) or a full day off (no clock-in at all) — deduct exactly as the
        // automatic sync/clock-in would, rather than leaving it undeducted just because HR
        // reviewed it manually. Skipped if a genuine approved leave already covers the day.
        if (in_array($attendance->status, ['half_day', 'on_leave'], true) && ! $employee->hasApprovedLeaveOn($workDate)) {
            $newAutoLeave = LeaveRequest::create([
                'user_id' => $employee->id,
                'type' => 'vacation',
                'start_date' => $workDate,
                'end_date' => $workDate,
                'days' => $attendance->status === 'half_day' ? 0.5 : 1,
                'reason' => $attendance->status === 'half_day'
                    ? 'Auto-marked (corrected): clock-in more than 4 hours late.'
                    : 'Auto-marked (corrected): no clock-in recorded for the day.',
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            $attendance->auto_leave_request_id = $newAutoLeave->id;
        }

        $attendance->save();

        $attendanceStatusRequest->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $statusLabel = Attendance::computeStatus($employee, $workDate, $attendance)['label'];
        $dateLabel = $attendance->work_date->format('M j, Y');

        Notification::send(
            $employee,
            'attendance_request_approved',
            'Your attendance request was approved',
            "Your attendance for {$dateLabel} is now marked {$statusLabel}.",
            route('hr.attendance')
        );

        Notification::sendToMany(
            User::role(['hr_admin', 'super_admin'])->where('id', '!=', Auth::id())->get(),
            'attendance_status_changed',
            "{$employee->name}'s attendance was updated",
            "{$dateLabel} changed to {$statusLabel} following their request (reviewed by ".Auth::user()->name.').',
            route('hradmin.attendance')
        );

        $this->dispatch('toast', message: 'Request approved and attendance updated.', type: 'success');
    }

    public function rejectRequest(AttendanceStatusRequest $attendanceStatusRequest): void
    {
        $attendanceStatusRequest->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        Notification::send(
            $attendanceStatusRequest->user,
            'attendance_request_rejected',
            'Your attendance request was declined',
            'Your request for '.$attendanceStatusRequest->attendance->work_date->format('M j, Y').' was not approved. You can resubmit with more detail if needed.',
            route('hr.attendance')
        );

        $this->dispatch('toast', message: 'Request rejected.', type: 'success');
    }

    public function render()
    {
        $pendingCount = AttendanceStatusRequest::where('status', 'pending')->count();

        if ($this->tab === 'requests') {
            $query = AttendanceStatusRequest::with(['user', 'attendance'])->latest();

            if ($this->requestFilter !== 'all') {
                $query->where('status', $this->requestFilter);
            }

            $requestCounts = AttendanceStatusRequest::where('created_at', '>=', now()->subDays(30))
                ->selectRaw('user_id, count(*) as total')
                ->groupBy('user_id')
                ->pluck('total', 'user_id');

            $frequentRequesters = User::whereIn('id', $requestCounts->filter(fn ($count) => $count >= 3)->keys())
                ->orderBy('name')
                ->get()
                ->map(fn (User $user) => ['user' => $user, 'count' => $requestCounts[$user->id]]);

            return view('livewire.hr-admin.attendance-oversight', [
                'pendingCount' => $pendingCount,
                'requests' => $query->paginate(10),
                'rows' => collect(),
                'requestCounts' => $requestCounts,
                'frequentRequesters' => $frequentRequesters,
            ]);
        }

        $viewDate = Carbon::parse($this->date)->gt(now()) ? now()->toDateString() : $this->date;

        if (Carbon::parse($viewDate)->lte(now())) {
            Attendance::syncAbsencesFor(Carbon::parse($viewDate));
        }

        $employees = User::where('employment_status', 'active')
            ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2->where('name', 'like', "%{$this->search}%")->orWhere('employee_code', 'like', "%{$this->search}%")))
            ->orderBy('name')
            ->paginate(15);

        $attendanceByUser = Attendance::whereIn('user_id', $employees->pluck('id'))
            ->whereDate('work_date', $viewDate)
            ->get()
            ->keyBy('user_id');

        $rows = $employees->through(function (User $user) use ($viewDate, $attendanceByUser) {
            $attendance = $attendanceByUser->get($user->id);

            return [
                'user' => $user,
                'attendance' => $attendance,
                'status' => Attendance::computeStatus($user, $viewDate, $attendance),
            ];
        });

        return view('livewire.hr-admin.attendance-oversight', [
            'pendingCount' => $pendingCount,
            'requests' => collect(),
            'rows' => $rows,
        ]);
    }
}
