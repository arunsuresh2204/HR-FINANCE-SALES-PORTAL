<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = ['invoice_id', 'recorded_by', 'amount', 'native_amount', 'payment_date', 'notes'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'native_amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * For a payment against a foreign-currency invoice, the portion of the
     * invoice's own total this payment settles — null when not applicable
     * (an INR invoice, or an older payment recorded before this was
     * tracked).
     */
    public function nativeMoney(): ?string
    {
        if ($this->native_amount === null) {
            return null;
        }

        return \App\Support\Currency::format($this->native_amount, $this->invoice->currency);
    }
}
