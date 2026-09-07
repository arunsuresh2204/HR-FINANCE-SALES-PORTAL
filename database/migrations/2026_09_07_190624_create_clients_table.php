<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sales_person_id')->constrained('users')->cascadeOnDelete();
            $table->string('business_name');
            $table->string('business_type')->nullable();
            $table->text('business_address')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('owner_designation')->nullable();
            $table->string('owner_contact')->nullable();
            $table->string('agreement_file')->nullable();
            $table->date('agreement_effective_date')->nullable();
            $table->text('agreement_scope_summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
