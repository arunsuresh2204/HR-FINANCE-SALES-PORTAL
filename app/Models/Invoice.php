<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'billing_request_id', 'client_id', 'created_by', 'invoice_number', 'line_items',
        'amount', 'tax_percent', 'total_amount', 'amount_paid', 'due_date', 'status', 'pdf_path',
    ];

    protected function casts(): array
    {
        return [
            'line_items' => 'array',
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'tax_percent' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function billingRequest(): BelongsTo
    {
        return $this->belongsTo(BillingRequest::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function balanceDue(): float
    {
        return (float) $this->total_amount - (float) $this->amount_paid;
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'paid';
    }
}
