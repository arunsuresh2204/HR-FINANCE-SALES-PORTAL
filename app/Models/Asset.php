<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    protected $fillable = [
        'user_id', 'item_name', 'item_type', 'serial_number', 'assigned_date',
        'return_date', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'assigned_date' => 'date',
            'return_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
