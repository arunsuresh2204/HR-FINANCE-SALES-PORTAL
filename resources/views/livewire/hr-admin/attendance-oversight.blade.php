<div>
    <x-page-header title="Attendance Oversight" subtitle="Track late logins across the team and review employee status-change requests." />

    <div class="mb-6 inline-flex rounded-xl border border-white/10 bg-white/5 p-1">
        <button type="button" wire:click="setTab('today')" class="rounded-lg px-4 py-1.5 text-xs font-semibold transition {{ $tab === 'today' ? 'bg-gold-400 text-ink-950' : 'text-white/50 hover:text-white' }}">Daily Status</button>
        <button type="button" wire:click="setTab('requests')" class="rounded-lg px-4 py-1.5 text-xs font-semibold transition {{ $tab === 'requests' ? 'bg-gold-400 text-ink-950' : 'text-white/50 hover:text-white' }}">
            Requests
            @if ($pendingCount > 0)
                <span class="ml-1 rounded-full bg-rose-400/80 px-1.5 py-0.5 text-[10px] text-white">{{ $pendingCount }}</span>
            @endif
        </button>
    </div>

    @if ($tab === 'today')
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="inline-flex items-center gap-2">
                <button type="button" wire:click="prevDay" class="btn-glass-secondary !px-2.5"><x-icon name="arrow-right" class="h-4 w-4 rotate-180" /></button>
                <span class="min-w-[10rem] text-center text-sm font-semibold text-white">{{ \Carbon\Carbon::parse($date)->format('l, M j, Y') }}</span>
                <button type="button" wire:click="nextDay" class="btn-glass-secondary !px-2.5"><x-icon name="arrow-right" class="h-4 w-4" /></button>
                <button type="button" wire:click="goToday" class="text-xs font-semibold text-gold-300 hover:text-gold-200">Today</button>
            </div>
            <input wire:model.live.debounce.400ms="search" type="text" class="input-glass max-w-xs" placeholder="Search employees...">
        </div>

        <div class="mb-4 flex flex-wrap gap-4 text-xs text-white/50">
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span> On Time</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-orange-400"></span> Grace Period (&le;30 min)</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-rose-400"></span> Late &mdash; Warning</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-rose-600"></span> Absent</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-violet-400"></span> On Leave</span>
        </div>

        <div class="glass-panel relative overflow-hidden">
            <div class="glass-sheen"></div>
            <div class="overflow-x-auto">
                <table class="table-glass">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Scheduled Login</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr wire:key="row-{{ $row['user']->id }}">
                                <td>
                                    <p class="font-medium text-white">{{ $row['user']->name }}</p>
                                    <p class="text-xs text-white/40">{{ $row['user']->employee_code }}</p>
                                </td>
                                <td class="text-white/60">{{ $row['user']->scheduled_login_time ? \Carbon\Carbon::parse($row['user']->scheduled_login_time)->format('g:i A') : '—' }}</td>
                                <td class="text-white/60">{{ $row['attendance']?->clock_in?->format('g:i A') ?? '—' }}</td>
                                <td class="text-white/60">{{ $row['attendance']?->clock_out?->format('g:i A') ?? '—' }}</td>
                                <td><x-attendance-status-pill :info="$row['status']" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-white/40">No employees found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $rows->links() }}</div>
        </div>
    @else
        <div class="mb-4">
            <select wire:model.live="requestFilter" class="input-glass max-w-xs">
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="all">All</option>
            </select>
        </div>

        <div class="glass-panel relative overflow-hidden">
            <div class="glass-sheen"></div>
            <div class="overflow-x-auto">
                <table class="table-glass">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Requested Status</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $req)
                            <tr wire:key="req-{{ $req->id }}">
                                <td class="font-medium text-white">{{ $req->user->name }}</td>
                                <td class="text-white/60">{{ $req->attendance->work_date->format('M j, Y') }}</td>
                                <td><x-status-pill :status="$req->requested_status" /></td>
                                <td class="max-w-xs text-white/60">{{ $req->reason }}</td>
                                <td><x-status-pill :status="$req->status" /></td>
                                <td class="text-right">
                                    @if ($req->status === 'pending')
                                        <div class="flex justify-end gap-3">
                                            <button wire:click="approveRequest({{ $req->id }})" class="text-xs font-semibold text-emerald-300 hover:text-emerald-200">Approve</button>
                                            <button wire:click="rejectRequest({{ $req->id }})" wire:confirm="Reject this request?" class="text-xs font-semibold text-white/40 hover:text-rose-300">Reject</button>
                                        </div>
                                    @else
                                        <span class="text-xs text-white/30">{{ $req->reviewed_at?->format('M j, g:i A') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-8 text-center text-white/40">No requests found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $requests->links() }}</div>
        </div>
    @endif
</div>
