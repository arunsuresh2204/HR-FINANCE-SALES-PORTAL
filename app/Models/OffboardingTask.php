<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OffboardingTask extends Model
{
    protected $fillable = ['resignation_id', 'task', 'completed'];

    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
        ];
    }

    public function resignation(): BelongsTo
    {
        return $this->belongsTo(Resignation::class);
    }
}
