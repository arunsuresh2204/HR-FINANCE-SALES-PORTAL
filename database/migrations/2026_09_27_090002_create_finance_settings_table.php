<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_settings', function (Blueprint $table) {
            $table->id();
            $table->string('signature_path')->nullable();
            $table->string('signer_name')->nullable();
            $table->string('signer_designation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_settings');
    }
};
