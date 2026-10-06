<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per break an employee takes during a clocked-in day. break_end
     * is null while the break is still running. Multiple rows per
     * attendance let someone take a couple of short breaks that together
     * count toward the 1-hour daily allowance, rather than one continuous
     * break.
     */
    public function up(): void
    {
        Schema::create('attendance_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            $table->dateTime('break_start');
            $table->dateTime('break_end')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_breaks');
    }
};
