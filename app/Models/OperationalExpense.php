<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperationalExpense extends Model
{
    protected $fillable = [
        'category_id', 'vendor', 'amount', 'expense_date', 'is_recurring', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'amount' => 'decimal:2',
            'is_recurring' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(OperationalExpenseCategory::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
