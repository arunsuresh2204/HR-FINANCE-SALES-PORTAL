<?php

namespace App\Livewire\Finance;

use App\Models\Payroll;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

class PayrollRun extends Component
{
    public int $month;

    public int $year;

    public ?int $editingPayrollId = null;

    public bool $showEditForm = false;

    #[Validate('required|numeric|min:0')]
    public string $basic_salary = '0';

    #[Validate('required|numeric|min:0')]
    public string $hra = '0';

    #[Validate('required|numeric|min:0')]
    public string $da = '0';

    #[Validate('required|numeric|min:0')]
    public string $other_allowances = '0';

    #[Validate('required|numeric|min:0')]
    public string $income_tax = '0';

    #[Validate('required|numeric|min:0')]
    public string $provident_fund = '0';

    #[Validate('required|numeric|min:0')]
    public string $loss_of_pay = '0';

    #[Validate('required|numeric|min:0')]
    public string $other_deductions = '0';

    #[Validate('required|integer|min:0|max:31')]
    public string $lop_days = '0';

    public function mount(): void
    {
        $this->month = now()->month;
        $this->year = now()->year;
    }

    public function generatePayroll(): void
    {
        $employees = User::where('employment_status', '!=', 'offboarded')->whereNotNull('monthly_salary')->get();

        foreach ($employees as $employee) {
            $payroll = Payroll::firstOrNew(['user_id' => $employee->id, 'month' => $this->month, 'year' => $this->year]);

            if ($payroll->exists) {
                continue;
            }

            $payroll->fill([
                'processed_by' => Auth::id(),
                'basic_salary' => $employee->monthly_salary,
                'status' => 'draft',
            ]);
            $payroll->recalculateTotals();
            $payroll->save();
        }

        $this->dispatch('toast', message: 'Payroll draft generated for '.count($employees).' employees.', type: 'success');
    }

    public function openEditForm(int $payrollId): void
    {
        $payroll = Payroll::find($payrollId);

        if (! $payroll || $payroll->status !== 'draft') {
            return;
        }

        $this->editingPayrollId = $payrollId;
        $this->basic_salary = (string) $payroll->basic_salary;
        $this->hra = (string) $payroll->hra;
        $this->da = (string) $payroll->da;
        $this->other_allowances = (string) $payroll->other_allowances;
        $this->income_tax = (string) $payroll->income_tax;
        $this->provident_fund = (string) $payroll->provident_fund;
        $this->loss_of_pay = (string) $payroll->loss_of_pay;
        $this->other_deductions = (string) $payroll->other_deductions;
        $this->lop_days = (string) $payroll->lop_days;
        $this->resetValidation();
        $this->showEditForm = true;
    }

    public function cancelEdit(): void
    {
        $this->reset([
            'editingPayrollId', 'showEditForm', 'basic_salary', 'hra', 'da', 'other_allowances',
            'income_tax', 'provident_fund', 'loss_of_pay', 'other_deductions', 'lop_days',
        ]);
    }

    public function saveEdit(): void
    {
        $this->validate();

        $payroll = Payroll::find($this->editingPayrollId);

        if (! $payroll || $payroll->status !== 'draft') {
            $this->dispatch('toast', message: 'This payslip can no longer be edited.', type: 'error');

            return;
        }

        $payroll->fill([
            'basic_salary' => $this->basic_salary,
            'hra' => $this->hra,
            'da' => $this->da,
            'other_allowances' => $this->other_allowances,
            'income_tax' => $this->income_tax,
            'provident_fund' => $this->provident_fund,
            'loss_of_pay' => $this->loss_of_pay,
            'other_deductions' => $this->other_deductions,
            'lop_days' => $this->lop_days,
        ]);
        $payroll->recalculateTotals();
        $payroll->save();

        $this->cancelEdit();
        $this->dispatch('toast', message: 'Payslip components updated.', type: 'success');
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
