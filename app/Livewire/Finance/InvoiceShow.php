<?php

namespace App\Livewire\Finance;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Component;

class InvoiceShow extends Component
{
    public Invoice $invoice;

    public bool $showPaymentForm = false;

    #[Validate('required|numeric|min:0.01')]
    public string $payment_amount = '';

    #[Validate('required|date|before_or_equal:today')]
    public string $payment_date = '';

    public bool $payment_completes_invoice = false;

    public bool $showAdjustmentForm = false;

    public string $adjustment_type = '';

    #[Validate('required|numeric|min:0.01')]
    public string $adjustment_amount = '';

    #[Validate('required|string|max:1000')]
    public string $adjustment_reason = '';

    public function mount(Invoice $invoice): void
    {
        $this->invoice = $invoice;
    }

    public function markSent(): void
    {
        $this->invoice->update(['status' => 'sent']);
        $this->dispatch('toast', message: 'Invoice marked as sent.', type: 'success');
    }

    public function cancel(): void
    {
        if ($this->invoice->isClosed() || (float) $this->invoice->amount_paid > 0) {
            return;
        }

        $this->invoice->update(['status' => 'cancelled']);
        $this->dispatch('toast', message: 'Invoice cancelled.', type: 'success');
    }

    public function openPaymentForm(): void
    {
        $this->payment_amount = '';
        $this->payment_date = now()->toDateString();
        $this->payment_completes_invoice = false;
        $this->resetValidation();
        $this->showPaymentForm = true;
    }

    public function recordPayment(): void
    {
        $rules = [
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date|before_or_equal:today',
        ];

        if ($this->invoice->currency === 'INR') {
            $rules['payment_amount'] .= '|max:'.$this->invoice->balanceDue();
        }

        $this->validate($rules);

        $this->invoice->payments()->create([
            'recorded_by' => Auth::id(),
            'amount' => $this->payment_amount,
            'payment_date' => $this->payment_date,
        ]);

        $this->invoice->recalculatePaid();

        if ($this->invoice->currency !== 'INR' && $this->payment_completes_invoice) {
            $this->invoice->update(['status' => 'paid']);
        }

        $this->showPaymentForm = false;
        $this->invoice->refresh();
        $this->dispatch('toast', message: 'Payment recorded.', type: 'success');
    }

    public function openAdjustmentForm(string $type): void
    {
        if (! in_array($type, ['credit_note', 'refund', 'written_off'], true)) {
            return;
        }

        $this->adjustment_type = $type;
        $this->adjustment_amount = number_format(match (true) {
            // A refund gives back what was actually received.
            $type === 'refund' => (float) $this->invoice->amount_paid,
            $this->invoice->currency === 'INR' => max(0, $this->invoice->balanceDue()),
            default => (float) $this->invoice->total_amount,
        }, 2, '.', '');
        $this->adjustment_reason = '';
        $this->resetValidation();
        $this->showAdjustmentForm = true;
    }

    public function saveAdjustment(): void
    {
        $this->validate([
            'adjustment_amount' => 'required|numeric|min:0.01',
            'adjustment_reason' => 'required|string|max:1000',
        ]);

        $status = $this->adjustment_type === 'refund' ? 'refunded' : $this->adjustment_type;

        $this->invoice->update([
            'status' => $status,
            'adjustment_type' => $this->adjustment_type,
            'adjustment_amount' => $this->adjustment_amount,
            'adjustment_reason' => $this->adjustment_reason,
            'adjustment_at' => now(),
            'adjusted_by' => Auth::id(),
        ]);

        // A refund pays real money back out, so it nets against the
        // salesperson's target for the month it happens; a credit note
        // (never collected) and a write-off (bad debt) don't touch a
        // payment that was never received.
        if ($this->adjustment_type === 'refund') {
            $this->invoice->payments()->create([
                'recorded_by' => Auth::id(),
                'amount' => -abs((float) $this->adjustment_amount),
                'payment_date' => now()->toDateString(),
                'notes' => 'Refund: '.$this->adjustment_reason,
            ]);

            // Keep the received-to-date figure in sync without letting the
            // usual paid/partially_paid recompute clobber the 'refunded'
            // status just set above.
            $this->invoice->update(['amount_paid' => (float) $this->invoice->payments()->sum('amount')]);
        }

        $this->showAdjustmentForm = false;
        $this->invoice->refresh();

        $label = match ($this->adjustment_type) {
            'credit_note' => 'Credit note issued.',
            'refund' => 'Refund recorded.',
            'written_off' => 'Invoice written off.',
        };
        $this->dispatch('toast', message: $label, type: 'success');
    }

    public function render()
    {
        return view('livewire.finance.invoice-show', [
            'payments' => $this->invoice->payments()->with('recordedBy')->latest('payment_date')->get(),
        ]);
    }
}
