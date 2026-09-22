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

    public bool $showEditForm = false;

    public array $edit_line_items = [];

    public string $edit_tax_percent = '0';

    public string $edit_due_date = '';

    public bool $showRejectRequestForm = false;

    public ?int $rejectingRequestId = null;

    #[Validate('required|string|max:1000')]
    public string $reject_notes = '';

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
        if (! $this->invoice->canBeCancelled()) {
            return;
        }

        $this->invoice->update(['status' => 'cancelled']);
        $this->dispatch('toast', message: 'Invoice cancelled.', type: 'success');
    }

    public function openEditForm(): void
    {
        if (! $this->invoice->isEditable() || $this->invoice->pendingEditRequest) {
            return;
        }

        $lineItems = $this->invoice->line_items ?: [['description' => $this->invoice->billingRequest?->summary() ?? 'Services rendered', 'amount' => $this->invoice->amount]];
        $this->edit_line_items = collect($lineItems)->map(fn ($item) => [
            'description' => $item['description'] ?? '',
            'amount' => (string) ($item['amount'] ?? 0),
        ])->all();
        $this->edit_tax_percent = (string) $this->invoice->tax_percent;
        $this->edit_due_date = $this->invoice->due_date?->toDateString() ?? '';
        $this->resetValidation();
        $this->showEditForm = true;
    }

    public function addEditLineItem(): void
    {
        $this->edit_line_items[] = ['description' => '', 'amount' => ''];
    }

    public function removeEditLineItem(int $index): void
    {
        if (count($this->edit_line_items) > 1) {
            unset($this->edit_line_items[$index]);
            $this->edit_line_items = array_values($this->edit_line_items);
        }
    }

    public function saveEdit(): void
    {
        if (! $this->invoice->isEditable() || $this->invoice->pendingEditRequest) {
            return;
        }

        $this->validate([
            'edit_line_items' => 'required|array|min:1',
            'edit_line_items.*.description' => 'required|string|max:255',
            'edit_line_items.*.amount' => 'required|numeric|min:0.01',
            'edit_tax_percent' => 'required|numeric|min:0',
            'edit_due_date' => 'required|date',
        ]);

        $lineItems = collect($this->edit_line_items)->map(fn ($item) => [
            'description' => $item['description'],
            'amount' => (float) $item['amount'],
        ])->values()->all();

        $amount = array_sum(array_column($lineItems, 'amount'));
        $taxPercent = $this->invoice->currency === 'INR' ? (float) $this->edit_tax_percent : 0;
        $newTotal = $amount + ($amount * ($taxPercent / 100));
        $oldTotal = (float) $this->invoice->total_amount;

        $this->invoice->update([
            'line_items' => $lineItems,
            'amount' => $amount,
            'tax_percent' => $taxPercent,
            'total_amount' => $newTotal,
            'due_date' => $this->edit_due_date,
        ]);

        $this->logAmountChangeIfAny($oldTotal, $newTotal);

        $this->showEditForm = false;
        $this->invoice->refresh();
        $this->dispatch('toast', message: 'Invoice updated.', type: 'success');
    }

    public function deleteInvoice(): void
    {
        if (! $this->invoice->canBeDeleted()) {
            return;
        }

        $this->invoice->billingRequest?->update(['status' => 'pending']);
        $this->invoice->delete();

        $this->dispatch('toast', message: 'Invoice deleted.', type: 'success');
        $this->redirect(route('finance.invoices'), navigate: true);
    }

    protected function logAmountChangeIfAny(float $oldTotal, float $newTotal): void
    {
        if (abs($newTotal - $oldTotal) < 0.01) {
            return;
        }

        $this->invoice->amountChanges()->create([
            'old_amount' => $oldTotal,
            'new_amount' => $newTotal,
            'changed_by' => Auth::id(),
        ]);
    }

    public function approveEditRequest(int $requestId): void
    {
        $request = $this->invoice->editRequests()->where('status', 'pending')->findOrFail($requestId);

        if (! $this->invoice->isEditable()) {
            return;
        }

        $oldTotal = (float) $this->invoice->total_amount;
        $newTotal = (float) $request->total_amount;

        $this->invoice->update([
            'line_items' => $request->line_items,
            'amount' => $request->amount,
            'tax_percent' => $request->tax_percent,
            'total_amount' => $newTotal,
            'due_date' => $request->due_date,
        ]);

        $this->logAmountChangeIfAny($oldTotal, $newTotal);

        $request->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        $this->invoice->refresh();
        $this->dispatch('toast', message: 'Edit request approved and applied.', type: 'success');
    }

    public function openRejectEditRequestForm(int $requestId): void
    {
        $this->rejectingRequestId = $requestId;
        $this->reject_notes = '';
        $this->resetValidation();
        $this->showRejectRequestForm = true;
    }

    public function rejectEditRequest(): void
    {
        $this->validate(['reject_notes' => 'required|string|max:1000']);

        $request = $this->invoice->editRequests()->where('status', 'pending')->findOrFail($this->rejectingRequestId);

        $request->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'review_notes' => $this->reject_notes,
        ]);

        $this->showRejectRequestForm = false;
        $this->dispatch('toast', message: 'Edit request rejected.', type: 'success');
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
        $allowed = match ($type) {
            'credit_note' => $this->invoice->canIssueCreditNote(),
            'refund' => $this->invoice->canRecordRefund(),
            'written_off' => $this->invoice->canBeWrittenOff(),
            default => false,
        };

        if (! $allowed) {
            return;
        }

        $this->adjustment_type = $type;
        $this->adjustment_amount = number_format(
            // A refund gives back what was actually received; a credit
            // note or write-off reduces what's still owed in the
            // invoice's own currency.
            $type === 'refund' ? (float) $this->invoice->amount_paid : max(0, $this->invoice->balanceDue()),
            2, '.', ''
        );
        $this->adjustment_reason = '';
        $this->resetValidation();
        $this->showAdjustmentForm = true;
    }

    public function saveAdjustment(): void
    {
        $allowed = match ($this->adjustment_type) {
            'credit_note' => $this->invoice->canIssueCreditNote(),
            'refund' => $this->invoice->canRecordRefund(),
            'written_off' => $this->invoice->canBeWrittenOff(),
            default => false,
        };

        if (! $allowed) {
            $this->showAdjustmentForm = false;

            return;
        }

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
            'pendingEditRequest' => $this->invoice->editRequests()->where('status', 'pending')->with('requestedBy')->latest()->first(),
            'amountChanges' => $this->invoice->amountChanges()->with('changedBy')->latest()->get(),
        ]);
    }
}
