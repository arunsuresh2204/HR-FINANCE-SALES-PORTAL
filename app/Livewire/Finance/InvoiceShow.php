<?php

namespace App\Livewire\Finance;

use App\Models\Invoice;
use Livewire\Attributes\Validate;
use Livewire\Component;

class InvoiceShow extends Component
{
    public Invoice $invoice;

    public bool $showPaymentForm = false;

    #[Validate('required|numeric|min:0.01')]
    public string $payment_amount = '';

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice;
    }

    public function markSent(): void
    {
        $this->invoice->update(['status' => 'sent']);
        $this->dispatch('toast', message: 'Invoice marked as sent.', type: 'success');
    }

    public function openPaymentForm(): void
    {
        $this->payment_amount = number_format($this->invoice->balanceDue(), 2, '.', '');
        $this->showPaymentForm = true;
    }

    public function recordPayment(): void
    {
        $this->validate(['payment_amount' => 'required|numeric|min:0.01|max:'.$this->invoice->balanceDue()]);

        $newPaid = (float) $this->invoice->amount_paid + (float) $this->payment_amount;
        $status = $newPaid >= (float) $this->invoice->total_amount ? 'paid' : 'partially_paid';

        $this->invoice->update(['amount_paid' => $newPaid, 'status' => $status]);

        $this->showPaymentForm = false;
        $this->invoice->refresh();
        $this->dispatch('toast', message: 'Payment recorded.', type: 'success');
    }

    public function render()
    {
        return view('livewire.finance.invoice-show');
    }
}
