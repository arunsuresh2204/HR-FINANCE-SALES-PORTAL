<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->decimal('previous_salary', 12, 2)->nullable()->after('previous_department');
            $table->decimal('new_salary', 12, 2)->nullable()->after('previous_salary');
        });
    }

    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn(['previous_salary', 'new_salary']);
        });
    }
};
