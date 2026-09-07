<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [
        'user_id', 'processed_by', 'month', 'year', 'gross_salary', 'deductions',
        'net_salary', 'status', 'payslip_file',
    ];

    protected function casts(): array
    {
        return [
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
}
