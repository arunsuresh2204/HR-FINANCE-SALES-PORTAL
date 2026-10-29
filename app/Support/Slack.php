<?php

namespace App\Support;

use App\Models\Project;
use App\Models\SlackSetting;
use Illuminate\Support\Facades\Http;

/**
 * Posts one-way notifications to Slack via Incoming Webhooks. Never throws —
 * a Slack outage or a bad/revoked webhook URL must never break the action
 * that triggered the notification (creating a task, submitting a
 * resignation, etc.), so every failure is swallowed and reported instead.
 */
class Slack
{
    /**
     * Post to a project's own Slack channel, if one is configured. Silently
     * does nothing otherwise — most projects won't have one set up.
     */
    public static function notifyProject(Project $project, string $message): void
    {
        if ($project->slack_webhook_url) {
            static::post($project->slack_webhook_url, $message);
        }
    }

    /**
     * Post to the catch-all channel configured in Admin > Slack Settings —
     * for HR events and anything else not tied to a specific project.
     */
    public static function notifyGeneral(string $message): void
    {
        $webhookUrl = SlackSetting::current()->general_webhook_url;

        if ($webhookUrl) {
            static::post($webhookUrl, $message);
        }
    }

    protected static function post(string $webhookUrl, string $message): void
    {
        try {
            Http::timeout(3)->post($webhookUrl, ['text' => $message]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
