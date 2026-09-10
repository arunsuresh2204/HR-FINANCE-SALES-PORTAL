<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_status_requests', function (Blueprint $table) {
            $table->string('reason_category')->nullable()->after('requested_status');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_status_requests', function (Blueprint $table) {
            $table->dropColumn('reason_category');
        });
    }
};
