<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('adjustment_document_number')->nullable()->after('adjustment_at');
        });

        // Backfill a document number for any credit note / refund already
        // issued before this feature existed, in the same CN-<FY>-0001 /
        // RV-<FY>-0001 scheme new ones get — oldest first, so the sequence
        // this produces is exactly what Invoice::nextCreditNoteNumber() /
        // nextRefundVoucherNumber() would have assigned had it existed then.
        $counters = [];

        DB::table('invoices')
            ->whereIn('adjustment_type', ['credit_note', 'refund'])
            ->whereNotNull('adjustment_at')
            ->orderBy('adjustment_at')
            ->get(['id', 'adjustment_type', 'adjustment_at'])
            ->each(function ($invoice) use (&$counters) {
                $date = \Carbon\Carbon::parse($invoice->adjustment_at);
                $fyStartYear = $date->month >= 4 ? $date->year : $date->year - 1;
                $prefix = $invoice->adjustment_type === 'credit_note' ? 'CN' : 'RV';
                $key = $prefix.'-'.$fyStartYear;
                $counters[$key] = ($counters[$key] ?? 0) + 1;

                DB::table('invoices')->where('id', $invoice->id)->update([
                    'adjustment_document_number' => $key.'-'.str_pad((string) $counters[$key], 4, '0', STR_PAD_LEFT),
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('adjustment_document_number');
        });
    }
};
