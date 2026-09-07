<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timesheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->string('project_name')->nullable();
            $table->date('work_date');
            $table->text('task_description');
            $table->decimal('hours', 5, 2);
            $table->enum('status', ['in_progress', 'completed', 'blocked'])->default('in_progress');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timesheets');
    }
};
