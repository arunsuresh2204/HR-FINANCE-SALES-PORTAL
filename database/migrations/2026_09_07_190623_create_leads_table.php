<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_person_id')->constrained('users')->cascadeOnDelete();
            $table->string('client_name');
            $table->string('company_name')->nullable();
            $table->string('country')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->text('requirement');
            $table->enum('service_type', ['web', 'mobile', 'social_media', 'pet_product', 'other']);
            $table->string('source');
            $table->enum('status', ['new', 'contacted', 'proposal_sent', 'negotiation', 'won', 'lost'])->default('new');
            $table->decimal('budget', 12, 2)->nullable();
            $table->date('follow_up_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
