<?php

namespace App\Livewire\Finance;

use App\Models\Currency;
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

    public bool $showAddCurrencyForm = false;

    #[Validate('required|alpha|size:3|unique:currencies,code')]
    public string $currency_code = '';

    #[Validate('required|string|max:8')]
    public string $currency_symbol = '';

    #[Validate('required|in:standard,european,indian')]
    public string $currency_format_style = 'standard';

    #[Validate('boolean')]
    public bool $currency_symbol_spaced = false;

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

    public function openAddCurrencyForm(): void
    {
        $this->reset(['currency_code', 'currency_symbol', 'currency_format_style', 'currency_symbol_spaced']);
        $this->currency_format_style = 'standard';
        $this->resetValidation();
        $this->showAddCurrencyForm = true;
    }

    public function addCurrency(): void
    {
        $this->validate();

        Currency::create([
            'code' => strtoupper($this->currency_code),
            'symbol' => $this->currency_symbol,
            'symbol_spaced' => $this->currency_symbol_spaced,
            'format_style' => $this->currency_format_style,
            'is_active' => true,
        ]);

        $this->reset(['currency_code', 'currency_symbol', 'currency_format_style', 'currency_symbol_spaced']);
        $this->showAddCurrencyForm = false;
        $this->dispatch('toast', message: 'Currency added.', type: 'success');
    }

    public function toggleCurrencyActive(int $id): void
    {
        $currency = Currency::findOrFail($id);
        $currency->update(['is_active' => ! $currency->is_active]);
    }

    public function render()
    {
        return view('livewire.finance.finance-settings', [
            'currencies' => Currency::orderBy('code')->get(),
        ]);
    }
}
