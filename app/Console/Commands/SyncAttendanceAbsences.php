<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Illuminate\Console\Command;

class SyncAttendanceAbsences extends Command
{
    protected $signature = 'attendance:sync-absences';

    protected $description = 'Auto-mark employees absent once 6 hours have passed their scheduled login time with no clock-in and no approved leave.';

    public function handle(): int
    {
        Attendance::syncAbsencesFor(now());

        $this->info('Attendance absences synced for '.now()->toDateString());

        return self::SUCCESS;
    }
}
