<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('slack_settings', function (Blueprint $table) {
            $table->id();
            // Catch-all channel for anything not tied to a specific project —
            // HR events (resignations, leave requests) and new-project
            // announcements (a project doesn't have its own channel yet at
            // the moment it's created).
            $table->text('general_webhook_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slack_settings');
    }
};
