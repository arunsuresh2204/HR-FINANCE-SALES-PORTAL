<div>
    <x-page-header title="HR Reports" subtitle="Headcount, leave trends and attendance summary." />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
        <x-stat-card label="Total Headcount" :value="$headcount" icon="users" accent="sky" />
        <x-stat-card label="Active" :value="$activeCount" icon="check" accent="emerald" />
        <x-stat-card label="On Notice" :value="$onNoticeCount" icon="exit" accent="gold" />
        <x-stat-card label="Pending Leave Requests" :value="$pendingLeave" icon="calendar" accent="violet" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="glass-card">
            <h2 class="mb-4 text-sm font-bold uppercase tracking-wide text-white/50">Headcount by Department</h2>
            <div class="space-y-3">
                @forelse ($byDepartment as $dept => $count)
                    <div>
                        <div class="mb-1 flex justify-between text-sm"><span class="text-white/70">{{ $dept }}</span><span class="font-semibold text-white">{{ $count }}</span></div>
                        <div class="h-2 rounded-full bg-white/5"><div class="h-2 rounded-full bg-gold-400" style="width: {{ $headcount ? min(100, $count / $headcount * 100) : 0 }}%"></div></div>
                    </div>
                @empty
                    <p class="text-sm text-white/40">No data.</p>
                @endforelse
            </div>
        </div>

        <div class="glass-card">
            <h2 class="mb-4 text-sm font-bold uppercase tracking-wide text-white/50">Leave Days by Type (This Year)</h2>
            <div class="space-y-3">
                @forelse ($leaveByType as $type => $days)
                    <div class="flex items-center justify-between text-sm">
                        <span class="capitalize text-white/70">{{ $type }}</span>
                        <span class="font-semibold text-white">{{ $days }} days</span>
                    </div>
                @empty
                    <p class="text-sm text-white/40">No approved leave yet this year.</p>
                @endforelse
            </div>
        </div>

        <div class="glass-card">
            <h2 class="mb-4 text-sm font-bold uppercase tracking-wide text-white/50">Attendance This Month</h2>
            <div class="space-y-3">
                @forelse ($attendanceThisMonth as $status => $count)
                    <div class="flex items-center justify-between text-sm">
                        <x-status-pill :status="$status" />
                        <span class="font-semibold text-white">{{ $count }}</span>
                    </div>
                @empty
                    <p class="text-sm text-white/40">No attendance recorded this month.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
