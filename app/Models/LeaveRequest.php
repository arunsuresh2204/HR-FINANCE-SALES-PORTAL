<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    public const CERTIFICATE_MIN_DAYS = 3;

    protected $fillable = [
        'user_id', 'type', 'start_date', 'end_date', 'days', 'reason',
        'status', 'reviewed_by', 'reviewed_at', 'review_notes',
        'certificate_path', 'certificate_requested_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'reviewed_at' => 'datetime',
            'certificate_requested_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function requiresCertificate(): bool
    {
        return $this->type === 'sick' && (float) $this->days >= self::CERTIFICATE_MIN_DAYS;
    }

    public function hasCertificate(): bool
    {
        return ! empty($this->certificate_path);
    }

    public function needsCertificate(): bool
    {
        return $this->requiresCertificate() && ! $this->hasCertificate();
    }

    public static function calculateBusinessDays(string $start, string $end): int
    {
        try {
            $cursor = \Carbon\Carbon::parse($start);
            $endDate = \Carbon\Carbon::parse($end);
        } catch (\Throwable) {
            return 0;
        }

        if ($cursor->gt($endDate)) {
            return 0;
        }

        $days = 0;
        while ($cursor->lte($endDate)) {
            if (! $cursor->isWeekend()) {
                $days++;
            }
            $cursor->addDay();
        }

        return $days;
    }
}
