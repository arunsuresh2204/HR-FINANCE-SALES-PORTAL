<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ProjectRequest extends Model
{
    protected $fillable = [
        'project_id', 'created_by', 'title', 'description', 'status',
        'converted_task_id', 'converted_by', 'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'converted_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function convertedTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'converted_task_id');
    }

    public function convertedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ProjectRequestComment::class)->orderBy('created_at');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
