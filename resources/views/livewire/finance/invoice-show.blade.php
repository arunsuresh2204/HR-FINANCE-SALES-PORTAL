<div>
    <x-page-header :title="$invoice->invoice_number" :subtitle="$invoice->client->business_name">
        <x-slot:actions>
            <a href="{{ route('finance.invoices') }}" wire:navigate class="btn-glass-secondary"><x-icon name="arrow-right" class="h-4 w-4 rotate-180" /> Back</a>
            <a href="{{ route('finance.invoices.pdf', $invoice) }}" target="_blank" class="btn-glass-secondary"><x-icon name="document" class="h-4 w-4" /> PDF</a>
            @if ($invoice->status === 'draft')
                <button wire:click="markSent" class="btn-glass-secondary"><x-icon name="arrow-right" class="h-4 w-4" /> Mark Sent</button>
            @endif
            @if ($invoice->balanceDue() > 0)
                <button wire:click="openPaymentForm" class="btn-glass-primary"><x-icon name="cash" class="h-4 w-4" /> Record Payment</button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="glass-card lg:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-base font-bold text-white">Line Items</h2>
                <x-status-pill :status="$invoice->isOverdue() ? 'overdue' : $invoice->status" />
            </div>
            <table class="table-glass">
                <thead><tr><th>Description</th><th class="text-right">Amount</th></tr></thead>
                <tbody>
                    @foreach (($invoice->line_items ?: [['description' => 'Services rendered', 'amount' => $invoice->amount]]) as $item)
                        <tr><td>{{ $item['description'] ?? 'Item' }}</td><td class="text-right">${{ number_format($item['amount'] ?? 0, 2) }}</td></tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4 ml-auto max-w-xs space-y-1.5 text-sm">
                <div class="flex justify-between text-white/60"><span>Subtotal</span><span>${{ number_format($invoice->amount, 2) }}</span></div>
                <div class="flex justify-between text-white/60"><span>Tax ({{ $invoice->tax_percent }}%)</span><span>${{ number_format($invoice->total_amount - $invoice->amount, 2) }}</span></div>
                <div class="flex justify-between border-t border-white/10 pt-1.5 text-base font-bold text-white"><span>Total</span><span>${{ number_format($invoice->total_amount, 2) }}</span></div>
                <div class="flex justify-between text-emerald-300"><span>Paid</span><span>${{ number_format($invoice->amount_paid, 2) }}</span></div>
                <div class="flex justify-between font-semibold text-gold-300"><span>Balance Due</span><span>${{ number_format($invoice->balanceDue(), 2) }}</span></div>
            </div>
        </div>

        <div class="glass-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-white/40">Bill To</p>
            <p class="mt-2 font-semibold text-white">{{ $invoice->client->business_name }}</p>
            <p class="text-sm text-white/50">{{ $invoice->client->business_address }}</p>
            <div class="mt-4 border-t border-white/10 pt-4 text-sm">
                <div class="flex justify-between"><span class="text-white/40">Issued</span><span class="text-white">{{ $invoice->created_at->format('M j, Y') }}</span></div>
                <div class="flex justify-between"><span class="text-white/40">Due</span><span class="text-white">{{ $invoice->due_date?->format('M j, Y') ?? '—' }}</span></div>
            </div>
        </div>
    </div>

    <x-modal-glass wire-model="showPaymentForm" title="Record Payment">
        <form wire:submit="recordPayment" class="space-y-4">
            <div>
                <x-input-label for="payment_amount" value="Amount ($)" />
                <x-text-input wire:model="payment_amount" id="payment_amount" type="number" step="0.01" class="mt-0" />
                <x-input-error :messages="$errors->get('payment_amount')" class="mt-1" />
                <p class="mt-1 text-xs text-white/40">Balance due: ${{ number_format($invoice->balanceDue(), 2) }}</p>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Record Payment</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
