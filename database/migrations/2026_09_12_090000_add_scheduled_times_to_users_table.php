<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->time('scheduled_login_time')->nullable()->after('manager_id');
            $table->time('scheduled_logoff_time')->nullable()->after('scheduled_login_time');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['scheduled_login_time', 'scheduled_logoff_time']);
        });
    }
};
