<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Before Project::ensureDevelopers() existed, assigning someone to a
     * task never added them as a project Developer, so on a project with
     * no developers staffed the assignee had no way to see the project or
     * their own task. Backfill: every existing task assignee becomes a
     * developer on that task's project (skipping the project's own
     * assigned_to, who already has full access).
     */
    public function up(): void
    {
        $pairs = DB::table('task_assignees')
            ->join('tasks', 'tasks.id', '=', 'task_assignees.task_id')
            ->join('projects', 'projects.id', '=', 'tasks.project_id')
            ->select('projects.id as project_id', 'projects.assigned_to', 'task_assignees.user_id')
            ->distinct()
            ->get();

        $now = now();

        foreach ($pairs as $pair) {
            if ($pair->assigned_to == $pair->user_id) {
                continue;
            }

            $exists = DB::table('project_developers')
                ->where('project_id', $pair->project_id)
                ->where('user_id', $pair->user_id)
                ->exists();

            if (! $exists) {
                DB::table('project_developers')->insert([
                    'project_id' => $pair->project_id,
                    'user_id' => $pair->user_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Data backfill only — not reversible without risking loss of
        // legitimate developer assignments made afterward.
    }
};
