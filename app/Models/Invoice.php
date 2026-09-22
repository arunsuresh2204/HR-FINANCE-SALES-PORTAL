<?php

namespace App\Models;

use App\Support\Currency;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    /**
     * Terminal statuses set once, outside the normal
     * draft -> sent -> partially_paid/paid flow.
     */
    public const CLOSED_STATUSES = ['cancelled', 'credit_note', 'refunded', 'written_off'];

    protected $fillable = [
        'billing_request_id', 'client_id', 'project_id', 'created_by', 'invoice_number', 'line_items',
        'currency', 'amount', 'tax_percent', 'total_amount', 'amount_paid', 'due_date', 'status', 'pdf_path',
        'adjustment_type', 'adjustment_amount', 'adjustment_reason', 'adjustment_at', 'adjusted_by',
        'adjustment_document_number',
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

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
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

    public function editRequests(): HasMany
    {
        return $this->hasMany(InvoiceEditRequest::class);
    }

    public function pendingEditRequest(): HasOne
    {
        return $this->hasOne(InvoiceEditRequest::class)->where('status', 'pending')->latestOfMany();
    }

    public function amountChanges(): HasMany
    {
        return $this->hasMany(InvoiceAmountChange::class);
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

    /**
     * For an INR invoice, amount_paid nets directly against total_amount —
     * same currency, so plain subtraction is correct. For a foreign-currency
     * invoice, amount_paid is a running INR-received figure (see
     * recalculatePaid()) with no stored FX rate to convert it back to the
     * invoice's own currency, so subtracting it here would silently mix
     * currencies. Once closed or explicitly marked paid there's nothing
     * further owed either way; otherwise a foreign invoice's balance due is
     * simply its full native-currency total until Finance marks it paid.
     */
    public function balanceDue(): float
    {
        if ($this->isClosed() || $this->status === 'paid') {
            return 0.0;
        }

        if ($this->currency !== 'INR') {
            return (float) $this->total_amount;
        }

        return max(0.0, (float) $this->total_amount - (float) $this->amount_paid);
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

    /**
     * Once an invoice has actually gone out to the customer, it can no
     * longer be deleted outright — only a draft (never sent) can be.
     */
    public function canBeDeleted(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Cancelling only makes sense for a sent invoice nothing has been paid
     * against yet; once money has moved, use a credit note, refund, or
     * write-off instead.
     */
    public function canBeCancelled(): bool
    {
        return $this->status === 'sent' && (float) $this->amount_paid <= 0;
    }

    /**
     * A credit note records a reduction in what's owed. It only makes
     * sense once an invoice has actually gone out (not a draft) and
     * hasn't already been closed some other way.
     */
    public function canIssueCreditNote(): bool
    {
        return ! $this->isClosed() && $this->status !== 'draft';
    }

    /**
     * A refund gives back money that was actually received, so it only
     * applies once some payment has landed.
     */
    public function canRecordRefund(): bool
    {
        return ! $this->isClosed() && (float) $this->amount_paid > 0;
    }

    /**
     * Writing off is abandoning collection of an outstanding balance, so
     * it doesn't apply to a draft (never sent) or an invoice that's
     * already fully paid - there's nothing left to write off.
     */
    public function canBeWrittenOff(): bool
    {
        return ! $this->isClosed() && $this->status !== 'draft' && $this->balanceDue() > 0.004;
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

    /**
     * The billed project(s), by name. A single-project invoice has
     * `project_id` set directly; one bundled from tasks across several of a
     * client's projects has it null and derives the label from the source
     * billing request instead.
     */
    public function projectsLabel(): ?string
    {
        if ($this->project) {
            return $this->project->name;
        }

        return $this->billingRequest?->projectsLabel();
    }

    /**
     * The next invoice number, numbered within the Indian financial year
     * (April 1 - March 31) it's issued in — e.g. INV-2026-0001 for the
     * first invoice of FY2026-27, resetting to INV-2027-0001 once
     * April 2027 starts a new financial year.
     */
    public static function nextInvoiceNumber(): string
    {
        $today = now();
        $fyStartYear = $today->month >= 4 ? $today->year : $today->year - 1;
        $fyStart = Carbon::create($fyStartYear, 4, 1)->startOfDay();
        $fyEnd = Carbon::create($fyStartYear + 1, 3, 31)->endOfDay();

        $count = static::whereBetween('created_at', [$fyStart, $fyEnd])->count();

        return 'INV-'.$fyStartYear.'-'.str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
    }

    /**
     * The next credit note number, numbered within the Indian financial
     * year it's issued in (CN-2026-0001, ...), independent of the invoice
     * number sequence.
     */
    public static function nextCreditNoteNumber(): string
    {
        return static::nextAdjustmentDocumentNumber('credit_note', 'CN');
    }

    /**
     * The next refund voucher number, same scheme as a credit note but its
     * own sequence (RV-2026-0001, ...).
     */
    public static function nextRefundVoucherNumber(): string
    {
        return static::nextAdjustmentDocumentNumber('refund', 'RV');
    }

    protected static function nextAdjustmentDocumentNumber(string $adjustmentType, string $prefix): string
    {
        $today = now();
        $fyStartYear = $today->month >= 4 ? $today->year : $today->year - 1;
        $fyStart = Carbon::create($fyStartYear, 4, 1)->startOfDay();
        $fyEnd = Carbon::create($fyStartYear + 1, 3, 31)->endOfDay();

        $count = static::where('adjustment_type', $adjustmentType)
            ->whereBetween('adjustment_at', [$fyStart, $fyEnd])
            ->count();

        return $prefix.'-'.$fyStartYear.'-'.str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Splits a credit note's flat adjustment amount into taxable value and
     * GST, backing out the split using the original invoice's tax rate —
     * the credited amount is treated as tax-inclusive, same as the invoice
     * total it reduces. For a zero-rated export invoice (tax_percent = 0)
     * the whole amount is taxable value with no GST component.
     */
    public function creditNoteBreakdown(): array
    {
        $total = (float) $this->adjustment_amount;
        $taxPercent = (float) $this->tax_percent;
        $taxable = $taxPercent > 0 ? $total / (1 + $taxPercent / 100) : $total;

        return [
            'taxable' => round($taxable, 2),
            'tax' => round($total - $taxable, 2),
            'total' => $total,
        ];
    }
}
