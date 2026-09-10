<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('basic_pay', 12, 2)->nullable()->after('monthly_salary');
            $table->decimal('hra_percent', 5, 2)->nullable()->after('basic_pay');
            $table->decimal('da_percent', 5, 2)->nullable()->after('hra_percent');
            $table->decimal('other_allowances', 12, 2)->nullable()->after('da_percent');
            $table->unsignedSmallInteger('annual_casual_leave')->default(12)->after('other_allowances');
            $table->unsignedSmallInteger('annual_sick_leave')->default(12)->after('annual_casual_leave');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['basic_pay', 'hra_percent', 'da_percent', 'other_allowances', 'annual_casual_leave', 'annual_sick_leave']);
        });
    }
};
