<div>
    <x-page-header :title="$invoice->invoice_number" :subtitle="$invoice->client->business_name">
        <x-slot:actions>
            <a href="{{ route('finance.invoices') }}" wire:navigate class="btn-glass-secondary"><x-icon name="arrow-right" class="h-4 w-4 rotate-180" /> Back</a>
            <a href="{{ route('finance.invoices.pdf', $invoice) }}" target="_blank" class="btn-glass-secondary"><x-icon name="document" class="h-4 w-4" /> PDF</a>
            @if ($invoice->status === 'draft')
                <button wire:click="markSent" class="btn-glass-secondary"><x-icon name="arrow-right" class="h-4 w-4" /> Mark Sent</button>
                <button wire:click="cancel" class="btn-glass-secondary" onclick="return confirm('Cancel this invoice?')"><x-icon name="x" class="h-4 w-4" /> Cancel</button>
            @endif
            @if ($invoice->isEditable())
                <button wire:click="openEditForm" class="btn-glass-secondary"><x-icon name="pencil" class="h-4 w-4" /> Edit</button>
                <button wire:click="deleteInvoice" wire:confirm="Delete this invoice? This can't be undone and will remove any recorded payments." class="btn-glass-secondary !text-rose-300"><x-icon name="trash" class="h-4 w-4" /> Delete</button>
                <button wire:click="openPaymentForm" class="btn-glass-primary"><x-icon name="cash" class="h-4 w-4" /> Record Payment</button>
            @endif
        </x-slot:actions>
    </x-page-header>

    @if (! $invoice->isClosed())
        <div class="mb-6 flex flex-wrap gap-2">
            <button wire:click="openAdjustmentForm('credit_note')" class="text-xs font-semibold text-white/40 hover:text-white/70">Issue Credit Note</button>
            <span class="text-white/20">&middot;</span>
            <button wire:click="openAdjustmentForm('refund')" class="text-xs font-semibold text-white/40 hover:text-white/70">Record Refund</button>
            <span class="text-white/20">&middot;</span>
            <button wire:click="openAdjustmentForm('written_off')" class="text-xs font-semibold text-white/40 hover:text-white/70">Write Off</button>
        </div>
    @endif

    @if ($invoice->isClosed())
        <div class="glass-card mb-6 border border-rose-400/20">
            <p class="text-xs font-semibold uppercase tracking-wide text-rose-300">{{ str($invoice->status)->replace('_', ' ')->title() }}</p>
            @if ($invoice->adjustment_amount)
                <p class="mt-1 text-sm text-white">{{ \App\Support\Currency::format($invoice->adjustment_amount, 'INR') }}</p>
            @endif
            @if ($invoice->adjustment_reason)
                <p class="mt-1 text-sm text-white/60">{{ $invoice->adjustment_reason }}</p>
            @endif
            <p class="mt-1 text-xs text-white/30">{{ $invoice->adjustedBy?->name }} &middot; {{ $invoice->adjustment_at?->format('M j, Y') }}</p>
        </div>
    @endif

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
                        <tr><td>{{ $item['description'] ?? 'Item' }}</td><td class="text-right">{{ $invoice->money($item['amount'] ?? 0) }}</td></tr>
                    @endforeach
                </tbody>
            </table>

            @if ($invoice->isExport())
                <p class="mt-3 text-xs text-white/40">{{ $invoice->taxLabel() }}: 0% &middot; export of IT services is treated as zero-rated supply (Section 16, IGST Act).</p>
            @endif

            <div class="mt-4 ml-auto max-w-xs space-y-1.5 text-sm">
                <div class="flex justify-between text-white/60"><span>Subtotal</span><span>{{ $invoice->money($invoice->amount) }}</span></div>
                <div class="flex justify-between text-white/60"><span>{{ $invoice->taxLabel() }} ({{ rtrim(rtrim(number_format((float) $invoice->tax_percent, 2), '0'), '.') }}%)</span><span>{{ $invoice->money($invoice->total_amount - $invoice->amount) }}</span></div>
                <div class="flex justify-between border-t border-white/10 pt-1.5 text-base font-bold text-white"><span>Total</span><span>{{ $invoice->money($invoice->total_amount) }}</span></div>
                <div class="flex justify-between text-emerald-300"><span>Received (INR)</span><span>{{ \App\Support\Currency::format($invoice->amount_paid, 'INR') }}</span></div>
                @if ($invoice->currency === 'INR')
                    <div class="flex justify-between font-semibold text-gold-300"><span>Balance Due</span><span>{{ $invoice->money($invoice->balanceDue()) }}</span></div>
                @endif
            </div>

            @if ($payments->isNotEmpty())
                <div class="mt-6 border-t border-white/10 pt-4">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-white/40">Payment History</p>
                    <div class="space-y-1.5">
                        @foreach ($payments as $payment)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-white/60">{{ $payment->payment_date->format('M j, Y') }} &middot; {{ $payment->recordedBy->name }}</span>
                                <span class="{{ $payment->amount < 0 ? 'text-rose-300' : 'text-white' }}">{{ \App\Support\Currency::format($payment->amount, 'INR') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="glass-card">
            <p class="text-xs font-semibold uppercase tracking-wide text-white/40">Bill To</p>
            <p class="mt-2 font-semibold text-white">{{ $invoice->client->business_name }}</p>
            <p class="text-sm text-white/50">{{ $invoice->client->business_address }}</p>
            @if ($invoice->client->tax_id)
                <p class="mt-1 text-xs text-white/40">{{ $invoice->clientTaxIdLabel() }}: {{ $invoice->client->tax_id }}</p>
            @endif
            <div class="mt-4 border-t border-white/10 pt-4 text-sm">
                <div class="flex justify-between"><span class="text-white/40">Issued</span><span class="text-white">{{ $invoice->created_at->format('M j, Y') }}</span></div>
                <div class="flex justify-between"><span class="text-white/40">Due</span><span class="text-white">{{ $invoice->due_date?->format('M j, Y') ?? '—' }}</span></div>
            </div>
        </div>
    </div>

    <x-modal-glass wire-model="showPaymentForm" title="Record Payment">
        <form wire:submit="recordPayment" class="space-y-4">
            <div>
                <x-input-label value="Amount Received (INR)" for="payment_amount" />
                <x-text-input wire:model="payment_amount" id="payment_amount" type="number" step="0.01" class="mt-0" />
                <x-input-error :messages="$errors->get('payment_amount')" class="mt-1" />
                @if ($invoice->currency === 'INR')
                    <p class="mt-1 text-xs text-white/40">Balance due: {{ $invoice->money($invoice->balanceDue()) }}</p>
                @else
                    <p class="mt-1 text-xs text-white/40">Invoice total: {{ $invoice->money($invoice->total_amount) }} &mdash; enter what actually landed in the bank, in INR.</p>
                @endif
            </div>
            <div>
                <x-input-label value="Payment Date" for="payment_date" />
                <x-text-input wire:model="payment_date" id="payment_date" type="date" class="mt-0" />
                <x-input-error :messages="$errors->get('payment_date')" class="mt-1" />
            </div>
            @if ($invoice->currency !== 'INR')
                <label class="flex items-center gap-2 text-sm text-white/70">
                    <input type="checkbox" wire:model="payment_completes_invoice" class="rounded border-white/20 bg-white/5 text-gold-400 focus:ring-gold-400/40">
                    This payment completes the invoice in full
                </label>
            @endif
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Record Payment</x-primary-button>
            </div>
        </form>
    </x-modal-glass>

    <x-modal-glass wire-model="showEditForm" title="Edit Invoice" max-width="2xl">
        <form wire:submit="saveEdit" class="space-y-4">
            <div>
                <div class="mb-2 flex items-center justify-between">
                    <x-input-label value="Line Items" class="mb-0" />
                    <button type="button" wire:click="addEditLineItem" class="text-xs font-semibold text-gold-300 hover:text-gold-200">+ Add Line Item</button>
                </div>
                <div class="space-y-2">
                    @foreach ($edit_line_items as $index => $item)
                        <div class="glass-inset flex items-start gap-2 p-2.5" wire:key="edit-line-item-{{ $index }}">
                            <div class="flex-1">
                                <x-text-input wire:model="edit_line_items.{{ $index }}.description" type="text" class="mt-0" placeholder="Description" />
                            </div>
                            <div class="w-32 shrink-0">
                                <x-text-input wire:model="edit_line_items.{{ $index }}.amount" type="number" step="0.01" class="mt-0" placeholder="Amount" />
                            </div>
                            @if (count($edit_line_items) > 1)
                                <button type="button" wire:click="removeEditLineItem({{ $index }})" class="mt-2 shrink-0 text-white/30 hover:text-rose-300"><x-icon name="trash" class="h-4 w-4" /></button>
                            @endif
                        </div>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('edit_line_items')" class="mt-1" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                @if ($invoice->currency === 'INR')
                    <div>
                        <x-input-label for="edit_tax_percent" value="GST %" />
                        <x-text-input wire:model="edit_tax_percent" id="edit_tax_percent" type="number" step="0.01" class="mt-0" />
                    </div>
                @endif
                <div>
                    <x-input-label for="edit_due_date" value="Due Date" />
                    <x-text-input wire:model="edit_due_date" id="edit_due_date" type="date" class="mt-0" />
                    <x-input-error :messages="$errors->get('edit_due_date')" class="mt-1" />
                </div>
            </div>
            <p class="text-xs text-white/40">Currency ({{ $invoice->currency }}) is fixed to what the salesperson originally requested and can't be changed here.</p>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Save Changes</x-primary-button>
            </div>
        </form>
    </x-modal-glass>

    <x-modal-glass wire-model="showAdjustmentForm" :title="match($adjustment_type) { 'credit_note' => 'Issue Credit Note', 'refund' => 'Record Refund', 'written_off' => 'Write Off Invoice', default => 'Adjust Invoice' }">
        <form wire:submit="saveAdjustment" class="space-y-4">
            <div>
                <x-input-label value="Amount (INR)" for="adjustment_amount" />
                <x-text-input wire:model="adjustment_amount" id="adjustment_amount" type="number" step="0.01" class="mt-0" />
                <x-input-error :messages="$errors->get('adjustment_amount')" class="mt-1" />
            </div>
            <div>
                <x-input-label value="Reason" for="adjustment_reason" />
                <textarea wire:model="adjustment_reason" id="adjustment_reason" rows="3" class="input-glass"></textarea>
                <x-input-error :messages="$errors->get('adjustment_reason')" class="mt-1" />
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Confirm</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
