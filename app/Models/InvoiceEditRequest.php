<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceEditRequest extends Model
{
    protected $fillable = [
        'invoice_id', 'requested_by', 'line_items', 'amount', 'tax_percent', 'total_amount',
        'due_date', 'status', 'review_notes', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'line_items' => 'array',
            'amount' => 'decimal:2',
            'tax_percent' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'due_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
