<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectCredential extends Model
{
    protected $fillable = ['project_id', 'label', 'username', 'secret', 'notes', 'created_by'];

    protected function casts(): array
    {
        return [
            'secret' => 'encrypted',
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
}
