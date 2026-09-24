<?php

namespace App\Livewire\Finance;

use App\Models\BillingRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceIndex extends Component
{
    use WithPagination;

    public string $filter = 'all';

    public bool $showCreateForm = false;

    public ?int $salesperson_id = null;

    #[Validate('required|exists:clients,id')]
    public ?int $client_id = null;

    #[Validate('required|string|size:3')]
    public string $currency = 'INR';

    #[Validate('required|numeric|min:0')]
    public string $tax_percent = '0';

    #[Validate('nullable|string|max:50')]
    public string $client_tax_id = '';

    #[Validate('required|date')]
    public string $due_date = '';

    public array $selectedBillingRequestIds = [];

    public array $lineItems = [];

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateForm(): void
    {
        $this->reset(['salesperson_id', 'client_id', 'client_tax_id', 'selectedBillingRequestIds']);
        $this->currency = 'INR';
        $this->tax_percent = '18';
        $this->due_date = now()->addDays(15)->toDateString();
        $this->lineItems = [['description' => '', 'hours' => '', 'rate' => '', 'amount' => '']];
        $this->resetValidation();
        $this->showCreateForm = true;
    }

    public function updatedSalespersonId(): void
    {
        $this->client_id = null;
        $this->selectedBillingRequestIds = [];
    }

    public function updatedClientId(): void
    {
        $this->selectedBillingRequestIds = [];

        if ($this->client_id) {
            $client = Client::find($this->client_id);
            $this->client_tax_id = $client?->tax_id ?? '';
        }
    }

    public function updatedCurrency(): void
    {
        $this->selectedBillingRequestIds = [];

        if ($this->currency !== 'INR') {
            $this->tax_percent = '0';
        }
    }

    public function addLineItem(): void
    {
        $this->lineItems[] = ['description' => '', 'hours' => '', 'rate' => '', 'amount' => ''];
    }

    public function removeLineItem(int $index): void
    {
        if (count($this->lineItems) > 1) {
            unset($this->lineItems[$index]);
            $this->lineItems = array_values($this->lineItems);
        }
    }

    public function createInvoice(): void
    {
        $this->validate();

        if (! in_array($this->currency, \App\Support\Currency::codes(), true)) {
            $this->addError('currency', 'That currency is not active.');

            return;
        }

        $billingRequests = BillingRequest::whereIn('id', $this->selectedBillingRequestIds)
            ->where('client_id', $this->client_id)
            ->where('status', 'pending')
            ->get();

        $lineItems = $billingRequests->flatMap(function (BillingRequest $br) {
            if ($br->isFromTask()) {
                return $br->billedTasks->loadMissing('project')->map(fn ($task) => [
                    'description' => $task->title,
                    'amount' => $task->effectiveAmount(),
                    'project_id' => $task->project_id,
                    'project_name' => $task->project?->name,
                ]);
            }

            return collect([[
                'description' => $br->summary(),
                'amount' => (float) $br->amount,
                'project_id' => $br->project_id,
                'project_name' => $br->project?->name,
            ]]);
        })->values()->all();

        foreach ($this->lineItems as $item) {
            $description = trim($item['description'] ?? '');
            $hours = (float) ($item['hours'] ?? 0);
            $rate = (float) ($item['rate'] ?? 0);
            $amount = ($hours > 0 && $rate > 0) ? $hours * $rate : (float) ($item['amount'] ?? 0);

            if ($description !== '' && $amount > 0) {
                $lineItems[] = ['description' => $description, 'amount' => $amount];
            }
        }

        if (empty($lineItems)) {
            $this->addError('lineItems', 'Add at least one billing request or line item.');

            return;
        }

        $amount = array_sum(array_column($lineItems, 'amount'));
        $taxPercent = $this->currency === 'INR' ? (float) $this->tax_percent : 0;
        $tax = $amount * ($taxPercent / 100);

        DB::transaction(function () use ($lineItems, $amount, $tax, $taxPercent, $billingRequests) {
            $invoiceNumber = Invoice::nextInvoiceNumber();

            Invoice::create([
                'billing_request_id' => $billingRequests->first()?->id,
                'client_id' => $this->client_id,
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

            $client = Client::find($this->client_id);
            if ($this->client_tax_id !== ($client->tax_id ?? '')) {
                $client->update(['tax_id' => $this->client_tax_id ?: null]);
            }

            $billingRequests->each(fn (BillingRequest $br) => $br->update(['status' => 'invoiced']));
        });

        $this->showCreateForm = false;
        $this->dispatch('toast', message: 'Invoice created.', type: 'success');
    }

    public function render()
    {
        $query = Invoice::with('client')->latest();

        if ($this->filter !== 'all') {
            $query->where('status', $this->filter);
        }

        $clientsQuery = Client::query()->orderBy('business_name');
        if ($this->salesperson_id) {
            $clientsQuery->where('sales_person_id', $this->salesperson_id);
        }

        return view('livewire.finance.invoice-index', [
            'invoices' => $query->paginate(12),
            'salespeople' => User::permission(['access_sales_leads', 'access_sales_clients'])->orderBy('name')->get(),
            'clients' => $clientsQuery->get(),
            'pendingBillingRequests' => $this->client_id
                ? BillingRequest::where('client_id', $this->client_id)
                    ->where('status', 'pending')
                    ->where('currency', $this->currency)
                    ->with('project')
                    ->latest()
                    ->get()
                : collect(),
        ]);
    }
}
