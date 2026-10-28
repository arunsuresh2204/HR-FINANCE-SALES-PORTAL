<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailSetting extends Model
{
    protected $fillable = [
        'mail_type', 'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password',
        'smtp_encryption', 'smtp_from_address', 'smtp_from_name',
    ];

    protected $hidden = ['smtp_password'];

    protected function casts(): array
    {
        return [
            'smtp_password' => 'encrypted',
        ];
    }

    public static function current(): self
    {
        return static::first() ?? new static;
    }
}
