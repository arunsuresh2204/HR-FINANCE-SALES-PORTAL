<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'symbol',
        'symbol_spaced',
        'format_style',
        'is_active',
    ];

    protected $casts = [
        'symbol_spaced' => 'bool',
        'is_active' => 'bool',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('currencies.all'));
        static::deleted(fn () => Cache::forget('currencies.all'));
    }
}
