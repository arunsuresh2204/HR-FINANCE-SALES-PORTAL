<?php

namespace App\Livewire\HrAdmin;

use App\Models\Attendance;
use App\Models\AttendanceStatusRequest;
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
        $attendanceStatusRequest->attendance->update(['status' => $attendanceStatusRequest->requested_status]);

        $attendanceStatusRequest->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $this->dispatch('toast', message: 'Request approved and attendance status updated.', type: 'success');
    }

    public function rejectRequest(AttendanceStatusRequest $attendanceStatusRequest): void
    {
        $attendanceStatusRequest->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

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

            return view('livewire.hr-admin.attendance-oversight', [
                'pendingCount' => $pendingCount,
                'requests' => $query->paginate(10),
                'rows' => collect(),
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
