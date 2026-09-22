<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'project_id', 'category_id', 'title', 'description', 'assignee_id', 'status',
        'start_date', 'end_date', 'tags', 'amount', 'currency', 'hours', 'rate',
        'visibility', 'created_by', 'pending_approval',
        'cancelled', 'cancel_reason', 'cancelled_by', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'amount' => 'decimal:2',
            'hours' => 'decimal:2',
            'rate' => 'decimal:2',
            'pending_approval' => 'boolean',
            'cancelled' => 'boolean',
            'cancelled_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'category_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function billingRequests(): HasMany
    {
        return $this->hasMany(BillingRequest::class);
    }

    /**
     * Peer visibility among developers: a private task is only visible to the
     * developer who created it. Manager and Team Lead always see everything —
     * this is peer-to-peer privacy, not a way to hide work from leadership.
     */
    public function canBeSeenBy(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isManager() || $user->isTeamLead()) {
            return true;
        }

        return $this->visibility !== 'private' || $this->created_by === $user->id;
    }

    /**
     * Manager can cancel anything. A Team Lead/Developer can only withdraw a
     * task they created themselves, and only before it's been priced — once
     * the Manager has approved it, only the Manager can cancel.
     */
    public function canBeCancelledBy(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->isManager()) {
            return true;
        }

        return $this->created_by === $user->id && $this->pending_approval;
    }

    public function isPriced(): bool
    {
        return $this->amount > 0 || ($this->hours > 0 && $this->rate > 0);
    }

    public function effectiveAmount(): float
    {
        if ($this->amount !== null) {
            return (float) $this->amount;
        }

        if ($this->hours !== null && $this->rate !== null) {
            return (float) $this->hours * (float) $this->rate;
        }

        return 0.0;
    }

    /**
     * A Backlog task overdue on its own end date turns progressively redder —
     * light the first week, deeper each week after. Blade caps the tint tier.
     */
    public function weeksLate(): int
    {
        if ($this->status !== 'backlog' || ! $this->end_date) {
            return 0;
        }

        $today = now()->startOfDay();

        if (! $this->end_date->lt($today)) {
            return 0;
        }

        return (int) floor($this->end_date->diffInDays($today) / 7) + 1;
    }
}
