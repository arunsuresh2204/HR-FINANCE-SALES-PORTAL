<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceBreak extends Model
{
    protected $fillable = ['attendance_id', 'break_start', 'break_end'];

    protected function casts(): array
    {
        return [
            'break_start' => 'datetime',
            'break_end' => 'datetime',
        ];
    }

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    public function isOpen(): bool
    {
        return $this->break_end === null;
    }

    /**
     * Minutes elapsed so far — against now() while still running, or the
     * recorded end time once closed.
     */
    public function minutes(): int
    {
        $end = $this->break_end ?? Carbon::now();

        return max(0, (int) floor($this->break_start->diffInMinutes($end)));
    }
}
