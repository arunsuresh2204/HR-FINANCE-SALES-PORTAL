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
        ]);
    }
}
