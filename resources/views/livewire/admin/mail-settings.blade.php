<div>
    <x-page-header title="Mail Settings" subtitle="Choose how the portal sends outgoing email." />

    <div class="glass-card max-w-2xl">
        <form wire:submit="save" class="space-y-5">
            <div>
                <x-input-label value="Mail Type" />
                <div class="mt-1 grid grid-cols-2 gap-3">
                    <label class="glass-inset flex cursor-pointer items-center gap-2 p-3 text-sm {{ $mail_type === 'php_mail' ? 'ring-1 ring-gold-400/50' : '' }}">
                        <input type="radio" wire:model.live="mail_type" value="php_mail" class="text-gold-400 focus:ring-gold-400/40">
                        <span>PHP Mail <span class="text-white/40">(default)</span></span>
                    </label>
                    <label class="glass-inset flex cursor-pointer items-center gap-2 p-3 text-sm {{ $mail_type === 'smtp' ? 'ring-1 ring-gold-400/50' : '' }}">
                        <input type="radio" wire:model.live="mail_type" value="smtp" class="text-gold-400 focus:ring-gold-400/40">
                        <span>SMTP</span>
                    </label>
                </div>
                <p class="mt-1 text-xs text-white/40">
                    @if ($mail_type === 'php_mail')
                        Sends through PHP's built-in mail() function, using whatever local mail transfer agent the server is configured with. Simplest option, but depends on the server having outbound mail already set up (sendmail on Linux, or an SMTP relay configured in php.ini on Windows).
                    @else
                        Sends through an external SMTP server &mdash; more reliable deliverability, needs real credentials below.
                    @endif
                </p>
            </div>

            @if ($mail_type === 'smtp')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="SMTP Host" for="smtp_host" />
                        <x-text-input wire:model="smtp_host" id="smtp_host" type="text" class="mt-0" placeholder="smtp.gmail.com" />
                        <x-input-error :messages="$errors->get('smtp_host')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Port" for="smtp_port" />
                        <x-text-input wire:model="smtp_port" id="smtp_port" type="number" class="mt-0" placeholder="587" />
                        <x-input-error :messages="$errors->get('smtp_port')" class="mt-1" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label value="Username" for="smtp_username" />
                        <x-text-input wire:model="smtp_username" id="smtp_username" type="text" class="mt-0" placeholder="noreply@nexstarc.com" />
                        <x-input-error :messages="$errors->get('smtp_username')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="Password" for="smtp_password" />
                        <x-text-input wire:model="smtp_password" id="smtp_password" type="password" class="mt-0" placeholder="{{ $setting->exists && $setting->smtp_host ? 'Leave blank to keep current password' : '' }}" />
                        <x-input-error :messages="$errors->get('smtp_password')" class="mt-1" />
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <x-input-label value="Encryption" for="smtp_encryption" />
                        <select wire:model="smtp_encryption" id="smtp_encryption" class="input-glass">
                            <option value="tls">TLS (recommended, port 587)</option>
                            <option value="ssl">SSL (port 465)</option>
                            <option value="none">None</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label value="From Address" for="smtp_from_address" />
                        <x-text-input wire:model="smtp_from_address" id="smtp_from_address" type="email" class="mt-0" placeholder="noreply@nexstarc.com" />
                        <x-input-error :messages="$errors->get('smtp_from_address')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label value="From Name" for="smtp_from_name" />
                        <x-text-input wire:model="smtp_from_name" id="smtp_from_name" type="text" class="mt-0" placeholder="Nexstarc Portal" />
                    </div>
                </div>
            @endif

            <p class="rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-xs text-white/50">
                GST filing and other finance emails always send from and reply to <strong class="text-white/70">{{ config('mail.finance_notification_address') }}</strong>, regardless of this setting.
            </p>

            <div class="flex justify-end pt-2">
                <x-primary-button>Save Settings</x-primary-button>
            </div>
        </form>
    </div>
</div>
