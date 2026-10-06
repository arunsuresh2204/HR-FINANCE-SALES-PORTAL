<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

class Project extends Model
{
    protected $fillable = ['client_id', 'created_by', 'assigned_to', 'name', 'description', 'status', 'currency', 'estimated_amount'];

    protected function casts(): array
    {
        return [
            'estimated_amount' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function developers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_developers')->withTimestamps();
    }

    public function billingRequests(): HasMany
    {
        return $this->hasMany(BillingRequest::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(ProjectCategory::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ProjectRequest::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ProjectNote::class)->latest();
    }

    public function credentials(): HasMany
    {
        return $this->hasMany(ProjectCredential::class)->latest();
    }

    public function needsEstimate(): bool
    {
        return $this->estimated_amount === null;
    }

    /**
     * How much has already been claimed against this project's estimate —
     * every billing request not yet rejected, whether it's still pending
     * review or already invoiced. A rejected one frees that amount back up
     * to be billed again.
     */
    public function totalBilled(): float
    {
        return (float) $this->billingRequests()->whereIn('status', ['pending', 'invoiced'])->sum('amount');
    }

    public function remainingEstimate(): float
    {
        if ($this->estimated_amount === null) {
            return 0.0;
        }

        return round(max(0.0, (float) $this->estimated_amount - $this->totalBilled()), 2);
    }

    /**
     * Who a viewer may assign a task to on this project. Whoever can manage
     * the project (Manager, super_admin, or the Team Lead it's currently
     * handed off to) can pick any Programmer company-wide — not just ones
     * already staffed on it — since they can already add anyone as a
     * developer via Manage Developers; letting them do it in one step from
     * the task form avoids a task being "assigned" to someone who then has
     * no access to see it. A plain Developer can only assign themselves or
     * another developer already on the project — never upward, and never
     * pulling in someone new.
     */
    public function assignableUsersFor(User $viewer): Collection
    {
        $canManageProject = $viewer->isManager() || $viewer->isSuperAdmin() || $this->assigned_to === $viewer->id;

        $people = collect([$viewer]);

        if ($this->assigned_to && $this->assigned_to !== $viewer->id) {
            $people->push($this->assignedTo);
        }

        $people = $people->concat($canManageProject ? User::role('programmer')->get() : $this->developers);

        return $people->unique('id')->values();
    }

    /**
     * Whoever is newly picked as a task assignee, but isn't yet a developer
     * on this project (or its assignee), is added as one automatically —
     * assigning someone a task should always mean they can see it.
     */
    public function ensureDevelopers(array $userIds): void
    {
        $existingIds = $this->developers()->pluck('users.id')->all();

        $newIds = collect($userIds)
            ->reject(fn ($id) => $id == $this->assigned_to)
            ->diff($existingIds);

        if ($newIds->isNotEmpty()) {
            $this->developers()->attach($newIds);
        }
    }
}
