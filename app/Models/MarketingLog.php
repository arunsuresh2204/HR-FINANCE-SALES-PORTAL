<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingLog extends Model
{
    protected $fillable = [
        'user_id', 'client_id', 'work_date', 'platform', 'task_type',
        'is_in_house_product', 'hours', 'notes', 'deliverable_link',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'is_in_house_product' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
