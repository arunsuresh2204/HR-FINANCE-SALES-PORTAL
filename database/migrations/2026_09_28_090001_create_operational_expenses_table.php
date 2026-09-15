<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('operational_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('operational_expense_categories')->restrictOnDelete();
            $table->string('vendor')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->boolean('is_recurring')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operational_expenses');
    }
};
