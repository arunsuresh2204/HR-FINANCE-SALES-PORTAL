<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    public const GRACE_MINUTES = 30;

    public const ABSENT_CUTOFF_HOURS = 6;

    protected $fillable = [
        'user_id', 'work_date', 'scheduled_login_time', 'clock_in', 'clock_out', 'ip_address', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'clock_in' => 'datetime',
            'clock_out' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusRequests(): HasMany
    {
        return $this->hasMany(AttendanceStatusRequest::class);
    }

    /**
     * Compute the display status (tier + label + warning flag) for a user on a given date,
     * whether or not an attendance record exists yet.
     */
    public static function computeStatus(User $user, string $date, ?self $attendance): array
    {
        if (($attendance && $attendance->status === 'on_leave') || $user->hasApprovedLeaveOn($date)) {
            return ['tier' => 'on_leave', 'label' => 'On Leave', 'warning' => false, 'minutes_late' => null];
        }

        $scheduledLogin = $attendance->scheduled_login_time ?? $user->scheduled_login_time;

        if (! $scheduledLogin) {
            return ['tier' => 'no_schedule', 'label' => 'No Schedule Set', 'warning' => false, 'minutes_late' => null];
        }

        $scheduledAt = Carbon::parse($date.' '.$scheduledLogin);
        $graceEnd = $scheduledAt->copy()->addMinutes(self::GRACE_MINUTES);
        $absentCutoff = $scheduledAt->copy()->addHours(self::ABSENT_CUTOFF_HOURS);

        if ($attendance && $attendance->clock_in) {
            $clockIn = $attendance->clock_in instanceof Carbon ? $attendance->clock_in : Carbon::parse($attendance->clock_in);
            $minutesLate = max(0, $scheduledAt->diffInMinutes($clockIn, false));

            if ($clockIn->lte($scheduledAt)) {
                return ['tier' => 'on_time', 'label' => 'On Time', 'warning' => false, 'minutes_late' => 0];
            }

            if ($clockIn->lte($graceEnd)) {
                return ['tier' => 'grace', 'label' => 'Late ('.self::formatDuration($minutesLate).')', 'warning' => false, 'minutes_late' => $minutesLate];
            }

            return ['tier' => 'severe', 'label' => 'Late - Warning ('.self::formatDuration($minutesLate).')', 'warning' => true, 'minutes_late' => $minutesLate];
        }

        if ($attendance && $attendance->exists) {
            // A row exists with no clock-in: either the auto-absent sync marked it, or HR
            // approved a status correction. Either way, trust the persisted status rather
            // than recomputing live against the current time.
            return match ($attendance->status) {
                'absent' => ['tier' => 'absent', 'label' => 'Absent', 'warning' => false, 'minutes_late' => null],
                'late' => ['tier' => 'grace', 'label' => 'Late (Confirmed)', 'warning' => false, 'minutes_late' => null],
                default => ['tier' => 'on_time', 'label' => 'Present (Confirmed)', 'warning' => false, 'minutes_late' => null],
            };
        }

        $now = now();

        if ($now->lte($scheduledAt)) {
            return ['tier' => 'pending', 'label' => 'Not Due Yet', 'warning' => false, 'minutes_late' => null];
        }

        if ($now->lte($graceEnd)) {
            return ['tier' => 'grace', 'label' => 'Not Clocked In', 'warning' => false, 'minutes_late' => null];
        }

        if ($now->lte($absentCutoff)) {
            return ['tier' => 'severe', 'label' => 'Not Clocked In - Warning', 'warning' => true, 'minutes_late' => null];
        }

        return ['tier' => 'absent', 'label' => 'Absent', 'warning' => false, 'minutes_late' => null];
    }

    /**
     * Format a minute count as a compact duration, e.g. 45 => "45m", 90 => "1h30m", 120 => "2h".
     */
    public static function formatDuration(int $minutes): string
    {
        if ($minutes < 60) {
            return "{$minutes}m";
        }

        $hours = intdiv($minutes, 60);
        $remainder = $minutes % 60;

        return $remainder > 0 ? "{$hours}h{$remainder}m" : "{$hours}h";
    }

    /**
     * Auto-mark employees absent once 6 hours have passed their scheduled login time
     * with no clock-in and no approved leave covering the date. Safe to call repeatedly.
     */
    public static function syncAbsencesFor(Carbon $date): void
    {
        $dateString = $date->toDateString();

        User::where('employment_status', 'active')
            ->whereNotNull('scheduled_login_time')
            ->each(function (User $user) use ($dateString) {
                $scheduledAt = Carbon::parse($dateString.' '.$user->scheduled_login_time);

                if (now()->lt($scheduledAt->copy()->addHours(self::ABSENT_CUTOFF_HOURS))) {
                    return;
                }

                if ($user->hasApprovedLeaveOn($dateString)) {
                    return;
                }

                // A record already existing for this day means it's already been handled — either
                // the employee clocked in, it was already auto-marked absent, or HR approved a
                // status correction. Never overwrite it; only create a fresh absence record.
                // insertOrIgnore is used (rather than exists()-then-create) so two concurrent
                // syncs for the same day can't race each other into a duplicate-key error.
                if (self::where('user_id', $user->id)->whereDate('work_date', $dateString)->exists()) {
                    return;
                }

                self::query()->insertOrIgnore([[
                    'user_id' => $user->id,
                    'work_date' => $dateString,
                    'scheduled_login_time' => $user->scheduled_login_time,
                    'status' => 'absent',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]]);
            });
    }
}
