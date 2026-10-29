<div>
    <x-page-header title="Slack Settings" subtitle="Where general notifications are posted." />

    <div class="glass-card max-w-2xl">
        <form wire:submit="save" class="space-y-5">
            <div>
                <x-input-label value="General Webhook URL" for="general_webhook_url" />
                <p class="mb-2 text-xs text-white/40">Create an Incoming Webhook in Slack for a general/notifications channel and paste its URL here. Used for resignations, leave requests, and new-project announcements — anything not tied to a specific project.</p>
                <x-text-input wire:model="general_webhook_url" id="general_webhook_url" type="text" class="mt-0" placeholder="https://hooks.slack.com/services/..." />
                <x-input-error :messages="$errors->get('general_webhook_url')" class="mt-1" />
            </div>

            <p class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-xs text-white/50">
                Each project can also have its own Slack channel webhook for task updates — set that from the project's edit form on its client's page.
            </p>

            <div class="flex justify-end pt-2">
                <x-primary-button>Save Settings</x-primary-button>
            </div>
        </form>
    </div>
</div>
