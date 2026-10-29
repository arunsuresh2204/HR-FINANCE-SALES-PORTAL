<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlackSetting extends Model
{
    protected $fillable = ['general_webhook_url'];

    protected $hidden = ['general_webhook_url'];

    protected function casts(): array
    {
        return [
            'general_webhook_url' => 'encrypted',
        ];
    }

    public static function current(): self
    {
        return static::first() ?? new static;
    }
}
