<?php

namespace App\Models;

use App\Support\NumberToWords;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [
        'user_id', 'processed_by', 'month', 'year',
        'basic_salary', 'hra', 'da', 'other_allowances',
        'income_tax', 'provident_fund', 'loss_of_pay', 'other_deductions', 'lop_days',
        'gross_salary', 'deductions', 'net_salary', 'status', 'payslip_file',
    ];

    // Mirrors the migration's DB defaults so a `new Payroll()` never carries
    // null into arithmetic or Currency::format() before its first save.
    protected $attributes = [
        'basic_salary' => 0,
        'hra' => 0,
        'da' => 0,
        'other_allowances' => 0,
        'income_tax' => 0,
        'provident_fund' => 0,
        'loss_of_pay' => 0,
        'other_deductions' => 0,
        'lop_days' => 0,
        'gross_salary' => 0,
        'deductions' => 0,
        'net_salary' => 0,
    ];

    protected function casts(): array
    {
        return [
            'basic_salary' => 'decimal:2',
            'hra' => 'decimal:2',
            'da' => 'decimal:2',
            'other_allowances' => 'decimal:2',
            'income_tax' => 'decimal:2',
            'provident_fund' => 'decimal:2',
            'loss_of_pay' => 'decimal:2',
            'other_deductions' => 'decimal:2',
            'gross_salary' => 'decimal:2',
            'deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Recompute gross_salary, deductions and net_salary from the itemized
     * earning and deduction components. Does not save.
     */
    public function recalculateTotals(): void
    {
        $this->gross_salary = $this->basic_salary + $this->hra + $this->da + $this->other_allowances;
        $this->deductions = $this->income_tax + $this->provident_fund + $this->loss_of_pay + $this->other_deductions;
        $this->net_salary = $this->gross_salary - $this->deductions;
    }

    public function paidDays(): int
    {
        $daysInMonth = Carbon::create($this->year, $this->month, 1)->daysInMonth;

        return max(0, $daysInMonth - $this->lop_days);
    }

    public function amountInWords(): string
    {
        return NumberToWords::indianRupees((float) $this->net_salary);
    }
}
