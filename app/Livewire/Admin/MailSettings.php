<?php

namespace App\Livewire\Admin;

use App\Models\MailSetting;
use Livewire\Attributes\Validate;
use Livewire\Component;

class MailSettings extends Component
{
    public MailSetting $setting;

    #[Validate('required|in:php_mail,smtp')]
    public string $mail_type = 'php_mail';

    #[Validate('required_if:mail_type,smtp|nullable|string|max:255')]
    public string $smtp_host = '';

    #[Validate('required_if:mail_type,smtp|nullable|integer|min:1|max:65535')]
    public string $smtp_port = '';

    #[Validate('nullable|string|max:255')]
    public string $smtp_username = '';

    #[Validate('nullable|string|max:255')]
    public string $smtp_password = '';

    #[Validate('nullable|in:none,tls,ssl')]
    public string $smtp_encryption = 'tls';

    #[Validate('required_if:mail_type,smtp|nullable|email|max:255')]
    public string $smtp_from_address = '';

    #[Validate('nullable|string|max:255')]
    public string $smtp_from_name = '';

    public function mount(): void
    {
        $this->setting = MailSetting::current();
        $this->mail_type = $this->setting->mail_type ?? 'php_mail';
        $this->smtp_host = $this->setting->smtp_host ?? '';
        $this->smtp_port = (string) ($this->setting->smtp_port ?? '');
        $this->smtp_username = $this->setting->smtp_username ?? '';
        $this->smtp_encryption = $this->setting->smtp_encryption ?? 'tls';
        $this->smtp_from_address = $this->setting->smtp_from_address ?? '';
        $this->smtp_from_name = $this->setting->smtp_from_name ?? '';
        // smtp_password is never pre-filled — leaving it blank on save keeps the existing one.
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'mail_type' => $this->mail_type,
            'smtp_host' => $this->smtp_host ?: null,
            'smtp_port' => $this->smtp_port !== '' ? (int) $this->smtp_port : null,
            'smtp_username' => $this->smtp_username ?: null,
            'smtp_encryption' => $this->smtp_encryption ?: null,
            'smtp_from_address' => $this->smtp_from_address ?: null,
            'smtp_from_name' => $this->smtp_from_name ?: null,
        ];

        if ($this->smtp_password !== '') {
            $data['smtp_password'] = $this->smtp_password;
        }

        if ($this->setting->exists) {
            $this->setting->update($data);
        } else {
            $this->setting = MailSetting::create($data);
        }

        $this->smtp_password = '';
        $this->dispatch('toast', message: 'Mail settings saved.', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.mail-settings');
    }
}
