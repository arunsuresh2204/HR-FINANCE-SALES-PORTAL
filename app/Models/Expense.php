<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'user_id', 'amount', 'category', 'description', 'expense_date', 'receipt_file',
        'status', 'reviewed_by', 'reviewed_at', 'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'amount' => 'decimal:2',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
