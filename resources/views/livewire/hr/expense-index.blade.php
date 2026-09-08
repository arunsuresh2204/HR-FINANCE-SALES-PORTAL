<div>
    <x-page-header title="Expense Claims" subtitle="Submit receipts for reimbursement.">
        <x-slot:actions>
            <button wire:click="openForm" class="btn-glass-primary"><x-icon name="plus" class="h-4 w-4" /> New Claim</button>
        </x-slot:actions>
    </x-page-header>

    <div class="glass-panel relative overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead>
                    <tr><th>Date</th><th>Category</th><th>Description</th><th>Amount</th><th>Receipt</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $e)
                        <tr>
                            <td>{{ $e->expense_date->format('M j, Y') }}</td>
                            <td class="capitalize">{{ $e->category }}</td>
                            <td class="max-w-xs truncate">{{ $e->description ?: '—' }}</td>
                            <td class="font-semibold text-white">{{ \App\Support\Currency::format($e->amount, 'INR') }}</td>
                            <td>
                                @if ($e->receipt_file)
                                    <a href="{{ Storage::url($e->receipt_file) }}" target="_blank" class="text-xs font-semibold text-gold-300 hover:text-gold-200">View</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td><x-status-pill :status="$e->status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-white/40">No expense claims yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $expenses->links() }}</div>
    </div>

    <x-modal-glass wire-model="showForm" title="New Expense Claim">
        <form wire:submit="submit" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="amount" value="Amount (₹)" />
                    <x-text-input wire:model="amount" id="amount" type="number" step="0.01" class="mt-0" />
                    <x-input-error :messages="$errors->get('amount')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="category" value="Category" />
                    <select wire:model="category" id="category" class="input-glass">
                        <option value="software">Software</option>
                        <option value="travel">Travel</option>
                        <option value="office">Office</option>
                        <option value="marketing">Marketing</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div>
                <x-input-label for="expense_date" value="Expense Date" />
                <x-text-input wire:model="expense_date" id="expense_date" type="date" class="mt-0" />
                <x-input-error :messages="$errors->get('expense_date')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="description" value="Description" />
                <textarea wire:model="description" id="description" rows="2" class="input-glass"></textarea>
            </div>
            <div>
                <x-input-label for="receipt" value="Receipt (optional)" />
                <input wire:model="receipt" id="receipt" type="file" class="input-glass file:mr-3 file:rounded-lg file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-white/80" />
                <x-input-error :messages="$errors->get('receipt')" class="mt-1" />
                <div wire:loading wire:target="receipt" class="mt-1 text-xs text-white/40">Uploading...</div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Submit Claim</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
