<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('currency', 3)->default('INR');
            $table->enum('billing_type', ['milestone', 'hourly'])->default('milestone');
            $table->decimal('amount', 12, 2);
            $table->string('milestone_description')->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->enum('status', ['pending', 'invoiced', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_requests');
    }
};
