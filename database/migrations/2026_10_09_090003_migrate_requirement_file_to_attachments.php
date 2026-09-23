<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('projects')->whereNotNull('requirement_file')->orderBy('id')->each(function ($project) {
            DB::table('attachments')->insert([
                'attachable_type' => \App\Models\Project::class,
                'attachable_id' => $project->id,
                'path' => $project->requirement_file,
                'original_name' => basename($project->requirement_file),
                'size' => null,
                'uploaded_by' => $project->created_by,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('requirement_file');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('requirement_file')->nullable()->after('description');
        });

        DB::table('attachments')
            ->where('attachable_type', \App\Models\Project::class)
            ->orderBy('id')
            ->get()
            ->groupBy('attachable_id')
            ->each(function ($attachments, $projectId) {
                DB::table('projects')->where('id', $projectId)->update([
                    'requirement_file' => $attachments->first()->path,
                ]);
            });
    }
};
