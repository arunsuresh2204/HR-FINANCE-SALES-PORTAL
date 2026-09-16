<?php

namespace App\Livewire\Finance;

use App\Models\FinanceSetting;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class FinanceSettings extends Component
{
    use WithFileUploads;

    public FinanceSetting $setting;

    #[Validate('nullable|image|max:2048')]
    public $signature = null;

    #[Validate('nullable|string|max:255')]
    public string $signer_name = '';

    #[Validate('nullable|string|max:255')]
    public string $signer_designation = '';

    #[Validate('nullable|string|max:20')]
    public string $gstin = '';

    public function mount(): void
    {
        $this->setting = FinanceSetting::current();
        $this->signer_name = $this->setting->signer_name ?? config('company.signatory_name');
        $this->signer_designation = $this->setting->signer_designation ?? config('company.signatory_title');
        $this->gstin = $this->setting->gstin ?? '';
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'signer_name' => $this->signer_name ?: null,
            'signer_designation' => $this->signer_designation ?: null,
            'gstin' => $this->gstin ?: null,
        ];

        if ($this->signature) {
            $data['signature_path'] = $this->signature->store('signatures', 'public');
        }

        if ($this->setting->exists) {
            $this->setting->update($data);
        } else {
            $this->setting = FinanceSetting::create($data);
        }

        $this->signature = null;
        $this->dispatch('toast', message: 'Finance settings saved.', type: 'success');
    }

    public function render()
    {
        return view('livewire.finance.finance-settings');
    }
}
