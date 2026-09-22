<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Plain string, not a DB enum — validated in app code (Task::URGENCIES)
            // so it stays portable across the sqlite/mysql split this app runs on.
            $table->string('urgency')->default('medium')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('urgency');
        });
    }
};
