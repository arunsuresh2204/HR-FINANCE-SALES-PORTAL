<div>
    <x-page-header title="GST Filing" subtitle="Select a month's invoices and send them as a ZIP to your accounting firm." />

    <div class="glass-card">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <select wire:model.live="month" class="input-glass w-36">
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}</option>
                    @endforeach
                </select>
                <select wire:model.live="year" class="input-glass w-28">
                    @foreach (array_reverse(range(now()->year - 10, now()->year)) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <p class="text-xs text-white/45">{{ $invoices->count() }} invoice{{ $invoices->count() === 1 ? '' : 's' }} this period</p>
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th class="!w-10"><input type="checkbox" wire:model.live="selectAll" class="rounded border-white/20 bg-white/5 text-gold-400 focus:ring-gold-400/40"></th>
                        <th>Invoice #</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Currency</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr wire:key="inv-{{ $invoice->id }}">
                            <td><input type="checkbox" wire:model.live="selectedIds" value="{{ $invoice->id }}" class="rounded border-white/20 bg-white/5 text-gold-400 focus:ring-gold-400/40"></td>
                            <td class="font-medium text-white">{{ $invoice->invoice_number }}</td>
                            <td class="text-white/60">{{ $invoice->client->business_name }}</td>
                            <td class="text-white/60">{{ $invoice->created_at->format('M j, Y') }}</td>
                            <td class="text-white/60">{{ $invoice->currency }}</td>
                            <td class="text-white/60">{{ \App\Support\Currency::format($invoice->total_amount, $invoice->currency) }}</td>
                            <td><x-status-pill :status="$invoice->status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-white/40">No invoices for this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="glass-card mt-6">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-white/40">Send Selected Invoices</p>
        <div class="flex flex-wrap items-end gap-3">
            <div class="min-w-[16rem] flex-1">
                <x-input-label for="gst_email" value="Accounting Firm Email" />
                <x-text-input wire:model="email" id="gst_email" type="email" class="mt-0" placeholder="accounts@firm.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-1" />
            </div>
            <button wire:click="send" wire:loading.attr="disabled" wire:target="send" @disabled(empty($selectedIds)) class="btn-glass-primary disabled:cursor-not-allowed disabled:opacity-40">
                <span wire:loading.remove wire:target="send" class="inline-flex items-center gap-2"><x-icon name="document" class="h-4 w-4" /> Send {{ count($selectedIds) }} as ZIP</span>
                <span wire:loading wire:target="send">Sending&hellip;</span>
            </button>
        </div>
        @if (count($selectedIds) > 0)
            <p class="mt-2 text-xs text-white/40">
                {{ count($selectedIds) }} selected &middot;
                @foreach ($selectedTotalsByCurrency as $currencyCode => $sum)
                    {{ !$loop->first ? ' + ' : '' }}{{ \App\Support\Currency::format($sum, $currencyCode) }}
                @endforeach
            </p>
        @endif
    </div>
</div>
