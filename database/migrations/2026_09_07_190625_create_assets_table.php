<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('item_name');
            $table->string('item_type')->nullable();
            $table->string('serial_number')->nullable();
            $table->date('assigned_date');
            $table->date('return_date')->nullable();
            $table->enum('status', ['assigned', 'returned'])->default('assigned');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
