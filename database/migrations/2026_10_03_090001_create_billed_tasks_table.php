<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pivot linking a BillingRequest to the real engineering Tasks it bills —
 * a Sales-created billing request can bundle several tasks from the same
 * project into one client bill. Distinct from billing_request_tasks, which
 * holds free-text hourly line items for the older, non-project billing
 * request flow.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billed_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('billing_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['billing_request_id', 'task_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billed_tasks');
    }
};
