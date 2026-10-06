<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The overall cost the engineering manager quotes for the whole
     * project — most projects here are small/medium and billed as one
     * lump-sum estimate rather than per-task, so billing requests are
     * tracked against this total (see Project::totalBilled()) instead of
     * requiring every task to be individually priced and marked Done first.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('estimated_amount', 12, 2)->nullable()->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('estimated_amount');
        });
    }
};
