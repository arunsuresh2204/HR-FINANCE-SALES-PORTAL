<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    /**
     * Free-text hourly line items for the older, non-project billing
     * request flow (Sales typing up "3h @ rate" entries by hand).
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(BillingRequestTask::class);
    }

    /**
     * The real engineering Tasks this billing request bundles — set when
     * Sales picks completed, ready-to-bill tasks off a project to bill.
     */
    public function billedTasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'billed_tasks');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function isHourly(): bool
    {
        return $this->billing_type === 'hourly';
    }

    public function isFromTask(): bool
    {
        return $this->billedTasks->isNotEmpty();
    }

    /**
     * The distinct projects billed by this request. A task-based request
     * can span several projects belonging to the same client (bundled by
     * Sales into one invoice); a legacy/non-task request has at most the
     * single stored `project`.
     */
    public function projects(): \Illuminate\Support\Collection
    {
        if ($this->isFromTask()) {
            return $this->billedTasks->loadMissing('project')->pluck('project')->filter()->unique('id')->values();
        }

        return $this->project ? collect([$this->project]) : collect();
    }

    public function projectsLabel(): ?string
    {
        $names = $this->projects()->pluck('name');

        return match (true) {
            $names->isEmpty() => null,
            $names->count() === 1 => $names->first(),
            default => $names->implode(' + '),
        };
    }

    public function summary(): string
    {
        if ($this->isFromTask()) {
            $count = $this->billedTasks->count();

            return $count.' '.str('task')->plural($count).' — '.($this->projectsLabel() ?? 'billed');
        }

        if ($this->isHourly()) {
            $count = $this->tasks->count() ?: $this->tasks()->count();

            return $count.' hourly '.str('task')->plural($count).' · '.number_format((float) $this->tasks()->sum('hours'), 2).'h';
        }

        return (string) $this->milestone_description;
    }
}
