<div>
    <x-page-header title="Expense Approvals" subtitle="Review and categorize employee expense claims.">
        <x-slot:actions>
            <select wire:model.live="filter" class="input-glass w-40">
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="all">All</option>
            </select>
        </x-slot:actions>
    </x-page-header>

    @if ($byCategory->isNotEmpty())
        <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-5">
            @foreach ($byCategory as $cat => $total)
                <x-stat-card :label="ucfirst($cat)" value="{{ \App\Support\Currency::format($total, 'INR') }}" icon="receipt" accent="violet" />
            @endforeach
        </div>
    @endif

    <div class="glass-panel relative overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead><tr><th>Employee</th><th>Category</th><th>Description</th><th>Amount</th><th>Receipt</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($expenses as $e)
                        <tr>
                            <td class="font-medium text-white">{{ $e->user->name }}</td>
                            <td class="capitalize">{{ $e->category }}</td>
                            <td class="max-w-xs truncate">{{ $e->description ?: '—' }}</td>
                            <td class="font-semibold text-white">{{ \App\Support\Currency::format($e->amount, 'INR') }}</td>
                            <td>
                                @if ($e->receipt_file)
                                    <a href="{{ Storage::url($e->receipt_file) }}" target="_blank" class="text-xs font-semibold text-gold-300 hover:text-gold-200">View</a>
                                @else — @endif
                            </td>
                            <td><x-status-pill :status="$e->status" /></td>
                            <td class="text-right">
                                @if ($e->status === 'pending')
                                    <div class="flex justify-end gap-2">
                                        <button wire:click="approve({{ $e->id }})" class="rounded-lg bg-emerald-400/15 px-2.5 py-1 text-xs font-semibold text-emerald-300 hover:bg-emerald-400/25">Approve</button>
                                        <button wire:click="reject({{ $e->id }})" class="rounded-lg bg-rose-400/15 px-2.5 py-1 text-xs font-semibold text-rose-300 hover:bg-rose-400/25">Reject</button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-white/40">No expense claims found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $expenses->links() }}</div>
    </div>
</div>
