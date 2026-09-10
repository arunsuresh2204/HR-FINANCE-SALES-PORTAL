<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = ['client_id', 'created_by', 'assigned_to', 'name', 'description', 'status'];

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
}
