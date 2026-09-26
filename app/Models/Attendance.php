<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    public const GRACE_MINUTES = 30;

    public const HALF_DAY_CUTOFF_HOURS = 4;

    /**
     * Fallback length of a working day, used only when an employee has no
     * scheduled_logoff_time set — otherwise their own logoff time marks
     * when their day is considered over.
     */
    public const DEFAULT_WORKDAY_HOURS = 6;

    protected $fillable = [
        'user_id', 'work_date', 'scheduled_login_time', 'clock_in', 'clock_out', 'ip_address', 'status',
        'auto_leave_request_id', 'notes',
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

    public function autoLeaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class, 'auto_leave_request_id');
    }

    /**
     * present/late/half_day for an actual clock-in against the employee's
     * own schedule and timezone — shared by self-service clock-in and by
     * HR approving a corrected clock-in time, so the two can never disagree.
     */
    public static function deriveStatusFromClockIn(User $user, string $workDate, Carbon $clockIn): string
    {
        $scheduledLogin = $user->scheduled_login_time;

        if (! $scheduledLogin) {
            return 'present';
        }

        $scheduledAt = Carbon::parse($workDate.' '.$scheduledLogin, $user->tz());

        if ($clockIn->lte($scheduledAt)) {
            return 'present';
        }

        $minutesLate = max(0, $scheduledAt->diffInMinutes($clockIn, false));

        return $minutesLate >= self::HALF_DAY_CUTOFF_HOURS * 60 ? 'half_day' : 'late';
    }

    /**
     * Compute the display status (tier + label + warning flag) for a user on a given date,
     * whether or not an attendance record exists yet.
     */
    public static function computeStatus(User $user, string $date, ?self $attendance): array
    {
        $approvedLeave = $user->approvedLeaveOn($date);

        if ($approvedLeave) {
            $label = $approvedLeave->is_half_day ? 'On Leave (Half Day)' : 'On Leave';

            return ['tier' => 'on_leave', 'label' => $label, 'warning' => false, 'minutes_late' => null];
        }

        if ($attendance && $attendance->status === 'on_leave') {
            // A genuinely pre-approved leave is always caught above, so reaching this with
            // an auto_leave_request_id means the day was auto-marked for no clock-in at all.
            $label = $attendance->auto_leave_request_id ? 'Leave (Unattended)' : 'On Leave';

            return ['tier' => 'on_leave', 'label' => $label, 'warning' => (bool) $attendance->auto_leave_request_id, 'minutes_late' => null];
        }

        $scheduledLogin = $attendance->scheduled_login_time ?? $user->scheduled_login_time;

        if (! $scheduledLogin) {
            return ['tier' => 'no_schedule', 'label' => 'No Schedule Set', 'warning' => false, 'minutes_late' => null];
        }

        $tz = $user->tz();
        $scheduledAt = Carbon::parse($date.' '.$scheduledLogin, $tz);
        $graceEnd = $scheduledAt->copy()->addMinutes(self::GRACE_MINUTES);
        $halfDayAt = $scheduledAt->copy()->addHours(self::HALF_DAY_CUTOFF_HOURS);
        $eodAt = self::endOfDayFor($user, $date, $scheduledAt);

        if ($attendance && $attendance->clock_in) {
            $clockIn = $attendance->clock_in instanceof Carbon ? $attendance->clock_in : Carbon::parse($attendance->clock_in);
            $minutesLate = max(0, $scheduledAt->diffInMinutes($clockIn, false));

            if ($clockIn->lte($scheduledAt)) {
                return ['tier' => 'on_time', 'label' => 'On Time', 'warning' => false, 'minutes_late' => 0];
            }

            if ($minutesLate >= self::HALF_DAY_CUTOFF_HOURS * 60) {
                return ['tier' => 'half_day', 'label' => 'Half Day ('.self::formatDuration($minutesLate).' late)', 'warning' => true, 'minutes_late' => $minutesLate];
            }

            if ($clockIn->lte($graceEnd)) {
                return ['tier' => 'grace', 'label' => 'Late ('.self::formatDuration($minutesLate).')', 'warning' => false, 'minutes_late' => $minutesLate];
            }

            return ['tier' => 'severe', 'label' => 'Late - Warning ('.self::formatDuration($minutesLate).')', 'warning' => true, 'minutes_late' => $minutesLate];
        }

        if ($attendance && $attendance->exists) {
            // A row exists with no clock-in: either the auto-mark sync created it (status
            // will be 'on_leave', already handled above), a legacy 'absent' row from before
            // this policy existed, or HR approved a status correction directly.
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

        if ($now->lt($halfDayAt)) {
            return ['tier' => 'severe', 'label' => 'Not Clocked In - Warning', 'warning' => true, 'minutes_late' => null];
        }

        if ($now->lt($eodAt)) {
            return ['tier' => 'half_day', 'label' => 'Not Clocked In - Half Day', 'warning' => true, 'minutes_late' => null];
        }

        return ['tier' => 'on_leave', 'label' => 'Not Clocked In - Leave', 'warning' => true, 'minutes_late' => null];
    }

    /**
     * When the employee's working day is considered over for the purpose of
     * the "never clocked in at all" full-day leave mark — their own scheduled
     * logoff time if set, otherwise a flat fallback after their login time.
     */
    protected static function endOfDayFor(User $user, string $date, Carbon $scheduledAt): Carbon
    {
        if ($user->scheduled_logoff_time) {
            $eodAt = Carbon::parse($date.' '.$user->scheduled_logoff_time, $user->tz());

            if ($eodAt->gt($scheduledAt)) {
                return $eodAt;
            }
        }

        return $scheduledAt->copy()->addHours(self::DEFAULT_WORKDAY_HOURS);
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
     * Finalize any working day that has fully elapsed with no clock-in at all
     * and no approved leave covering it: mark it 'on_leave' and auto-create an
     * approved, full-day LeaveRequest so it's deducted from the employee's
     * allocation the same way any other approved leave is. Safe to call
     * repeatedly — a day already handled (clocked in, already marked, or
     * genuinely pre-approved leave) is left untouched.
     */
    public static function syncAbsencesFor(Carbon $date): void
    {
        $dateString = $date->toDateString();

        User::where('employment_status', 'active')
            ->whereNotNull('scheduled_login_time')
            ->each(function (User $user) use ($dateString) {
                $scheduledAt = Carbon::parse($dateString.' '.$user->scheduled_login_time, $user->tz());
                $eodAt = self::endOfDayFor($user, $dateString, $scheduledAt);

                if (now()->lt($eodAt)) {
                    return;
                }

                if ($user->hasApprovedLeaveOn($dateString)) {
                    return;
                }

                // A record already existing for this day means it's already been handled —
                // clocked in, already auto-marked, or HR approved a status correction.
                if (self::where('user_id', $user->id)->whereDate('work_date', $dateString)->exists()) {
                    return;
                }

                $leaveRequest = LeaveRequest::create([
                    'user_id' => $user->id,
                    'type' => 'vacation',
                    'start_date' => $dateString,
                    'end_date' => $dateString,
                    'days' => 1,
                    'reason' => 'Auto-marked: no clock-in recorded for the day.',
                    'status' => 'approved',
                    'reviewed_at' => now(),
                ]);

                // insertOrIgnore (rather than exists()-then-create) so two concurrent syncs
                // for the same day can't race each other into a duplicate-key error; if we
                // lost that race, undo the leave request we just created so it isn't orphaned.
                $inserted = self::query()->insertOrIgnore([[
                    'user_id' => $user->id,
                    'work_date' => $dateString,
                    'scheduled_login_time' => $user->scheduled_login_time,
                    'status' => 'on_leave',
                    'auto_leave_request_id' => $leaveRequest->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]]);

                if ($inserted === 0) {
                    $leaveRequest->delete();
                }
            });
    }
}
