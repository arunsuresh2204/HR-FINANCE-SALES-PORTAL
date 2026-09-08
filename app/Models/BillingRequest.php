<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BillingRequest extends Model
{
    protected $fillable = [
        'client_id', 'project_id', 'created_by', 'currency', 'billing_type',
        'amount', 'milestone_description', 'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(BillingRequestTask::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function isHourly(): bool
    {
        return $this->billing_type === 'hourly';
    }

    public function summary(): string
    {
        if ($this->isHourly()) {
            $count = $this->tasks->count() ?: $this->tasks()->count();

            return $count.' hourly '.str('task')->plural($count).' · '.number_format((float) $this->tasks()->sum('hours'), 2).'h';
        }

        return (string) $this->milestone_description;
    }
}
