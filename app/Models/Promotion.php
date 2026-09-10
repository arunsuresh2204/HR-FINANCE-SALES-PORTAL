<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promotion extends Model
{
    public const TYPE_PROMOTION = 'promotion';

    public const TYPE_SALARY_HIKE = 'salary_hike';

    protected $fillable = [
        'user_id', 'created_by', 'type', 'previous_designation', 'new_designation',
        'previous_department', 'new_department', 'previous_salary', 'new_salary',
        'effective_date', 'notes', 'certificate_path',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'previous_salary' => 'decimal:2',
            'new_salary' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isSalaryHikeOnly(): bool
    {
        return $this->type === self::TYPE_SALARY_HIKE;
    }

    public function hasSalaryHike(): bool
    {
        return $this->previous_salary !== null && $this->new_salary !== null;
    }

    public function hikeAmount(): ?float
    {
        return $this->hasSalaryHike() ? (float) $this->new_salary - (float) $this->previous_salary : null;
    }

    public function hikePercent(): ?float
    {
        if (! $this->hasSalaryHike() || (float) $this->previous_salary <= 0) {
            return null;
        }

        return ($this->hikeAmount() / (float) $this->previous_salary) * 100;
    }
}
