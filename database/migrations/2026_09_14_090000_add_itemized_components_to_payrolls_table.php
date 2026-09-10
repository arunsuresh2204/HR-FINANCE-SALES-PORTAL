<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('basic_salary', 12, 2)->default(0)->after('gross_salary');
            $table->decimal('hra', 12, 2)->default(0)->after('basic_salary');
            $table->decimal('da', 12, 2)->default(0)->after('hra');
            $table->decimal('other_allowances', 12, 2)->default(0)->after('da');

            $table->decimal('income_tax', 12, 2)->default(0)->after('deductions');
            $table->decimal('provident_fund', 12, 2)->default(0)->after('income_tax');
            $table->decimal('loss_of_pay', 12, 2)->default(0)->after('provident_fund');
            $table->decimal('other_deductions', 12, 2)->default(0)->after('loss_of_pay');
            $table->unsignedTinyInteger('lop_days')->default(0)->after('other_deductions');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn([
                'basic_salary', 'hra', 'da', 'other_allowances',
                'income_tax', 'provident_fund', 'loss_of_pay', 'other_deductions', 'lop_days',
            ]);
        });
    }
};
