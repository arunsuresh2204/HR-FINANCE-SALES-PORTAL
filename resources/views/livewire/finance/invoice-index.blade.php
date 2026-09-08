<div>
    <x-page-header title="Invoices" subtitle="Track invoice status and payments.">
        <x-slot:actions>
            <select wire:model.live="filter" class="input-glass w-40">
                <option value="all">All</option>
                <option value="draft">Draft</option>
                <option value="sent">Sent</option>
                <option value="partially_paid">Partially Paid</option>
                <option value="paid">Paid</option>
                <option value="overdue">Overdue</option>
            </select>
            <button wire:click="openCreateForm" class="btn-glass-primary"><x-icon name="plus" class="h-4 w-4" /> New Invoice</button>
        </x-slot:actions>
    </x-page-header>

    <div class="glass-panel relative overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead><tr><th>Invoice #</th><th>Client</th><th>Currency</th><th>Amount</th><th>Paid</th><th>Due Date</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td class="font-medium text-white">{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->client->business_name }}</td>
                            <td><span class="badge-glass">{{ $invoice->currency }}</span></td>
                            <td class="font-semibold text-white">{{ $invoice->money($invoice->total_amount) }}</td>
                            <td>{{ $invoice->money($invoice->amount_paid) }}</td>
                            <td>{{ $invoice->due_date?->format('M j, Y') ?? '—' }}</td>
                            <td><x-status-pill :status="$invoice->isOverdue() ? 'overdue' : $invoice->status" /></td>
                            <td class="text-right"><a href="{{ route('finance.invoices.show', $invoice) }}" wire:navigate class="text-xs font-semibold text-gold-300 hover:text-gold-200">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-white/40">No invoices found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $invoices->links() }}</div>
    </div>

    <x-modal-glass wire-model="showCreateForm" title="New Invoice" max-width="2xl">
        <form wire:submit="createInvoice" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="salesperson_id" value="Salesperson (optional, to filter clients)" />
                    <select wire:model.live="salesperson_id" id="salesperson_id" class="input-glass">
                        <option value="">— All salespeople —</option>
                        @foreach ($salespeople as $sp)
                            <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="client_id" value="Client" />
                    <select wire:model.live="client_id" id="client_id" class="input-glass">
                        <option value="">— Select client —</option>
                        @foreach ($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->business_name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('client_id')" class="mt-1" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="currency" value="Currency" />
                    <select wire:model.live="currency" id="currency" class="input-glass">
                        <option value="INR">INR (₹)</option>
                        <option value="USD">USD ($)</option>
                        <option value="EUR">EUR (€)</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="due_date" value="Due Date" />
                    <x-text-input wire:model="due_date" id="due_date" type="date" class="mt-0" />
                    <x-input-error :messages="$errors->get('due_date')" class="mt-1" />
                </div>
            </div>

            @if ($currency === 'INR')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="tax_percent" value="GST %" />
                        <x-text-input wire:model="tax_percent" id="tax_percent" type="number" step="0.01" class="mt-0" />
                        <p class="mt-1 text-xs text-white/40">Set 0% if this client is GST-exempt.</p>
                    </div>
                    <div>
                        <x-input-label for="client_tax_id" value="Client GSTIN (optional)" />
                        <x-text-input wire:model="client_tax_id" id="client_tax_id" type="text" class="mt-0" placeholder="e.g. 32ABBCS6427Q1ZY" />
                    </div>
                </div>
            @else
                <div class="grid grid-cols-2 gap-4">
                    <div class="glass-inset p-3 text-xs text-white/50">
                        <p class="font-semibold text-white/70">GST: 0%</p>
                        <p class="mt-1">Export of IT services is treated as zero-rated supply (Section 16, IGST Act).</p>
                    </div>
                    <div>
                        <x-input-label for="client_tax_id" value="Client VAT / Tax ID (optional)" />
                        <x-text-input wire:model="client_tax_id" id="client_tax_id" type="text" class="mt-0" placeholder="e.g. DE337512877" />
                    </div>
                </div>
            @endif

            @if ($client_id)
                <div>
                    <x-input-label value="Pending Billing Requests ({{ $currency }})" />
                    <div class="mt-1 max-h-40 space-y-1.5 overflow-y-auto">
                        @forelse ($pendingBillingRequests as $br)
                            <label class="glass-inset flex cursor-pointer items-center justify-between gap-3 p-2.5 text-sm">
                                <span class="flex items-center gap-2">
                                    <input type="checkbox" wire:model="selectedBillingRequestIds" value="{{ $br->id }}" class="rounded border-white/20 bg-white/5 text-gold-400 focus:ring-gold-400/40">
                                    <span>
                                        {{ $br->summary() }}
                                        @if ($br->project)
                                            <span class="text-white/30">&middot; {{ $br->project->name }}</span>
                                        @endif
                                    </span>
                                </span>
                                <span class="shrink-0 font-semibold text-white/70">{{ \App\Support\Currency::format($br->amount, $br->currency) }}</span>
                            </label>
                        @empty
                            <p class="text-xs text-white/40">No pending {{ $currency }} billing requests for this client. You can still add manual line items below.</p>
                        @endforelse
                    </div>
                </div>
            @endif

            <div>
                <div class="mb-2 flex items-center justify-between">
                    <x-input-label value="Manual Line Items (optional)" class="mb-0" />
                    <button type="button" wire:click="addLineItem" class="text-xs font-semibold text-gold-300 hover:text-gold-200">+ Add Line Item</button>
                </div>
                <div class="space-y-2">
                    @foreach ($lineItems as $index => $item)
                        <div class="flex items-start gap-2" wire:key="line-item-{{ $index }}">
                            <div class="flex-1">
                                <x-text-input wire:model="lineItems.{{ $index }}.description" type="text" class="mt-0" placeholder="Description" />
                            </div>
                            <div class="w-32">
                                <x-text-input wire:model="lineItems.{{ $index }}.amount" type="number" step="0.01" class="mt-0" placeholder="Amount" />
                            </div>
                            @if (count($lineItems) > 1)
                                <button type="button" wire:click="removeLineItem({{ $index }})" class="mt-2 text-white/30 hover:text-rose-300"><x-icon name="trash" class="h-4 w-4" /></button>
                            @endif
                        </div>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('lineItems')" class="mt-1" />
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Create Invoice</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
