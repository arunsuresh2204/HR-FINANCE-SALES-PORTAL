<?php

namespace App\Livewire\Finance;

use App\Models\BillingRequest;
use App\Models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class BillingRequestIndex extends Component
{
    use WithPagination;

    public string $filter = 'pending';

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public bool $showConvertForm = false;

    public ?BillingRequest $converting = null;

    #[Validate('required|string|size:3')]
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
        $this->currency = $billingRequest->currency;
        $this->tax_percent = $this->currency === 'INR' ? '18' : '0';
        $this->client_tax_id = $billingRequest->client->tax_id ?? '';
        $this->due_date = now()->addDays(15)->toDateString();
        $this->showConvertForm = true;
    }

    public function createInvoice(): void
    {
        $this->validate();

        if (! in_array($this->currency, \App\Support\Currency::allCodes(), true)) {
            $this->addError('currency', 'That currency is not recognized.');

            return;
        }

        $billingRequest = $this->converting;

        $amount = (float) $billingRequest->amount;
        $taxPercent = $this->currency === 'INR' ? (float) $this->tax_percent : 0;
        $tax = $amount * ($taxPercent / 100);

        $lineItems = $billingRequest->isFromTask()
            ? $billingRequest->billedTasks->loadMissing('project')->map(fn ($task) => [
                'description' => $task->title,
                'amount' => $task->effectiveAmount(),
                'project_id' => $task->project_id,
                'project_name' => $task->project?->name,
            ])->all()
            : [['description' => $billingRequest->summary(), 'amount' => $amount]];

        DB::transaction(function () use ($billingRequest, $amount, $tax, $taxPercent, $lineItems) {
            $invoiceNumber = Invoice::nextInvoiceNumber();

            Invoice::create([
                'billing_request_id' => $billingRequest->id,
                'client_id' => $billingRequest->client_id,
                'project_id' => $billingRequest->project_id,
                'created_by' => Auth::id(),
                'invoice_number' => $invoiceNumber,
                'currency' => $this->currency,
                'amount' => $amount,
                'tax_percent' => $taxPercent,
                'total_amount' => $amount + $tax,
                'due_date' => $this->due_date,
                'status' => 'draft',
                'line_items' => $lineItems,
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

    public function deleteBillingRequest(BillingRequest $billingRequest): void
    {
        if ($billingRequest->status !== 'pending') {
            return;
        }

        $billingRequest->delete();
        $this->dispatch('toast', message: 'Billing request deleted.', type: 'success');
    }

    public function render()
    {
        $query = BillingRequest::with(['client', 'billedTasks', 'project'])->latest();

        if ($this->filter !== 'all') {
            $query->where('status', $this->filter);
        }

        return view('livewire.finance.billing-request-index', [
            'requests' => $query->paginate(10),
        ]);
    }
}
