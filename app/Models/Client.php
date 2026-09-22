<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Client extends Model
{
    protected $fillable = [
        'lead_id', 'sales_person_id', 'business_name', 'business_type', 'business_address',
        'owner_name', 'owner_designation', 'owner_contact', 'owner_phone', 'tax_id', 'agreement_file',
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

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
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

    /**
     * Invoiced total per currency — a client can be billed in more than one
     * currency (e.g. an INR project and a USD one), so a single summed
     * figure would silently add unlike currencies together. Keyed by
     * currency code.
     */
    public function invoicedByCurrency(): \Illuminate\Support\Collection
    {
        return $this->invoices->groupBy('currency')->map(fn ($group) => (float) $group->sum('total_amount'));
    }

    /**
     * amount_paid is always recorded in INR regardless of the invoice's own
     * currency (see Invoice::recalculatePaid()), so this sum is already a
     * single valid figure — no per-currency breakdown needed.
     */
    public function totalPaid(): float
    {
        return (float) $this->invoices()->sum('amount_paid');
    }

    public function billingRequestTasks(): HasManyThrough
    {
        return $this->hasManyThrough(BillingRequestTask::class, BillingRequest::class);
    }

    public function billableHours(): float
    {
        return (float) $this->billingRequestTasks()->sum('hours');
    }
}
