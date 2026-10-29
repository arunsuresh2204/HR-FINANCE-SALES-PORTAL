<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Slack integration removed — the team uses Slack separately rather than
     * through the portal. Guarded with hasTable/hasColumn since some
     * deployments may never have actually run the original Slack migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('slack_settings')) {
            Schema::dropIfExists('slack_settings');
        }

        if (Schema::hasColumn('projects', 'slack_webhook_url')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('slack_webhook_url');
            });
        }
    }

    public function down(): void
    {
        // Intentional removal — not meant to be reversed. Re-add the
        // original 2026_10_18_* migrations if Slack integration is wanted
        // again later.
    }
};
