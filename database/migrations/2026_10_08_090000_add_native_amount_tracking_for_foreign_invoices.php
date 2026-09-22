<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A payment's `amount` is always INR (what actually landed in the
        // bank); for a foreign-currency invoice that alone can't say how
        // much of the invoice's own total it settles — there's no stored
        // FX rate to derive it. `native_amount` is that missing figure,
        // entered directly by Finance in the invoice's own currency.
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('native_amount', 12, 2)->nullable()->after('amount');
        });

        // Same idea for a refund: its `amount`/`currency` are always INR
        // (real cash paid back), so a refund on a foreign invoice also
        // needs its own native-currency figure to correctly reverse what
        // was settled.
        Schema::table('invoice_adjustments', function (Blueprint $table) {
            $table->decimal('native_amount', 12, 2)->nullable()->after('currency');
        });

        // The running total of native_amount across payments minus refunds
        // for a foreign-currency invoice — mirrors amount_paid (which does
        // this job for INR invoices) so balanceDue() stays an O(1) column
        // read instead of summing relations on every call.
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('native_amount_settled', 12, 2)->default(0)->after('amount_paid');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('native_amount_settled');
        });

        Schema::table('invoice_adjustments', function (Blueprint $table) {
            $table->dropColumn('native_amount');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('native_amount');
        });
    }
};
