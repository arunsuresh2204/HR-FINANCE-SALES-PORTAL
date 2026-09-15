<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Widen status from a fixed enum to a plain string so cancelled /
        // credit_note / refunded / written_off can be added without a
        // driver-specific ALTER ... ENUM statement.
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('status')->default('draft')->change();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('adjustment_type')->nullable()->after('status');
            $table->decimal('adjustment_amount', 12, 2)->nullable()->after('adjustment_type');
            $table->text('adjustment_reason')->nullable()->after('adjustment_amount');
            $table->timestamp('adjustment_at')->nullable()->after('adjustment_reason');
            $table->foreignId('adjusted_by')->nullable()->after('adjustment_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('adjusted_by');
            $table->dropColumn(['adjustment_type', 'adjustment_amount', 'adjustment_reason', 'adjustment_at']);
        });
    }
};
