<?php

namespace App\Livewire;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\BillingRequest;
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
    public int $salesMonth;

    public int $salesYear;

    public function mount(): void
    {
        $this->salesMonth = now()->month;
        $this->salesYear = now()->year;
    }

    public function render()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $data = [
            'todayAttendance' => Attendance::where('user_id', $user->id)->whereDate('work_date', $today)->first(),
            'pendingLeave' => LeaveRequest::where('user_id', $user->id)->where('status', 'pending')->count(),
            'approvedLeaveDaysThisYear' => LeaveRequest::where('user_id', $user->id)->where('status', 'approved')->whereYear('start_date', now()->year)->sum('days'),
            'pendingExpenses' => Expense::where('user_id', $user->id)->where('status', 'pending')->count(),
            'announcements' => Announcement::latest()->limit(3)->get(),
        ];

        if ($user->can('access_timesheets')) {
            $data['todayHours'] = Timesheet::where('user_id', $user->id)->whereDate('work_date', $today)->sum('hours');
            $data['weekHours'] = Timesheet::where('user_id', $user->id)->whereBetween('work_date', [now()->startOfWeek(), now()->endOfWeek()])->sum('hours');
        }

        if ($user->can('access_marketing_logs')) {
            $data['todayMarketingHours'] = MarketingLog::where('user_id', $user->id)->whereDate('work_date', $today)->sum('hours');
        }

        if ($user->can('access_sales_leads')) {
            $data['myLeadsOpen'] = Lead::where('sales_person_id', $user->id)->whereNotIn('status', Lead::CLOSED_STATUSES)->count();
            $data['myLeadsWonThisMonth'] = Lead::where('sales_person_id', $user->id)->where('status', 'won')->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year)->count();
        }

        if ($user->isSalesPerson()) {
            $target = SalesTarget::where('user_id', $user->id)->where('month', $this->salesMonth)->where('year', $this->salesYear)->first();

            $data['salesTarget'] = $target;
            $data['salesTargetAmount'] = $target?->effectiveTargetAmount() ?? 0;
            $data['salesAchieved'] = $target
                ? $target->achievedAmount()
                : (new SalesTarget(['user_id' => $user->id, 'month' => $this->salesMonth, 'year' => $this->salesYear]))->achievedAmount();
        }

        if ($user->can('access_hr_admin')) {
            $data['headcount'] = User::where('employment_status', 'active')->count();
            $data['pendingLeaveApprovals'] = LeaveRequest::where('status', 'pending')->count();
            $data['pendingResignations'] = Resignation::where('status', 'pending')->count();
        }

        if ($user->can('access_finance_admin')) {
            $data['pendingBillingRequests'] = BillingRequest::where('status', 'pending')->count();
            $data['outstandingInvoices'] = Invoice::whereIn('status', ['sent', 'partially_paid', 'overdue'])->get()->sum(fn ($i) => $i->balanceDue());
            $data['pendingExpenseApprovals'] = Expense::where('status', 'pending')->count();
            $data['revenueThisMonth'] = Invoice::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount_paid');
        }

        if ($user->isSuperAdmin()) {
            $data['totalClients'] = Client::count();
            $data['openLeads'] = Lead::whereNotIn('status', Lead::CLOSED_STATUSES)->count();
            $data['totalRevenue'] = Invoice::sum('amount_paid');
        }

        return view('livewire.dashboard', $data);
    }
}
