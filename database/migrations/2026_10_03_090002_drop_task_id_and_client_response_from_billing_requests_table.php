<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Superseded by the billed_tasks pivot (a billing request can now cover
 * several tasks, not one) and by dropping the client-approval gate on
 * invoicing — Sales creates the billing request directly, no client
 * response is recorded against it.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('billing_requests')
            ->whereNotNull('task_id')
            ->get(['id', 'task_id'])
            ->each(fn ($row) => DB::table('billed_tasks')->insertOrIgnore([
                'billing_request_id' => $row->id,
                'task_id' => $row->task_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]));

        Schema::table('billing_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('task_id');
            $table->dropColumn('client_response');
        });
    }

    public function down(): void
    {
        Schema::table('billing_requests', function (Blueprint $table) {
            $table->foreignId('task_id')->nullable()->after('project_id')->constrained('tasks')->nullOnDelete();
            $table->string('client_response')->nullable()->after('status');
        });
    }
};
