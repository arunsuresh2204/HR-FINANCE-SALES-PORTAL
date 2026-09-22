<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceAdjustment extends Model
{
    protected $fillable = [
        'invoice_id', 'type', 'amount', 'currency', 'reason', 'document_number', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function money(): string
    {
        return \App\Support\Currency::format($this->amount, $this->currency);
    }

    public function label(): string
    {
        return match ($this->type) {
            'credit_note' => 'Credit Note',
            'refund' => 'Refund',
            'written_off' => 'Written Off',
            default => str($this->type)->replace('_', ' ')->title(),
        };
    }

    /**
     * Splits a credit note's flat amount into taxable value and GST,
     * backing out the split using the parent invoice's tax rate — the
     * credited amount is treated as tax-inclusive, same as the invoice
     * total it reduces. For a zero-rated export invoice (tax_percent = 0)
     * the whole amount is taxable value with no GST component.
     */
    public function taxBreakdown(): array
    {
        $total = (float) $this->amount;
        $taxPercent = (float) $this->invoice->tax_percent;
        $taxable = $taxPercent > 0 ? $total / (1 + $taxPercent / 100) : $total;

        return [
            'taxable' => round($taxable, 2),
            'tax' => round($total - $taxable, 2),
            'total' => $total,
        ];
    }

    /**
     * The next credit note number, numbered within the Indian financial
     * year it's issued in (CN-2026-0001, ...), independent of the invoice
     * number sequence and shared across every invoice — not per invoice.
     */
    public static function nextCreditNoteNumber(): string
    {
        return static::nextDocumentNumber('credit_note', 'CN');
    }

    /**
     * The next refund voucher number, same scheme as a credit note but its
     * own sequence (RV-2026-0001, ...).
     */
    public static function nextRefundVoucherNumber(): string
    {
        return static::nextDocumentNumber('refund', 'RV');
    }

    protected static function nextDocumentNumber(string $type, string $prefix): string
    {
        $today = now();
        $fyStartYear = $today->month >= 4 ? $today->year : $today->year - 1;
        $fyStart = Carbon::create($fyStartYear, 4, 1)->startOfDay();
        $fyEnd = Carbon::create($fyStartYear + 1, 3, 31)->endOfDay();

        $count = static::where('type', $type)
            ->whereBetween('created_at', [$fyStart, $fyEnd])
            ->count();

        return $prefix.'-'.$fyStartYear.'-'.str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
    }
}
