<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = ['date', 'name'];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public static function datesBetween(string $start, string $end): array
    {
        return self::whereBetween('date', [$start, $end])->pluck('date')
            ->map(fn ($date) => $date->format('Y-m-d'))
            ->all();
    }
}
