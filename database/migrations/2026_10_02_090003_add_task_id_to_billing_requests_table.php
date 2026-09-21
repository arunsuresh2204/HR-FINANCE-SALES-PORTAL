<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_requests', function (Blueprint $table) {
            $table->foreignId('task_id')->nullable()->after('project_id')->constrained('tasks')->nullOnDelete();
            $table->string('client_response')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('billing_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('task_id');
            $table->dropColumn('client_response');
        });
    }
};
