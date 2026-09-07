<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $phone = '';
    public string $address = '';
    public string $emergency_contact_name = '';
    public string $emergency_contact_phone = '';
    public string $bank_name = '';
    public string $bank_account_number = '';
    public string $bank_ifsc = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->phone = $user->phone ?? '';
        $this->address = $user->address ?? '';
        $this->emergency_contact_name = $user->emergency_contact_name ?? '';
        $this->emergency_contact_phone = $user->emergency_contact_phone ?? '';
        $this->bank_name = $user->bank_name ?? '';
        $this->bank_account_number = $user->bank_account_number ?? '';
        $this->bank_ifsc = $user->bank_ifsc ?? '';
    }

    public function updateEmployeeProfile(): void
    {
        $validated = $this->validate([
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
            'bank_ifsc' => ['nullable', 'string', 'max:50'],
        ]);

        Auth::user()->update($validated);

        $this->dispatch('employee-profile-updated');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-bold text-white">{{ __('Contact & Banking') }}</h2>
        <p class="mt-1 text-sm text-white/50">{{ __('Update your contact details, emergency contact and payroll banking information.') }}</p>
    </header>

    <form wire:submit="updateEmployeeProfile" class="mt-6 space-y-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="phone" value="Phone" />
                <x-text-input wire:model="phone" id="phone" type="text" class="mt-1 w-full" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="address" value="Address" />
                <x-text-input wire:model="address" id="address" type="text" class="mt-1 w-full" />
                <x-input-error :messages="$errors->get('address')" class="mt-2" />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="emergency_contact_name" value="Emergency Contact Name" />
                <x-text-input wire:model="emergency_contact_name" id="emergency_contact_name" type="text" class="mt-1 w-full" />
            </div>
            <div>
                <x-input-label for="emergency_contact_phone" value="Emergency Contact Phone" />
                <x-text-input wire:model="emergency_contact_phone" id="emergency_contact_phone" type="text" class="mt-1 w-full" />
            </div>
        </div>

        <div class="border-t border-white/10 pt-6">
            <p class="mb-4 text-xs font-semibold uppercase tracking-wide text-white/40">Banking Info (for Payroll)</p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <x-input-label for="bank_name" value="Bank Name" />
                    <x-text-input wire:model="bank_name" id="bank_name" type="text" class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label for="bank_account_number" value="Account Number" />
                    <x-text-input wire:model="bank_account_number" id="bank_account_number" type="text" class="mt-1 w-full" />
                </div>
                <div>
                    <x-input-label for="bank_ifsc" value="IFSC / Routing Code" />
                    <x-text-input wire:model="bank_ifsc" id="bank_ifsc" type="text" class="mt-1 w-full" />
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
            <x-action-message on="employee-profile-updated">{{ __('Saved.') }}</x-action-message>
        </div>
    </form>
</section>
