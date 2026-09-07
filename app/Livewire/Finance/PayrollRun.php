<?php

namespace App\Livewire\Finance;

use App\Models\Payroll;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PayrollRun extends Component
{
    public int $month;

    public int $year;

    public array $deductions = [];

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    public function updatedMonth(): void
    {
        $this->deductions = [];
    }

    public function updatedYear(): void
    {
        $this->deductions = [];
    }

    public function generatePayroll(): void
    {
        $employees = User::where('employment_status', '!=', 'offboarded')->whereNotNull('monthly_salary')->get();

        foreach ($employees as $employee) {
            Payroll::firstOrCreate(
                ['user_id' => $employee->id, 'month' => $this->month, 'year' => $this->year],
                [
                    'processed_by' => Auth::id(),
                    'gross_salary' => $employee->monthly_salary,
                    'deductions' => 0,
                    'net_salary' => $employee->monthly_salary,
                    'status' => 'draft',
                ]
            );
        }

        $this->dispatch('toast', message: 'Payroll draft generated for '.count($employees).' employees.', type: 'success');
    }

    public function updateDeduction(int $payrollId, string $value): void
    {
        $payroll = Payroll::find($payrollId);
        if (! $payroll || $payroll->status !== 'draft') {
            return;
        }

        $deduction = max(0, (float) $value);
        $payroll->update([
            'deductions' => $deduction,
            'net_salary' => $payroll->gross_salary - $deduction,
        ]);
    }

    public function process(Payroll $payroll): void
    {
        $pdf = Pdf::loadView('pdf.payslip', ['payroll' => $payroll->load('user')]);
        $path = 'payslips/payslip-'.$payroll->id.'.pdf';
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $pdf->output());

        $payroll->update(['status' => 'processed', 'payslip_file' => $path, 'processed_by' => Auth::id()]);
        $this->dispatch('toast', message: 'Payslip generated.', type: 'success');
    }

    public function markPaid(Payroll $payroll): void
    {
        $payroll->update(['status' => 'paid']);
        $this->dispatch('toast', message: 'Marked as paid.', type: 'success');
    }

    public function render()
    {
        return view('livewire.finance.payroll-run', [
            'payrolls' => Payroll::with('user')->where('month', $this->month)->where('year', $this->year)->get()->sortBy('user.name'),
        ]);
    }
}
