<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = ['posted_by', 'title', 'body', 'pinned', 'attachment_path'];

    protected function casts(): array
    {
        return [
            'pinned' => 'boolean',
        ];
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function isImageAttachment(): bool
    {
        return $this->attachment_path
            && in_array(strtolower(pathinfo($this->attachment_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png']);
    }
}
