<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'lead_id', 'sales_person_id', 'business_name', 'business_type', 'business_address',
        'owner_name', 'owner_designation', 'owner_contact', 'tax_id', 'agreement_file',
        'agreement_effective_date', 'agreement_scope_summary',
    ];

    protected function casts(): array
    {
        return [
            'agreement_effective_date' => 'date',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function salesPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    public function billingRequests(): HasMany
    {
        return $this->hasMany(BillingRequest::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class);
    }

    public function totalInvoiced(): float
    {
        return (float) $this->invoices()->sum('total_amount');
    }

    public function totalPaid(): float
    {
        return (float) $this->invoices()->sum('amount_paid');
    }
}
