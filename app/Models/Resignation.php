<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resignation extends Model
{
    protected $fillable = [
        'user_id', 'notice_date', 'last_working_date', 'reason', 'status', 'reviewed_by', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'notice_date' => 'date',
            'last_working_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function offboardingTasks(): HasMany
    {
        return $this->hasMany(OffboardingTask::class);
    }
}
