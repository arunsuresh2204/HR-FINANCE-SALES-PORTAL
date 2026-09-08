<div>
    <x-page-header title="Billing Requests" subtitle="Convert sales billing requests into formal invoices.">
        <x-slot:actions>
            <select wire:model.live="filter" class="input-glass w-40">
                <option value="pending">Pending</option>
                <option value="invoiced">Invoiced</option>
                <option value="rejected">Rejected</option>
                <option value="all">All</option>
            </select>
        </x-slot:actions>
    </x-page-header>

    <div class="glass-panel relative overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead><tr><th>Client</th><th>Milestone</th><th>Amount</th><th>Requested By</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($requests as $req)
                        <tr>
                            <td class="font-medium text-white">{{ $req->client->business_name }}</td>
                            <td>{{ $req->milestone_description }}</td>
                            <td class="font-semibold text-white">{{ \App\Support\Currency::format($req->amount, 'INR') }}</td>
                            <td>{{ $req->creator->name }}</td>
                            <td><x-status-pill :status="$req->status" /></td>
                            <td class="text-right">
                                @if ($req->status === 'pending')
                                    <div class="flex justify-end gap-2">
                                        <button wire:click="openConvert({{ $req->id }})" class="rounded-lg bg-emerald-400/15 px-2.5 py-1 text-xs font-semibold text-emerald-300 hover:bg-emerald-400/25">Create Invoice</button>
                                        <button wire:click="reject({{ $req->id }})" class="rounded-lg bg-rose-400/15 px-2.5 py-1 text-xs font-semibold text-rose-300 hover:bg-rose-400/25">Reject</button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-white/40">No billing requests found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-modal-glass wire-model="showConvertForm" title="Create Invoice">
        @if ($converting)
            <form wire:submit="createInvoice" class="space-y-4">
                <div class="glass-inset p-3 text-sm">
                    <p class="text-white/70">{{ $converting->client->business_name }} &middot; {{ $converting->milestone_description }}</p>
                    <p class="mt-1 text-lg font-bold text-white">{{ \App\Support\Currency::format($converting->amount, $currency) }}</p>
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
                            <x-input-error :messages="$errors->get('tax_percent')" class="mt-1" />
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

                <div class="flex justify-end gap-3 pt-2">
                    <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                    <x-primary-button>Create Invoice</x-primary-button>
                </div>
            </form>
        @endif
    </x-modal-glass>
</div>
