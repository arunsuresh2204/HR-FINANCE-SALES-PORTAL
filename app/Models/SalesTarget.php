<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesTarget extends Model
{
    protected $fillable = ['user_id', 'month', 'year', 'target_amount', 'commission_percent'];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'commission_percent' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function achievedAmount(): float
    {
        return (float) Lead::query()
            ->where('sales_person_id', $this->user_id)
            ->where('status', 'won')
            ->whereYear('updated_at', $this->year)
            ->whereMonth('updated_at', $this->month)
            ->sum('budget');
    }
}
