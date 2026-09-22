<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->boolean('ready_to_bill')->default(false)->after('cancelled_at');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('ready_to_bill');
        });
    }
};
