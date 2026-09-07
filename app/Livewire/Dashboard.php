<?php

namespace App\Livewire;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\LeaveRequest;
use App\Models\MarketingLog;
use App\Models\Resignation;
use App\Models\SalesTarget;
use App\Models\Timesheet;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $data = [
            'todayAttendance' => Attendance::where('user_id', $user->id)->where('work_date', $today)->first(),
            'pendingLeave' => LeaveRequest::where('user_id', $user->id)->where('status', 'pending')->count(),
            'approvedLeaveDaysThisYear' => LeaveRequest::where('user_id', $user->id)->where('status', 'approved')->whereYear('start_date', now()->year)->sum('days'),
            'pendingExpenses' => Expense::where('user_id', $user->id)->where('status', 'pending')->count(),
            'announcements' => Announcement::latest()->limit(3)->get(),
        ];

        if ($user->isProgrammer()) {
            $data['todayHours'] = Timesheet::where('user_id', $user->id)->where('work_date', $today)->sum('hours');
            $data['weekHours'] = Timesheet::where('user_id', $user->id)->whereBetween('work_date', [now()->startOfWeek(), now()->endOfWeek()])->sum('hours');
        }

        if ($user->isMarketer()) {
            $data['todayMarketingHours'] = MarketingLog::where('user_id', $user->id)->where('work_date', $today)->sum('hours');
        }

        if ($user->isSalesExec()) {
            $data['myLeadsOpen'] = Lead::where('sales_person_id', $user->id)->whereNotIn('status', ['won', 'lost'])->count();
            $data['myLeadsWonThisMonth'] = Lead::where('sales_person_id', $user->id)->where('status', 'won')->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year)->count();
            $target = SalesTarget::where('user_id', $user->id)->where('month', now()->month)->where('year', now()->year)->first();
            $data['salesTarget'] = $target;
            $data['salesAchieved'] = $target?->achievedAmount() ?? 0;
        }

        if ($user->isHrAdmin()) {
            $data['headcount'] = User::where('employment_status', 'active')->count();
            $data['pendingLeaveApprovals'] = LeaveRequest::where('status', 'pending')->count();
            $data['pendingResignations'] = Resignation::where('status', 'pending')->count();
        }

        if ($user->isFinanceAdmin()) {
            $data['pendingBillingRequests'] = \App\Models\BillingRequest::where('status', 'pending')->count();
            $data['outstandingInvoices'] = Invoice::whereIn('status', ['sent', 'partially_paid', 'overdue'])->get()->sum(fn ($i) => $i->balanceDue());
            $data['pendingExpenseApprovals'] = Expense::where('status', 'pending')->count();
            $data['revenueThisMonth'] = Invoice::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount_paid');
        }

        if ($user->isSuperAdmin()) {
            $data['totalClients'] = Client::count();
            $data['openLeads'] = Lead::whereNotIn('status', ['won', 'lost'])->count();
            $data['totalRevenue'] = Invoice::sum('amount_paid');
        }

        return view('livewire.dashboard', $data);
    }
}
