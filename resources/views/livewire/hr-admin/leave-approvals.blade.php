<div>
    <x-page-header title="Leave Approvals" subtitle="Review and act on employee time-off requests.">
        <x-slot:actions>
            <select wire:model.live="filter" class="input-glass w-40">
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="all">All</option>
            </select>
        </x-slot:actions>
    </x-page-header>

    <div class="glass-panel relative overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead><tr><th>Employee</th><th>Type</th><th>Dates</th><th>Days</th><th>Reason</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($requests as $req)
                        <tr>
                            <td class="font-medium text-white">{{ $req->user->name }}</td>
                            <td class="capitalize">{{ $req->type }}</td>
                            <td>{{ $req->start_date->format('M j') }} – {{ $req->end_date->format('M j, Y') }}</td>
                            <td>{{ $req->days }}</td>
                            <td class="max-w-xs truncate">{{ $req->reason ?: '—' }}</td>
                            <td><x-status-pill :status="$req->status" /></td>
                            <td class="text-right">
                                @if ($req->status === 'pending')
                                    <div class="flex justify-end gap-2">
                                        <button wire:click="approve({{ $req->id }})" class="rounded-lg bg-emerald-400/15 px-2.5 py-1 text-xs font-semibold text-emerald-300 hover:bg-emerald-400/25">Approve</button>
                                        <button wire:click="reject({{ $req->id }})" class="rounded-lg bg-rose-400/15 px-2.5 py-1 text-xs font-semibold text-rose-300 hover:bg-rose-400/25">Reject</button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-white/40">No requests found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $requests->links() }}</div>
    </div>
</div>
