<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Widens the status enum to add 'half_day', kept alongside the existing
     * values ('absent' stays for old rows — new code no longer sets it, an
     * unattended full day now finalizes as 'on_leave' with an auto-deduction
     * instead). SQLite has no ALTER for CHECK constraints, so the column is
     * rebuilt there via Laravel's change(); MySQL/others get a direct MODIFY.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('attendances', function (Blueprint $table) {
                $table->enum('status', ['present', 'late', 'half_day', 'absent', 'on_leave'])->default('present')->change();
            });
        } else {
            DB::statement("ALTER TABLE attendances MODIFY status ENUM('present', 'late', 'half_day', 'absent', 'on_leave') NOT NULL DEFAULT 'present'");
        }
    }

    public function down(): void
    {
        DB::table('attendances')->where('status', 'half_day')->update(['status' => 'late']);

        if (DB::getDriverName() === 'sqlite') {
            Schema::table('attendances', function (Blueprint $table) {
                $table->enum('status', ['present', 'late', 'absent', 'on_leave'])->default('present')->change();
            });
        } else {
            DB::statement("ALTER TABLE attendances MODIFY status ENUM('present', 'late', 'absent', 'on_leave') NOT NULL DEFAULT 'present'");
        }
    }
};
