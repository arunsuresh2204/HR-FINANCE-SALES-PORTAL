<?php

namespace App\Livewire\Sales;

use App\Models\BillingRequest;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class ClientShow extends Component
{
    use WithFileUploads;

    public Client $client;

    public bool $showAgreementForm = false;

    #[Validate('nullable|file|max:10240')]
    public $agreement_file = null;

    #[Validate('nullable|date')]
    public string $agreement_effective_date = '';

    #[Validate('nullable|string|max:1000')]
    public string $agreement_scope_summary = '';

    public bool $showBillingForm = false;

    #[Validate('required|numeric|min:0.01')]
    public string $amount = '';

    #[Validate('required|string|max:255')]
    public string $milestone_description = '';

    public function mount(Client $client): void
    {
        $this->client = $client;
        $this->agreement_effective_date = $client->agreement_effective_date?->toDateString() ?? '';
        $this->agreement_scope_summary = $client->agreement_scope_summary ?? '';
    }

    public function saveAgreement(): void
    {
        $this->validate();

        $data = [
            'agreement_effective_date' => $this->agreement_effective_date ?: null,
            'agreement_scope_summary' => $this->agreement_scope_summary,
        ];

        if ($this->agreement_file) {
            $data['agreement_file'] = $this->agreement_file->store('agreements', 'public');
        }

        $this->client->update($data);
        $this->showAgreementForm = false;
        $this->dispatch('toast', message: 'Agreement details saved.', type: 'success');
    }

    public function openBillingForm(): void
    {
        $this->reset(['amount', 'milestone_description']);
        $this->showBillingForm = true;
    }

    public function submitBillingRequest(): void
    {
        $this->validate(['amount' => 'required|numeric|min:0.01', 'milestone_description' => 'required|string|max:255']);

        BillingRequest::create([
            'client_id' => $this->client->id,
            'created_by' => Auth::id(),
            'amount' => $this->amount,
            'milestone_description' => $this->milestone_description,
            'status' => 'pending',
        ]);

        $this->showBillingForm = false;
        $this->dispatch('toast', message: 'Billing request sent to Finance.', type: 'success');
    }

    public function render()
    {
        return view('livewire.sales.client-show', [
            'billingRequests' => $this->client->billingRequests()->latest()->get(),
            'invoices' => $this->client->invoices()->latest()->get(),
            'totalHours' => $this->client->timesheets()->sum('hours'),
        ]);
    }
}
