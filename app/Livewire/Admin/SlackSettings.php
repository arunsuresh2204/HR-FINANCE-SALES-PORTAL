<?php

namespace App\Livewire\Admin;

use App\Models\SlackSetting;
use Livewire\Attributes\Validate;
use Livewire\Component;

class SlackSettings extends Component
{
    public SlackSetting $setting;

    #[Validate('nullable|url|starts_with:https://hooks.slack.com/')]
    public string $general_webhook_url = '';

    public function mount(): void
    {
        $this->setting = SlackSetting::current();
        $this->general_webhook_url = $this->setting->general_webhook_url ?? '';
    }

    public function save(): void
    {
        $this->validate();

        $data = ['general_webhook_url' => $this->general_webhook_url ?: null];

        if ($this->setting->exists) {
            $this->setting->update($data);
        } else {
            $this->setting = SlackSetting::create($data);
        }

        $this->dispatch('toast', message: 'Slack settings saved.', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.slack-settings');
    }
}
