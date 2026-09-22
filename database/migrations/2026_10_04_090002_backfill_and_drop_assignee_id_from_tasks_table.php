<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('tasks')
            ->whereNotNull('assignee_id')
            ->get(['id', 'assignee_id'])
            ->each(fn ($row) => DB::table('task_assignees')->insertOrIgnore([
                'task_id' => $row->id,
                'user_id' => $row->assignee_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]));

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assignee_id');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('assignee_id')->nullable()->after('category_id')->constrained('users')->nullOnDelete();
        });
    }
};
