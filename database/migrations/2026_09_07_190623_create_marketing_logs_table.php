<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->date('work_date');
            $table->string('platform');
            $table->enum('task_type', ['content', 'ad', 'engagement', 'other']);
            $table->boolean('is_in_house_product')->default(false);
            $table->decimal('hours', 5, 2);
            $table->text('notes')->nullable();
            $table->string('deliverable_link')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_logs');
    }
};
