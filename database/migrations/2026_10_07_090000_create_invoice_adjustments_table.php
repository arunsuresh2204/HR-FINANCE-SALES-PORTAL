<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // credit_note | refund | written_off
            $table->decimal('amount', 12, 2);
            // The invoice's own currency for a credit note / write-off;
            // always INR for a refund, since that's real cash paid back.
            $table->string('currency', 3);
            $table->text('reason');
            // Only a credit note or refund gets a numbered document.
            $table->string('document_number')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // Each invoice could only ever hold one adjustment before this
        // table existed — carry that single record over as this invoice's
        // first (and so far only) adjustment, in the same order they were
        // originally issued, before the old columns are dropped below.
        DB::table('invoices')
            ->whereNotNull('adjustment_type')
            ->orderBy('adjustment_at')
            ->get(['id', 'currency', 'adjustment_type', 'adjustment_amount', 'adjustment_reason', 'adjustment_document_number', 'adjusted_by', 'adjustment_at'])
            ->each(function ($invoice) {
                DB::table('invoice_adjustments')->insert([
                    'invoice_id' => $invoice->id,
                    'type' => $invoice->adjustment_type,
                    'amount' => $invoice->adjustment_amount,
                    'currency' => $invoice->adjustment_type === 'refund' ? 'INR' : $invoice->currency,
                    'reason' => $invoice->adjustment_reason ?? '',
                    'document_number' => $invoice->adjustment_document_number,
                    'created_by' => $invoice->adjusted_by,
                    'created_at' => $invoice->adjustment_at,
                    'updated_at' => $invoice->adjustment_at,
                ]);
            });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('adjusted_by');
            $table->dropColumn(['adjustment_type', 'adjustment_amount', 'adjustment_reason', 'adjustment_at', 'adjustment_document_number']);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('adjustment_type')->nullable()->after('status');
            $table->decimal('adjustment_amount', 12, 2)->nullable()->after('adjustment_type');
            $table->text('adjustment_reason')->nullable()->after('adjustment_amount');
            $table->timestamp('adjustment_at')->nullable()->after('adjustment_reason');
            $table->string('adjustment_document_number')->nullable()->after('adjustment_at');
            $table->foreignId('adjusted_by')->nullable()->after('adjustment_document_number')->constrained('users')->nullOnDelete();
        });

        // Best-effort restore: only the latest adjustment per invoice fits
        // back into the single-slot columns this rolls back to.
        DB::table('invoice_adjustments')
            ->orderBy('created_at')
            ->get()
            ->groupBy('invoice_id')
            ->each(function ($rows, $invoiceId) {
                $latest = $rows->last();

                DB::table('invoices')->where('id', $invoiceId)->update([
                    'adjustment_type' => $latest->type,
                    'adjustment_amount' => $latest->amount,
                    'adjustment_reason' => $latest->reason,
                    'adjustment_at' => $latest->created_at,
                    'adjustment_document_number' => $latest->document_number,
                    'adjusted_by' => $latest->created_by,
                ]);
            });

        Schema::dropIfExists('invoice_adjustments');
    }
};
