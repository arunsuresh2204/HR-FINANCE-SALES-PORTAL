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
        </x-slot:actions>
    </x-page-header>

    <div class="glass-panel relative overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead><tr><th>Invoice #</th><th>Client</th><th>Amount</th><th>Paid</th><th>Due Date</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td class="font-medium text-white">{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->client->business_name }}</td>
                            <td class="font-semibold text-white">${{ number_format($invoice->total_amount, 2) }}</td>
                            <td>${{ number_format($invoice->amount_paid, 2) }}</td>
                            <td>{{ $invoice->due_date?->format('M j, Y') ?? '—' }}</td>
                            <td><x-status-pill :status="$invoice->isOverdue() ? 'overdue' : $invoice->status" /></td>
                            <td class="text-right"><a href="{{ route('finance.invoices.show', $invoice) }}" wire:navigate class="text-xs font-semibold text-gold-300 hover:text-gold-200">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-white/40">No invoices found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $invoices->links() }}</div>
    </div>
</div>
