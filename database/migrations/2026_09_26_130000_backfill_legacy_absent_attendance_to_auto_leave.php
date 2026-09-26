<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Before this half-day/full-day auto-leave feature existed, a day with no
     * clock-in was auto-marked 'absent' with nothing deducted from the
     * employee's leave allocation. Those rows predate Attendance::syncAbsencesFor()
     * and its idempotency guard ("a row already exists for this day, so it's
     * already been handled") means it will never touch them on its own —
     * they'd stay 'absent' with no deduction forever. Convert every such row
     * to the same 'on_leave' + linked auto-approved LeaveRequest that the new
     * logic would have created at the time, unless the employee already has
     * an approved leave covering that day (so nothing is ever double-deducted).
     */
    public function up(): void
    {
        $now = now();

        DB::table('attendances')
            ->where('status', 'absent')
            ->whereNull('clock_in')
            ->whereNull('auto_leave_request_id')
            ->orderBy('id')
            ->get(['id', 'user_id', 'work_date'])
            ->each(function ($attendance) use ($now) {
                $workDate = \Illuminate\Support\Carbon::parse($attendance->work_date)->toDateString();

                $alreadyOnLeave = DB::table('leave_requests')
                    ->where('user_id', $attendance->user_id)
                    ->where('status', 'approved')
                    ->where('start_date', '<=', $workDate)
                    ->where('end_date', '>=', $workDate)
                    ->exists();

                if ($alreadyOnLeave) {
                    return;
                }

                $leaveRequestId = DB::table('leave_requests')->insertGetId([
                    'user_id' => $attendance->user_id,
                    'type' => 'vacation',
                    'start_date' => $workDate,
                    'end_date' => $workDate,
                    'days' => 1,
                    'reason' => 'Auto-marked (backfilled): no clock-in recorded for the day.',
                    'status' => 'approved',
                    'reviewed_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('attendances')->where('id', $attendance->id)->update([
                    'status' => 'on_leave',
                    'auto_leave_request_id' => $leaveRequestId,
                    'updated_at' => $now,
                ]);
            });
    }

    public function down(): void
    {
        // Data backfill only — not reversible without risking loss of
        // legitimate leave/attendance changes made afterward.
    }
};
