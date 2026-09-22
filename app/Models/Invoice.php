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
        'currency', 'amount', 'tax_percent', 'total_amount', 'amount_paid', 'native_amount_settled',
        'due_date', 'status', 'pdf_path',
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
            'native_amount_settled' => 'decimal:2',
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

    /**
     * Every credit note, refund, and write-off ever issued against this
     * invoice, oldest first — an invoice can carry more than one over its
     * life (e.g. a partial credit note now, a further write-off later),
     * unlike the single adjustment slot this replaced.
     */
    public function adjustments(): HasMany
    {
        return $this->hasMany(InvoiceAdjustment::class)->oldest();
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
     *
     * native_amount_settled is the companion figure for a foreign-currency
     * invoice: how much of its own total has actually been settled,
     * entered directly by Finance per payment/refund since there's no
     * stored FX rate to derive it from the INR figure above.
     */
    public function recalculatePaid(): void
    {
        $paid = (float) $this->payments()->sum('amount');
        $nativeSettled = (float) $this->payments->sum('native_amount')
            - (float) $this->adjustments->where('type', 'refund')->sum('native_amount');
        $status = $this->status;

        if (! in_array($status, self::CLOSED_STATUSES, true)) {
            if ($this->currency === 'INR') {
                $status = $paid >= (float) $this->total_amount ? 'paid' : ($paid > 0 ? 'partially_paid' : $status);
            } elseif ($paid > 0 && $status !== 'paid') {
                $status = 'partially_paid';
            }
        }

        $this->update(['amount_paid' => $paid, 'native_amount_settled' => $nativeSettled, 'status' => $status]);
    }

    /**
     * Credit notes and write-offs both reduce what's actually owed, in the
     * invoice's own currency (unlike a refund, which is cash paid back and
     * doesn't change what was owed).
     */
    public function totalCreditedOrWrittenOff(): float
    {
        return $this->totalCredited() + $this->totalWrittenOff();
    }

    public function totalCredited(): float
    {
        return (float) $this->adjustments->where('type', 'credit_note')->sum('amount');
    }

    public function totalWrittenOff(): float
    {
        return (float) $this->adjustments->where('type', 'written_off')->sum('amount');
    }

    public function totalRefunded(): float
    {
        return (float) $this->adjustments->where('type', 'refund')->sum('amount');
    }

    public function hasAdjustments(): bool
    {
        return $this->adjustments->isNotEmpty();
    }

    /**
     * For an INR invoice, amount_paid nets directly against the remaining
     * (post-credit/write-off) total — same currency, so plain subtraction
     * is correct, and it already reflects any refund since a refund is
     * recorded as a negative entry in the payments ledger amount_paid sums.
     * For a foreign-currency invoice, amount_paid (always INR) can't be
     * netted the same way — native_amount_settled is the figure Finance
     * entered directly in the invoice's own currency for that purpose (see
     * recalculatePaid()); it's 0 until they start entering it, so an older
     * partial payment recorded before this existed won't net here until
     * it's re-entered. Cancelled or explicitly marked paid always means
     * nothing further is owed, regardless of the numbers above.
     */
    public function balanceDue(): float
    {
        if (in_array($this->status, ['cancelled', 'paid'], true)) {
            return 0.0;
        }

        $remaining = (float) $this->total_amount - $this->totalCreditedOrWrittenOff();

        if ($this->currency !== 'INR') {
            return max(0.0, $remaining - (float) $this->native_amount_settled);
        }

        return max(0.0, $remaining - (float) $this->amount_paid);
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
     * sense once an invoice has actually gone out (not a draft, not
     * cancelled) and there's still something left to credit — a second
     * credit note is fine as long as it, together with any write-off,
     * hasn't already covered the full original total. Whether the invoice
     * has since been fully paid or refunded doesn't block it: crediting an
     * already-paid invoice is a normal precursor to then refunding that
     * amount, not a contradiction.
     */
    public function canIssueCreditNote(): bool
    {
        return $this->status !== 'cancelled'
            && $this->status !== 'draft'
            && $this->totalCreditedOrWrittenOff() < (float) $this->total_amount - 0.004;
    }

    /**
     * A refund gives back money that was actually received, so it only
     * applies while there's still received cash on the books that hasn't
     * already been refunded — amount_paid already nets out any prior
     * refund (each one is a negative entry in the payments ledger), so
     * this naturally allows a second, smaller refund and blocks a further
     * one once fully refunded. Issuing a credit note first doesn't block
     * this — crediting what's owed and refunding cash already received are
     * independent actions that commonly happen together.
     */
    public function canRecordRefund(): bool
    {
        return $this->status !== 'cancelled' && (float) $this->amount_paid > 0;
    }

    /**
     * Writing off is abandoning collection of an outstanding balance, so
     * it doesn't apply to a draft (never sent), a cancelled invoice, or
     * one with nothing left owing — including a balance already brought to
     * zero by a prior credit note or payment.
     */
    public function canBeWrittenOff(): bool
    {
        return $this->status !== 'cancelled' && $this->status !== 'draft' && $this->balanceDue() > 0.004;
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

}
