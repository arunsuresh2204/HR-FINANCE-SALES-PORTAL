<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceSetting extends Model
{
    protected $fillable = ['signature_path', 'signer_name', 'signer_designation', 'gstin'];

    public static function current(): self
    {
        return static::first() ?? new static;
    }
}
