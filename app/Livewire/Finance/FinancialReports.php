<?php

namespace App\Livewire\Finance;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payroll;
use Livewire\Component;

class FinancialReports extends Component
{
    public function render()
    {
        $revenueByClient = Client::withSum('invoices as revenue', 'amount_paid')
            ->get()
            ->filter(fn ($client) => $client->revenue > 0)
            ->sortByDesc('revenue')
            ->values();

        $revenueThisMonth = Invoice::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount_paid');
        $expensesThisMonth = Expense::where('status', 'approved')->whereMonth('expense_date', now()->month)->whereYear('expense_date', now()->year)->sum('amount');
        $payrollThisMonth = Payroll::where('month', now()->month)->where('year', now()->year)->sum('net_salary');

        $agingInvoices = Invoice::whereIn('status', ['sent', 'partially_paid', 'overdue'])->with('client')->get();

        $wonDealsValue = Lead::where('status', 'won')->sum('budget');
        $invoicedValue = Invoice::sum('total_amount');

        $recentMonths = collect(range(0, 2))->map(function (int $i) {
            $period = now()->subMonthsNoOverflow($i)->startOfMonth();

            return [
                'year' => $period->year,
                'month' => $period->month,
                'label' => $period->format('F Y'),
                'revenue' => Invoice::whereMonth('created_at', $period->month)->whereYear('created_at', $period->year)->sum('amount_paid'),
                'net' => Invoice::whereMonth('created_at', $period->month)->whereYear('created_at', $period->year)->sum('amount_paid')
                    - Expense::where('status', 'approved')->whereMonth('expense_date', $period->month)->whereYear('expense_date', $period->year)->sum('amount')
                    - Payroll::where('month', $period->month)->where('year', $period->year)->sum('net_salary'),
            ];
        });

        return view('livewire.finance.financial-reports', [
            'revenueByClient' => $revenueByClient,
            'revenueThisMonth' => $revenueThisMonth,
            'expensesThisMonth' => $expensesThisMonth,
            'payrollThisMonth' => $payrollThisMonth,
            'netThisMonth' => $revenueThisMonth - $expensesThisMonth - $payrollThisMonth,
            'agingInvoices' => $agingInvoices,
            'totalOutstanding' => $agingInvoices->sum(fn ($i) => $i->balanceDue()),
            'wonDealsValue' => $wonDealsValue,
            'invoicedValue' => $invoicedValue,
            'recentMonths' => $recentMonths,
        ]);
    }
}
