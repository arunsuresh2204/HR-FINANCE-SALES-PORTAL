<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->decimal('gross_salary', 12, 2);
            $table->decimal('deductions', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2);
            $table->enum('status', ['draft', 'processed', 'paid'])->default('draft');
            $table->string('payslip_file')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
