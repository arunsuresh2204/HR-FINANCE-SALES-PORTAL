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
                <thead><tr><th>Employee</th><th>Type</th><th>Dates</th><th>Days</th><th>Reason</th><th>Certificate</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse ($requests as $req)
                        <tr>
                            <td class="font-medium text-white">{{ $req->user->name }}</td>
                            <td>{{ $req->typeLabel() }}</td>
                            <td>{{ $req->start_date->format('M j') }} – {{ $req->end_date->format('M j, Y') }}</td>
                            <td>{{ $req->days }}</td>
                            <td class="max-w-xs truncate">{{ $req->reason ?: '—' }}</td>
                            <td>
                                @if ($req->hasCertificate())
                                    <a href="{{ Storage::url($req->certificate_path) }}" target="_blank" class="text-xs font-semibold text-gold-300 hover:text-gold-200">View</a>
                                @elseif ($req->requiresCertificate())
                                    <div class="flex items-center gap-2">
                                        <span class="badge-glass !border-rose-400/25 !bg-rose-400/10 !text-rose-200">Missing</span>
                                        @if ($req->status === 'pending')
                                            <button wire:click="requestCertificate({{ $req->id }})" class="text-xs font-semibold text-gold-300 hover:text-gold-200">
                                                {{ $req->certificate_requested_at ? 'Remind' : 'Request' }}
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-white/25">—</span>
                                @endif
                            </td>
                            <td><x-status-pill :status="$req->status" /></td>
                            <td class="text-right">
                                @if ($req->status === 'pending')
                                    <div class="flex justify-end gap-2">
                                        @if ($req->needsCertificate())
                                            <span class="rounded-lg bg-white/5 px-2.5 py-1 text-xs font-semibold text-white/30" title="Waiting on medical certificate">Approve</span>
                                        @else
                                            <button wire:click="approve({{ $req->id }})" class="rounded-lg bg-emerald-400/15 px-2.5 py-1 text-xs font-semibold text-emerald-300 hover:bg-emerald-400/25">Approve</button>
                                        @endif
                                        <button wire:click="reject({{ $req->id }})" class="rounded-lg bg-rose-400/15 px-2.5 py-1 text-xs font-semibold text-rose-300 hover:bg-rose-400/25">Reject</button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @if ($req->needsCertificate() && $req->certificate_requested_at)
                            <tr>
                                <td colspan="8" class="!py-2.5">
                                    <div class="rounded-lg border border-amber-400/20 bg-amber-400/[0.06] px-3 py-2 text-xs text-amber-200">
                                        Certificate requested {{ $req->certificate_requested_at->diffForHumans() }} — still waiting on {{ $req->user->name }}.
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-white/40">No requests found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $requests->links() }}</div>
    </div>
</div>
