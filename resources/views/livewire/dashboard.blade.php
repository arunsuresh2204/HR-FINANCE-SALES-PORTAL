<div>
    <x-page-header title="Welcome back, {{ Str::of(auth()->user()->name)->before(' ') }}" subtitle="Here's what's happening across your workspace today." />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Today's Attendance" :value="$todayAttendance ? 'Clocked In' : 'Not Clocked In'" icon="clock" :accent="$todayAttendance ? 'emerald' : 'rose'" />
        <x-stat-card label="Leave Days Used (Year)" :value="$approvedLeaveDaysThisYear" icon="calendar" accent="sky" />
        <x-stat-card label="Pending Leave Requests" :value="$pendingLeave" icon="calendar" accent="gold" />
        <x-stat-card label="Pending Expenses" :value="$pendingExpenses" icon="receipt" accent="violet" />
    </div>

    @if (auth()->user()->isProgrammer() || auth()->user()->isMarketer() || auth()->user()->isSalesExec())
        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @if (auth()->user()->isProgrammer())
                <x-stat-card label="Hours Logged Today" :value="number_format($todayHours ?? 0, 1)" icon="code" accent="sky" hint="{{ number_format($weekHours ?? 0, 1) }}h this week" />
            @endif
            @if (auth()->user()->isMarketer())
                <x-stat-card label="Marketing Hours Today" :value="number_format($todayMarketingHours ?? 0, 1)" icon="megaphone" accent="violet" />
            @endif
            @if (auth()->user()->isSalesExec())
                <x-stat-card label="Open Leads" :value="$myLeadsOpen" icon="target" accent="sky" />
                <x-stat-card label="Won This Month" :value="$myLeadsWonThisMonth" icon="briefcase" accent="emerald" />
                @if ($salesTarget)
                    <x-stat-card label="Target Progress" value="${{ number_format($salesAchieved) }} / ${{ number_format($salesTarget->target_amount) }}" icon="chart" accent="gold" />
                @endif
            @endif
        </div>
    @endif

    @if (auth()->user()->isHrAdmin() || auth()->user()->isFinanceAdmin() || auth()->user()->isSuperAdmin())
        <h2 class="mb-3 mt-8 text-xs font-bold uppercase tracking-widest text-white/40">Admin Overview</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @if (auth()->user()->isHrAdmin())
                <x-stat-card label="Active Headcount" :value="$headcount" icon="users" accent="emerald" />
                <x-stat-card label="Pending Leave Approvals" :value="$pendingLeaveApprovals" icon="calendar" accent="gold" />
                <x-stat-card label="Pending Resignations" :value="$pendingResignations" icon="exit" accent="rose" />
            @endif
            @if (auth()->user()->isFinanceAdmin())
                <x-stat-card label="Pending Billing Requests" :value="$pendingBillingRequests" icon="inbox" accent="sky" />
                <x-stat-card label="Outstanding Invoices" value="${{ number_format($outstandingInvoices, 2) }}" icon="cash" accent="rose" />
                <x-stat-card label="Pending Expense Approvals" :value="$pendingExpenseApprovals" icon="receipt" accent="violet" />
                <x-stat-card label="Revenue This Month" value="${{ number_format($revenueThisMonth, 2) }}" icon="wallet" accent="emerald" />
            @endif
            @if (auth()->user()->isSuperAdmin())
                <x-stat-card label="Total Clients" :value="$totalClients" icon="briefcase" accent="sky" />
                <x-stat-card label="Open Leads (Company)" :value="$openLeads" icon="target" accent="gold" />
                <x-stat-card label="Total Revenue Collected" value="${{ number_format($totalRevenue, 2) }}" icon="wallet" accent="emerald" />
            @endif
        </div>
    @endif

    <div class="mt-8 glass-panel relative overflow-hidden p-6">
        <div class="glass-sheen"></div>
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-base font-bold text-white">Company Announcements</h2>
            <a href="{{ route('hr.announcements') }}" wire:navigate class="text-xs font-semibold text-gold-300 hover:text-gold-200">View all &rarr;</a>
        </div>
        <div class="space-y-3">
            @forelse ($announcements as $announcement)
                <div class="glass-inset p-4">
                    <div class="flex items-center justify-between">
                        <p class="font-semibold text-white">{{ $announcement->title }}</p>
                        @if ($announcement->pinned)
                            <span class="badge-glass"><x-icon name="megaphone" class="h-3 w-3" /> Pinned</span>
                        @endif
                    </div>
                    <p class="mt-1 text-sm text-white/60">{{ Str::limit($announcement->body, 140) }}</p>
                    <p class="mt-2 text-xs text-white/30">{{ $announcement->poster->name }} &middot; {{ $announcement->created_at->diffForHumans() }}</p>
                </div>
            @empty
                <p class="text-sm text-white/40">No announcements yet.</p>
            @endforelse
        </div>
    </div>
</div>
