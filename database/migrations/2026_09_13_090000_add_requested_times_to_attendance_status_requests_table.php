<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_status_requests', function (Blueprint $table) {
            $table->time('requested_clock_in')->nullable()->after('requested_status');
            $table->time('requested_clock_out')->nullable()->after('requested_clock_in');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_status_requests', function (Blueprint $table) {
            $table->dropColumn(['requested_clock_in', 'requested_clock_out']);
        });
    }
};
