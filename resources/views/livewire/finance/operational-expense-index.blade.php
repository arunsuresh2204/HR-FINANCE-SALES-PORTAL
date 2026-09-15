<div>
    <x-page-header title="Operational Expenses" subtitle="Track recurring and one-off business costs — rent, electricity, software licenses and more.">
        <x-slot:actions>
            <div class="flex flex-wrap items-center gap-2">
                <select wire:model.live="month" class="input-glass w-36">
                    @foreach (range(1, 12) as $m)
                        <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}</option>
                    @endforeach
                </select>
                <select wire:model.live="year" class="input-glass w-28">
                    @foreach (array_reverse(range(now()->year - 10, now()->year + 1)) as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
                <button wire:click="openCategoryManager" class="btn-glass-secondary"><x-icon name="list" class="h-4 w-4" /> Categories</button>
                <button wire:click="openAddForm" class="btn-glass-primary"><x-icon name="plus" class="h-4 w-4" /> Add Expense</button>
            </div>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-stat-card label="Total This Period" :value="\App\Support\Currency::format($totalThisMonth, 'INR')" icon="building" accent="rose" />
        <x-stat-card label="Recurring" :value="\App\Support\Currency::format($recurringThisMonth, 'INR')" icon="clock" accent="violet" />
        <x-stat-card label="Entries" :value="$expenses->total()" icon="receipt" accent="gold" />
    </div>

    @if ($byCategory->isNotEmpty())
        <div class="glass-card mt-6">
            <h2 class="mb-4 text-base font-bold text-white">By Category</h2>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($byCategory as $catName => $total)
                    <div class="glass-inset p-3">
                        <p class="truncate text-xs font-semibold text-white/50">{{ $catName }}</p>
                        <p class="mt-1 text-sm font-bold text-white">{{ \App\Support\Currency::format($total, 'INR') }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="glass-panel relative mt-6 overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Vendor</th>
                        <th>Amount</th>
                        <th>Recurring</th>
                        <th>Notes</th>
                        <th>Recorded By</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr wire:key="opex-{{ $expense->id }}">
                            <td class="whitespace-nowrap text-white/60">{{ $expense->expense_date->format('d M Y') }}</td>
                            <td class="font-medium text-white">{{ $expense->category->name }}</td>
                            <td class="text-white/60">{{ $expense->vendor ?: '—' }}</td>
                            <td class="whitespace-nowrap font-semibold text-white">{{ \App\Support\Currency::format($expense->amount, 'INR') }}</td>
                            <td>
                                @if ($expense->is_recurring)
                                    <span class="badge-glass !border-violet-400/25 !bg-violet-400/10 !text-violet-200">Recurring</span>
                                @else
                                    <span class="text-white/25">—</span>
                                @endif
                            </td>
                            <td class="max-w-xs truncate text-white/50">{{ $expense->notes ?: '—' }}</td>
                            <td class="whitespace-nowrap text-white/50">{{ $expense->creator?->name ?? '—' }}</td>
                            <td class="text-right">
                                <div class="flex justify-end gap-3">
                                    <button wire:click="openEditForm({{ $expense->id }})" class="text-xs font-semibold text-gold-300 hover:text-gold-200">Edit</button>
                                    <button wire:click="deleteExpense({{ $expense->id }})" wire:confirm="Delete this expense entry? This can't be undone." class="text-xs font-semibold text-white/30 hover:text-rose-300">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-white/40">No operational expenses recorded for this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $expenses->links() }}</div>
    </div>

    <x-modal-glass wire-model="showForm" :title="$editingId ? 'Edit Expense' : 'Add Operational Expense'">
        <form wire:submit="saveExpense" class="space-y-4">
            <div>
                <x-input-label for="category_id" value="Category" />
                <select wire:model="category_id" id="category_id" class="input-glass mt-0 w-full">
                    <option value="">Select a category…</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @disabled(! $category->is_active && $category_id !== $category->id)>
                            {{ $category->name }}{{ ! $category->is_active ? ' (inactive)' : '' }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('category_id')" class="mt-1" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="amount" value="Amount (INR)" />
                    <x-text-input wire:model="amount" id="amount" type="number" step="0.01" min="0" class="mt-0" placeholder="0.00" />
                    <x-input-error :messages="$errors->get('amount')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="expense_date" value="Date" />
                    <x-text-input wire:model="expense_date" id="expense_date" type="date" class="mt-0" />
                    <x-input-error :messages="$errors->get('expense_date')" class="mt-1" />
                </div>
            </div>
            <div>
                <x-input-label for="vendor" value="Vendor / Payee (optional)" />
                <x-text-input wire:model="vendor" id="vendor" type="text" class="mt-0" placeholder="e.g. BESCOM, WeWork, Adobe" />
                <x-input-error :messages="$errors->get('vendor')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="notes" value="Notes (optional)" />
                <textarea wire:model="notes" id="notes" rows="2" class="input-glass mt-0 w-full"></textarea>
                <x-input-error :messages="$errors->get('notes')" class="mt-1" />
            </div>
            <label class="flex items-center gap-2 text-sm text-white/70">
                <input type="checkbox" wire:model="is_recurring" class="rounded border-white/20 bg-white/5 text-gold-400 focus:ring-gold-400/50" />
                This is a recurring monthly cost
            </label>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>{{ $editingId ? 'Save Changes' : 'Add Expense' }}</x-primary-button>
            </div>
        </form>
    </x-modal-glass>

    <x-modal-glass wire-model="showCategoryManager" title="Operational Expense Categories" max-width="xl">
        <div class="mb-4 flex gap-2">
            <x-text-input wire:model="newCategoryName" type="text" class="mt-0 flex-1" placeholder="e.g. Cloud Hosting" />
            <button wire:click="addCategory" class="btn-glass-primary shrink-0"><x-icon name="plus" class="h-4 w-4" /> Add</button>
        </div>
        <x-input-error :messages="$errors->get('newCategoryName')" class="mb-3" />

        <div class="max-h-96 overflow-y-auto rounded-xl border border-white/10">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Entries</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $category)
                        <tr wire:key="cat-{{ $category->id }}">
                            <td>
                                @if ($editingCategoryId === $category->id)
                                    <x-text-input wire:model="editingCategoryName" type="text" class="mt-0 !py-1.5 text-sm" />
                                @else
                                    <span class="font-medium text-white">{{ $category->name }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($category->is_active)
                                    <span class="badge-glass !border-emerald-400/25 !bg-emerald-400/10 !text-emerald-200">Active</span>
                                @else
                                    <span class="badge-glass !border-white/15 !bg-white/5 !text-white/40">Inactive</span>
                                @endif
                            </td>
                            <td class="text-white/60">{{ $category->expenseCount() }}</td>
                            <td class="text-right">
                                @if ($editingCategoryId === $category->id)
                                    <div class="flex justify-end gap-3">
                                        <button wire:click="saveRenameCategory" class="text-xs font-semibold text-gold-300 hover:text-gold-200">Save</button>
                                        <button wire:click="cancelRenameCategory" class="text-xs font-semibold text-white/40 hover:text-white">Cancel</button>
                                    </div>
                                @else
                                    <div class="flex justify-end gap-3">
                                        <button wire:click="startRenameCategory({{ $category->id }})" class="text-xs font-semibold text-gold-300 hover:text-gold-200">Rename</button>
                                        <button wire:click="toggleCategoryActive({{ $category->id }})" class="text-xs font-semibold text-white/50 hover:text-white">{{ $category->is_active ? 'Deactivate' : 'Activate' }}</button>
                                        <button wire:click="deleteCategory({{ $category->id }})" wire:confirm="Delete this category? This can't be undone." class="text-xs font-semibold text-white/30 hover:text-rose-300">Delete</button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-modal-glass>
</div>
