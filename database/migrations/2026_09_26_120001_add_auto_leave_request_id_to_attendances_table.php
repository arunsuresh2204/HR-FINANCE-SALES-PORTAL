<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Links an auto-marked half-day/leave attendance row to the LeaveRequest
     * it created, so approving a correction later knows exactly which
     * deduction to reinstate.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('auto_leave_request_id')->nullable()->after('status')
                ->constrained('leave_requests')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('auto_leave_request_id');
        });
    }
};
