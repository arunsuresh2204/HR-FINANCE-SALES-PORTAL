<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ValidatesExportDateRange;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayrollExportController extends Controller
{
    use ValidatesExportDateRange;

    public function csv(Request $request): StreamedResponse
    {
        [$from, $to] = $this->dateRange($request);

        $fromPeriod = $from->year * 100 + $from->month;
        $toPeriod = $to->year * 100 + $to->month;

        $payrolls = Payroll::with('user')
            ->where('status', '!=', 'draft')
            ->whereRaw('(year * 100 + month) BETWEEN ? AND ?', [$fromPeriod, $toPeriod])
            ->orderBy('year')->orderBy('month')
            ->get();

        $filename = 'payroll-'.$from->format('Y-m').'-to-'.$to->format('Y-m').'.csv';

        return response()->streamDownload(function () use ($payrolls) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'Employee Code', 'Employee Name', 'Month', 'Year',
                'Basic Salary', 'HRA', 'DA', 'Other Allowances', 'Gross Salary',
                'Income Tax', 'Provident Fund', 'Loss of Pay', 'Other Deductions', 'Total Deductions',
                'Net Salary', 'LOP Days', 'Status',
            ]);

            foreach ($payrolls as $payroll) {
                fputcsv($out, [
                    $payroll->user->employee_code,
                    $payroll->user->name,
                    $payroll->month,
                    $payroll->year,
                    number_format((float) $payroll->basic_salary, 2, '.', ''),
                    number_format((float) $payroll->hra, 2, '.', ''),
                    number_format((float) $payroll->da, 2, '.', ''),
                    number_format((float) $payroll->other_allowances, 2, '.', ''),
                    number_format((float) $payroll->gross_salary, 2, '.', ''),
                    number_format((float) $payroll->income_tax, 2, '.', ''),
                    number_format((float) $payroll->provident_fund, 2, '.', ''),
                    number_format((float) $payroll->loss_of_pay, 2, '.', ''),
                    number_format((float) $payroll->other_deductions, 2, '.', ''),
                    number_format((float) $payroll->deductions, 2, '.', ''),
                    number_format((float) $payroll->net_salary, 2, '.', ''),
                    $payroll->lop_days,
                    ucfirst($payroll->status),
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
