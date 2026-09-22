<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Categories are now a lean name-only bucket for grouping tasks — the real
 * price and currency live on each task (and, as of this migration, the
 * project itself), so a separate category-level estimate was unused
 * dead weight that nothing displayed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_categories', function (Blueprint $table) {
            $table->dropColumn(['currency', 'estimated_amount', 'estimated_hours', 'estimated_rate']);
        });
    }

    public function down(): void
    {
        Schema::table('project_categories', function (Blueprint $table) {
            $table->string('currency', 3)->default('INR')->after('name');
            $table->decimal('estimated_amount', 12, 2)->nullable()->after('currency');
            $table->decimal('estimated_hours', 8, 2)->nullable()->after('estimated_amount');
            $table->decimal('estimated_rate', 10, 2)->nullable()->after('estimated_hours');
        });
    }
};
