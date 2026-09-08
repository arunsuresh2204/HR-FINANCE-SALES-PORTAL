<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingRequestTask extends Model
{
    protected $fillable = ['billing_request_id', 'task_description', 'hours', 'rate'];

    protected function casts(): array
    {
        return [
            'hours' => 'decimal:2',
            'rate' => 'decimal:2',
        ];
    }

    public function billingRequest(): BelongsTo
    {
        return $this->belongsTo(BillingRequest::class);
    }

    public function amount(): float
    {
        return (float) $this->hours * (float) $this->rate;
    }
}
