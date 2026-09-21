<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('project_categories')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['backlog', 'todo', 'in_progress', 'review', 'done'])->default('backlog');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->json('tags')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->decimal('hours', 8, 2)->nullable();
            $table->decimal('rate', 10, 2)->nullable();
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('pending_approval')->default(false);
            $table->boolean('cancelled')->default(false);
            $table->string('cancel_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
