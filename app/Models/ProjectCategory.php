<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectCategory extends Model
{
    protected $fillable = [
        'project_id', 'name', 'currency', 'estimated_amount', 'estimated_hours', 'estimated_rate', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'estimated_amount' => 'decimal:2',
            'estimated_hours' => 'decimal:2',
            'estimated_rate' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function estimatedTotal(): float
    {
        if ($this->estimated_amount !== null) {
            return (float) $this->estimated_amount;
        }

        if ($this->estimated_hours !== null && $this->estimated_rate !== null) {
            return (float) $this->estimated_hours * (float) $this->estimated_rate;
        }

        return 0.0;
    }
}
