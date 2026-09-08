<div>
    <x-page-header :title="$client->business_name" :subtitle="$client->business_type ?? 'Client'">
        <x-slot:actions>
            <a href="{{ route('sales.clients') }}" wire:navigate class="btn-glass-secondary"><x-icon name="arrow-right" class="h-4 w-4 rotate-180" /> Back</a>
            <button wire:click="openBillingForm" class="btn-glass-primary"><x-icon name="cash" class="h-4 w-4" /> New Billing Request</button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="glass-card">
                <h2 class="mb-4 text-base font-bold text-white">Business Details</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-white/40">Business Address</dt><dd class="mt-0.5 text-white">{{ $client->business_address ?? '—' }}</dd></div>
                    <div><dt class="text-white/40">Owner / Decision Maker</dt><dd class="mt-0.5 text-white">{{ $client->owner_name ?? '—' }} {{ $client->owner_designation ? '('.$client->owner_designation.')' : '' }}</dd></div>
                    <div><dt class="text-white/40">Contact</dt><dd class="mt-0.5 text-white">{{ $client->owner_contact ?? '—' }}</dd></div>
                    <div><dt class="text-white/40">GSTIN / VAT / Tax ID</dt><dd class="mt-0.5 text-white">{{ $client->tax_id ?? '—' }}</dd></div>
                    <div><dt class="text-white/40">Sales Person</dt><dd class="mt-0.5 text-white">{{ $client->salesPerson->name }}</dd></div>
                    <div><dt class="text-white/40">Hours Logged</dt><dd class="mt-0.5 text-white">{{ number_format($totalHours, 1) }}h</dd></div>
                </dl>
            </div>

            <div class="glass-card">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-base font-bold text-white">Agreement</h2>
                    <button wire:click="$set('showAgreementForm', true)" class="text-xs font-semibold text-gold-300 hover:text-gold-200">Edit</button>
                </div>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-white/40">Effective Date</dt><dd class="mt-0.5 text-white">{{ $client->agreement_effective_date?->format('M j, Y') ?? '—' }}</dd></div>
                    <div><dt class="text-white/40">Signed File</dt><dd class="mt-0.5">
                        @if ($client->agreement_file)
                            <a href="{{ Storage::url($client->agreement_file) }}" target="_blank" class="text-gold-300 hover:text-gold-200">View Agreement</a>
                        @else
                            <span class="text-white/40">Not uploaded</span>
                        @endif
                    </dd></div>
                </dl>
                @if ($client->agreement_scope_summary)
                    <p class="mt-3 text-sm text-white/60">{{ $client->agreement_scope_summary }}</p>
                @endif

                @if ($showAgreementForm)
                    <form wire:submit="saveAgreement" class="mt-4 space-y-4 border-t border-white/10 pt-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="agreement_effective_date" value="Effective Date" />
                                <x-text-input wire:model="agreement_effective_date" id="agreement_effective_date" type="date" class="mt-0" />
                            </div>
                            <div>
                                <x-input-label for="agreement_file" value="Signed Agreement File" />
                                <input wire:model="agreement_file" id="agreement_file" type="file" class="input-glass file:mr-3 file:rounded-lg file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-white/80">
                            </div>
                        </div>
                        <div>
                            <x-input-label for="agreement_scope_summary" value="Scope Summary" />
                            <textarea wire:model="agreement_scope_summary" id="agreement_scope_summary" rows="2" class="input-glass"></textarea>
                        </div>
                        <div class="flex justify-end gap-3">
                            <x-secondary-button type="button" wire:click="$set('showAgreementForm', false)">Cancel</x-secondary-button>
                            <x-primary-button>Save</x-primary-button>
                        </div>
                    </form>
                @endif
            </div>

            <div class="glass-card">
                <h2 class="mb-4 text-base font-bold text-white">Invoices</h2>
                <div class="space-y-2">
                    @forelse ($invoices as $invoice)
                        <div class="glass-inset flex items-center justify-between p-3">
                            <div>
                                <p class="text-sm font-medium text-white">{{ $invoice->invoice_number }}</p>
                                <p class="text-xs text-white/40">{{ $invoice->money($invoice->total_amount) }} &middot; Due {{ $invoice->due_date?->format('M j, Y') ?? '—' }}</p>
                            </div>
                            <x-status-pill :status="$invoice->status" />
                        </div>
                    @empty
                        <p class="text-sm text-white/40">No invoices yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="glass-card">
                <h2 class="mb-4 text-sm font-bold uppercase tracking-wide text-white/50">Billing Requests</h2>
                <div class="space-y-3">
                    @forelse ($billingRequests as $br)
                        <div class="glass-inset p-3">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-white">{{ \App\Support\Currency::format($br->amount, 'INR') }}</p>
                                <x-status-pill :status="$br->status" />
                            </div>
                            <p class="mt-1 text-xs text-white/45">{{ $br->milestone_description }}</p>
                            <p class="mt-1 text-[11px] text-white/30">{{ $br->created_at->format('M j, Y') }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-white/40">No billing requests yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <x-modal-glass wire-model="showBillingForm" title="New Billing Request">
        <form wire:submit="submitBillingRequest" class="space-y-4">
            <div>
                <x-input-label for="amount" value="Amount ($)" />
                <x-text-input wire:model="amount" id="amount" type="number" step="0.01" class="mt-0" />
                <x-input-error :messages="$errors->get('amount')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="milestone_description" value="Milestone Description" />
                <x-text-input wire:model="milestone_description" id="milestone_description" type="text" class="mt-0" placeholder="e.g. 50% advance" />
                <x-input-error :messages="$errors->get('milestone_description')" class="mt-1" />
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Send to Finance</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
