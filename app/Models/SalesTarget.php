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

    /**
     * Real revenue collected this month for this salesperson's clients, in
     * INR: the sum of the payments ledger (a refund posts as a negative
     * payment, so it nets out of the month it lands in).
     */
    public function achievedAmount(): float
    {
        return self::achievedAmountFor($this->user_id, $this->month, $this->year);
    }

    /**
     * Same figure as achievedAmount(), but computed directly from the
     * payments ledger without needing a persisted SalesTarget row for the
     * month — a salesperson's collected revenue exists independently of
     * whether anyone has set a target for them yet, so callers must not
     * skip this just because SalesTarget::where(...)->first() came back
     * null.
     */
    public static function achievedAmountFor(int $userId, int $month, int $year): float
    {
        return (float) Payment::query()
            ->whereHas('invoice.client', fn ($q) => $q->where('sales_person_id', $userId))
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
            ->sum('amount');
    }

    public function deficitCarriedIn(): float
    {
        $prevMonth = $this->month - 1;
        $prevYear = $this->year;

        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }

        $previous = self::where('user_id', $this->user_id)
            ->where('month', $prevMonth)
            ->where('year', $prevYear)
            ->first();

        if (! $previous) {
            return 0;
        }

        return max(0, $previous->effectiveTargetAmount() - $previous->achievedAmount());
    }

    public function effectiveTargetAmount(): float
    {
        return (float) $this->target_amount + $this->deficitCarriedIn();
    }
}
