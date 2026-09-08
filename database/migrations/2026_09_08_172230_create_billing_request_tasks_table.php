<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_request_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_request_id')->constrained()->cascadeOnDelete();
            $table->string('task_description');
            $table->decimal('hours', 6, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_request_tasks');
    }
};
