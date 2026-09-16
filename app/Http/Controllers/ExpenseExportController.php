<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesExportDateRange;
use App\Models\Expense;
use App\Models\OperationalExpense;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseExportController extends Controller
{
    use ValidatesExportDateRange;

    public function csv(Request $request): StreamedResponse
    {
        [$from, $to] = $this->dateRange($request);

        $employeeExpenses = Expense::with('user')
            ->where('status', 'approved')
            ->whereBetween('expense_date', [$from, $to])
            ->get()
            ->map(fn (Expense $e) => [
                'date' => $e->expense_date,
                'type' => 'Employee Reimbursement',
                'category' => $e->category,
                'paid_to' => $e->user->name,
                'description' => $e->description,
                'amount' => (float) $e->amount,
                'status' => 'Approved',
            ]);

        $operationalExpenses = OperationalExpense::with('category')
            ->whereBetween('expense_date', [$from, $to])
            ->get()
            ->map(fn (OperationalExpense $e) => [
                'date' => $e->expense_date,
                'type' => 'Operational',
                'category' => $e->category->name,
                'paid_to' => $e->vendor,
                'description' => $e->notes,
                'amount' => (float) $e->amount,
                'status' => 'Recorded',
            ]);

        $rows = $employeeExpenses->concat($operationalExpenses)->sortBy('date')->values();

        $filename = 'expenses-'.$from->format('Y-m-d').'-to-'.$to->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['Date', 'Type', 'Category', 'Paid To', 'Description', 'Amount (INR)', 'Status']);

            foreach ($rows as $row) {
                fputcsv($out, [
                    $row['date']->format('Y-m-d'),
                    $row['type'],
                    $row['category'],
                    $row['paid_to'],
                    $row['description'],
                    number_format($row['amount'], 2, '.', ''),
                    $row['status'],
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
