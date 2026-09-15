<?php

namespace App\Models;

use App\Support\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    /**
     * Terminal statuses set once, outside the normal
     * draft -> sent -> partially_paid/paid flow.
     */
    public const CLOSED_STATUSES = ['cancelled', 'credit_note', 'refunded', 'written_off'];

    protected $fillable = [
        'billing_request_id', 'client_id', 'created_by', 'invoice_number', 'line_items',
        'currency', 'amount', 'tax_percent', 'total_amount', 'amount_paid', 'due_date', 'status', 'pdf_path',
        'adjustment_type', 'adjustment_amount', 'adjustment_reason', 'adjustment_at', 'adjusted_by',
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
            'adjustment_amount' => 'decimal:2',
            'adjustment_at' => 'datetime',
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

    public function adjustedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * amount_paid is always INR (what actually landed in the bank), summed
     * from the payments ledger. For an INR invoice this is directly
     * comparable to total_amount; for a foreign-currency invoice it's a
     * running received-in-INR figure, so paid/partially_paid there is set
     * explicitly rather than derived by subtraction.
     */
    public function recalculatePaid(): void
    {
        $paid = (float) $this->payments()->sum('amount');
        $status = $this->status;

        if (! in_array($status, self::CLOSED_STATUSES, true)) {
            if ($this->currency === 'INR') {
                $status = $paid >= (float) $this->total_amount ? 'paid' : ($paid > 0 ? 'partially_paid' : $status);
            } elseif ($paid > 0 && $status !== 'paid') {
                $status = 'partially_paid';
            }
        }

        $this->update(['amount_paid' => $paid, 'status' => $status]);
    }

    public function balanceDue(): float
    {
        return (float) $this->total_amount - (float) $this->amount_paid;
    }

    public function isClosed(): bool
    {
        return in_array($this->status, self::CLOSED_STATUSES, true);
    }

    /**
     * Invoices can be edited or deleted right up until money has actually
     * landed in full — i.e. anything before the terminal 'paid' status or
     * a closed (cancelled/credit-noted/refunded/written-off) state.
     */
    public function isEditable(): bool
    {
        return ! $this->isClosed() && $this->status !== 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && ! in_array($this->status, ['paid', ...self::CLOSED_STATUSES], true);
    }

    public function isExport(): bool
    {
        return $this->currency !== 'INR';
    }

    public function taxLabel(): string
    {
        return 'GST';
    }

    public function clientTaxIdLabel(): string
    {
        return match ($this->currency) {
            'INR' => 'GSTIN',
            'EUR' => 'VAT',
            default => 'Tax ID',
        };
    }

    public function money(float|string $amount): string
    {
        return Currency::format($amount, $this->currency ?? 'INR');
    }
}
