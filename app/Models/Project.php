<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Project extends Model
{
    protected $fillable = ['client_id', 'created_by', 'assigned_to', 'name', 'description', 'requirement_file', 'status', 'currency'];

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

    public function needsEstimate(): bool
    {
        return $this->categories()->doesntExist();
    }

    /**
     * Who a viewer may assign a task to on this project: Manager can assign
     * themselves, the person the project is currently handed off to, or any
     * developer on it. Team Lead and Developer can only assign themselves or
     * another developer — never upward to the Manager.
     */
    public function assignableUsersFor(User $viewer): Collection
    {
        $people = collect();

        if ($viewer->isManager() || $viewer->isSuperAdmin()) {
            $people->push($viewer);

            if ($this->assigned_to && $this->assigned_to !== $viewer->id) {
                $people->push($this->assignedTo);
            }
        } else {
            $people->push($viewer);
        }

        foreach ($this->developers as $developer) {
            if (! $people->contains('id', $developer->id)) {
                $people->push($developer);
            }
        }

        return $people->unique('id')->values();
    }
}
