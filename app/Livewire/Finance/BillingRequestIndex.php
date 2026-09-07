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

    #[Validate('required|numeric|min:0')]
    public string $tax_percent = '0';

    #[Validate('required|date')]
    public string $due_date = '';

    public function openConvert(BillingRequest $billingRequest): void
    {
        $this->converting = $billingRequest;
        $this->tax_percent = '0';
        $this->due_date = now()->addDays(15)->toDateString();
        $this->showConvertForm = true;
    }

    public function createInvoice(): void
    {
        $this->validate();

        $billingRequest = $this->converting;
        $amount = (float) $billingRequest->amount;
        $tax = $amount * ((float) $this->tax_percent / 100);

        DB::transaction(function () use ($billingRequest, $amount, $tax) {
            $invoiceNumber = 'INV-'.now()->year.'-'.str_pad((string) (Invoice::count() + 1), 4, '0', STR_PAD_LEFT);

            Invoice::create([
                'billing_request_id' => $billingRequest->id,
                'client_id' => $billingRequest->client_id,
                'created_by' => Auth::id(),
                'invoice_number' => $invoiceNumber,
                'amount' => $amount,
                'tax_percent' => $this->tax_percent,
                'total_amount' => $amount + $tax,
                'due_date' => $this->due_date,
                'status' => 'draft',
                'line_items' => [['description' => $billingRequest->milestone_description, 'amount' => $amount]],
            ]);

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
