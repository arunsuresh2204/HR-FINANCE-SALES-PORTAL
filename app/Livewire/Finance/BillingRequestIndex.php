<?php

namespace App\Livewire\Finance;

use App\Models\BillingRequest;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Component;

class BillingRequestIndex extends Component
{
    public string $filter = 'pending';

    public bool $showConvertForm = false;

    public ?BillingRequest $converting = null;

    #[Validate('required|in:INR,USD,EUR')]
    public string $currency = 'INR';

    #[Validate('required|numeric|min:0')]
    public string $tax_percent = '0';

    #[Validate('nullable|string|max:50')]
    public string $client_tax_id = '';

    #[Validate('required|date')]
    public string $due_date = '';

    public function openConvert(BillingRequest $billingRequest): void
    {
        $this->converting = $billingRequest;
        $this->currency = 'INR';
        $this->tax_percent = '18';
        $this->client_tax_id = $billingRequest->client->tax_id ?? '';
        $this->due_date = now()->addDays(15)->toDateString();
        $this->showConvertForm = true;
    }

    public function updatedCurrency(): void
    {
        if ($this->currency !== 'INR') {
            $this->tax_percent = '0';
        }
    }

    public function createInvoice(): void
    {
        $this->validate();

        $billingRequest = $this->converting;
        $amount = (float) $billingRequest->amount;
        $taxPercent = $this->currency === 'INR' ? (float) $this->tax_percent : 0;
        $tax = $amount * ($taxPercent / 100);

        DB::transaction(function () use ($billingRequest, $amount, $tax, $taxPercent) {
            $invoiceNumber = 'INV-'.now()->year.'-'.str_pad((string) (Invoice::count() + 1), 4, '0', STR_PAD_LEFT);

            Invoice::create([
                'billing_request_id' => $billingRequest->id,
                'client_id' => $billingRequest->client_id,
                'created_by' => Auth::id(),
                'invoice_number' => $invoiceNumber,
                'currency' => $this->currency,
                'amount' => $amount,
                'tax_percent' => $taxPercent,
                'total_amount' => $amount + $tax,
                'due_date' => $this->due_date,
                'status' => 'draft',
                'line_items' => [['description' => $billingRequest->milestone_description, 'amount' => $amount]],
            ]);

            if ($this->client_tax_id !== ($billingRequest->client->tax_id ?? '')) {
                $billingRequest->client->update(['tax_id' => $this->client_tax_id ?: null]);
            }

            $billingRequest->update(['status' => 'invoiced']);
        });

        $this->converting = null;
        $this->showConvertForm = false;
        $this->dispatch('toast', message: 'Invoice created from billing request.', type: 'success');
    }

    public function reject(BillingRequest $billingRequest): void
    {
        $billingRequest->update(['status' => 'rejected']);
        $this->dispatch('toast', message: 'Billing request rejected.', type: 'success');
    }

    public function render()
    {
        $query = BillingRequest::with('client')->latest();

        if ($this->filter !== 'all') {
            $query->where('status', $this->filter);
        }

        return view('livewire.finance.billing-request-index', [
            'requests' => $query->get(),
        ]);
    }
}
