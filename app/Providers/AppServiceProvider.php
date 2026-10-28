<?php

namespace App\Providers;

use App\Mail\Transport\PhpMailTransport;
use App\Models\MailSetting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Mail::extend('phpmail', fn () => new PhpMailTransport);

        $this->applyMailSetting();
    }

    /**
     * The mail type (PHP mail() vs SMTP) is configurable at runtime from
     * Admin > Mail Settings rather than fixed in .env, so every outgoing
     * email in the app — GST filing, resignation notices, and anything
     * added later — picks it up without a redeploy. Falls back silently to
     * whatever .env/config already has if the setting can't be read yet
     * (e.g. the table doesn't exist until the first `artisan migrate` runs).
     */
    protected function applyMailSetting(): void
    {
        try {
            $setting = MailSetting::current();
        } catch (\Throwable) {
            return;
        }

        if (! $setting->exists) {
            return;
        }

        if ($setting->mail_type === 'smtp') {
            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.scheme' => $setting->smtp_encryption === 'ssl' ? 'smtps' : null,
                'mail.mailers.smtp.host' => $setting->smtp_host,
                'mail.mailers.smtp.port' => $setting->smtp_port,
                'mail.mailers.smtp.username' => $setting->smtp_username,
                'mail.mailers.smtp.password' => $setting->smtp_password,
            ]);

            if ($setting->smtp_from_address) {
                config([
                    'mail.from.address' => $setting->smtp_from_address,
                    'mail.from.name' => $setting->smtp_from_name ?: config('mail.from.name'),
                ]);
            }
        } else {
            config(['mail.default' => 'phpmail']);
        }
    }
}
