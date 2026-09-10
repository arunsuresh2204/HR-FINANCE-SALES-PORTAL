<?php

namespace App\Livewire\Finance;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payroll;
use App\Models\SalesTarget;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class FinancialReportMonth extends Component
{
    public int $year;

    public int $month;

    public function mount(int $year, int $month): void
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function render()
    {
        $period = Carbon::create($this->year, $this->month, 1);

        $revenue = Invoice::whereMonth('created_at', $this->month)->whereYear('created_at', $this->year)->sum('amount_paid');
        $expenses = Expense::where('status', 'approved')->whereMonth('expense_date', $this->month)->whereYear('expense_date', $this->year)->sum('amount');
        $payroll = Payroll::where('month', $this->month)->where('year', $this->year)->sum('net_salary');
        $net = $revenue - $expenses - $payroll;

        $revenueByClient = Client::withSum(['invoices as revenue' => function ($q) {
            $q->whereMonth('created_at', $this->month)->whereYear('created_at', $this->year);
        }], 'amount_paid')
            ->get()
            ->filter(fn ($client) => $client->revenue > 0)
            ->sortByDesc('revenue')
            ->values();

        $invoicesThisMonth = Invoice::with('client')
            ->whereMonth('created_at', $this->month)
            ->whereYear('created_at', $this->year)
            ->latest('created_at')
            ->get();

        $wonDealsValue = Lead::where('status', 'won')->whereYear('updated_at', $this->year)->whereMonth('updated_at', $this->month)->sum('budget');
        $invoicedValue = Invoice::whereMonth('created_at', $this->month)->whereYear('created_at', $this->year)->sum('total_amount');

        $salesAchievements = User::role('sales_exec')->orderBy('name')->get()->map(function (User $sp) {
            $target = SalesTarget::where('user_id', $sp->id)->where('month', $this->month)->where('year', $this->year)->first();
            $achieved = (float) ($target?->achievedAmount() ?? Lead::where('sales_person_id', $sp->id)
                ->where('status', 'won')
                ->whereYear('updated_at', $this->year)
                ->whereMonth('updated_at', $this->month)
                ->sum('budget'));
            $effectiveTarget = (float) ($target?->effectiveTargetAmount() ?? 0);

            return [
                'user' => $sp,
                'target' => $effectiveTarget,
                'achieved' => $achieved,
                'achievementPct' => $effectiveTarget > 0 ? round($achieved / $effectiveTarget * 100) : null,
                'commissionPercent' => (float) ($target?->commission_percent ?? 0),
                'commissionEarned' => $achieved * (float) ($target?->commission_percent ?? 0) / 100,
                'dealsWon' => Lead::where('sales_person_id', $sp->id)
                    ->where('status', 'won')
                    ->whereYear('updated_at', $this->year)
                    ->whereMonth('updated_at', $this->month)
                    ->count(),
            ];
        })->sortByDesc('achieved')->values();

        return view('livewire.finance.financial-report-month', [
            'period' => $period,
            'revenue' => $revenue,
            'expenses' => $expenses,
            'payroll' => $payroll,
            'net' => $net,
            'revenueByClient' => $revenueByClient,
            'invoicesThisMonth' => $invoicesThisMonth,
            'wonDealsValue' => $wonDealsValue,
            'invoicedValue' => $invoicedValue,
            'salesAchievements' => $salesAchievements,
        ]);
    }
}
