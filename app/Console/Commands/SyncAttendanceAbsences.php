<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Illuminate\Console\Command;

class SyncAttendanceAbsences extends Command
{
    protected $signature = 'attendance:sync-absences';

    protected $description = 'Auto-mark employees on leave once their working day is over with no clock-in and no approved leave, deducting a full day from their allocation. Also closes out any break left open.';

    public function handle(): int
    {
        Attendance::syncAbsencesFor(now());
        Attendance::syncAbsencesFor(now()->subDay());
        Attendance::closeStaleOpenBreaks();

        $this->info('Attendance absences synced for '.now()->toDateString());

        return self::SUCCESS;
    }
}
