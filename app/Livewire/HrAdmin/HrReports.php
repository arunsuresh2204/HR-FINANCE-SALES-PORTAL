<?php

namespace App\Livewire\HrAdmin;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use Livewire\Component;

class HrReports extends Component
{
    public function render()
    {
        $byDepartment = User::selectRaw('department, count(*) as total')
            ->whereNotNull('department')
            ->groupBy('department')
            ->pluck('total', 'department');

        $leaveByType = LeaveRequest::where('status', 'approved')
            ->whereYear('start_date', now()->year)
            ->selectRaw('type, sum(days) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $attendanceThisMonth = Attendance::whereMonth('work_date', now()->month)
            ->whereYear('work_date', now()->year)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('livewire.hr-admin.hr-reports', [
            'headcount' => User::count(),
            'activeCount' => User::where('employment_status', 'active')->count(),
            'onNoticeCount' => User::where('employment_status', 'on_notice')->count(),
            'byDepartment' => $byDepartment,
            'leaveByType' => $leaveByType,
            'attendanceThisMonth' => $attendanceThisMonth,
            'pendingLeave' => LeaveRequest::where('status', 'pending')->count(),
        ]);
    }
}
