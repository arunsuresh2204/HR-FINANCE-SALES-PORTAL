<div>
    <x-page-header title="Welcome back, {{ Str::of(auth()->user()->name)->before(' ') }}" subtitle="Here's what's happening across your workspace today." />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Today's Attendance" :value="$todayAttendance ? 'Clocked In' : 'Not Clocked In'" icon="clock" :accent="$todayAttendance ? 'emerald' : 'rose'" :href="route('hr.attendance')" />
        <x-stat-card label="Leave Days Used (Year)" :value="$approvedLeaveDaysThisYear" icon="calendar" accent="sky" :href="route('hr.leave')" />
        <x-stat-card label="Pending Leave Requests" :value="$pendingLeave" icon="calendar" accent="gold" :href="route('hr.leave')" />
        <x-stat-card label="Pending Expenses" :value="$pendingExpenses" icon="receipt" accent="violet" :href="route('hr.expenses')" />
    </div>

    @if (auth()->user()->can('access_timesheets') || auth()->user()->can('access_marketing_logs') || auth()->user()->can('access_sales_leads') || auth()->user()->isSalesPerson())
        <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @if (auth()->user()->can('access_timesheets'))
                <x-stat-card label="Hours Logged Today" :value="number_format($todayHours ?? 0, 1)" icon="code" accent="sky" hint="{{ number_format($weekHours ?? 0, 1) }}h this week" :href="route('work.timesheets')" />
            @endif
            @if (auth()->user()->can('access_marketing_logs'))
                <x-stat-card label="Marketing Hours Today" :value="number_format($todayMarketingHours ?? 0, 1)" icon="megaphone" accent="violet" :href="route('work.marketing-logs')" />
            @endif
            @if (auth()->user()->can('access_sales_leads'))
                <x-stat-card label="Open Leads" :value="$myLeadsOpen" icon="target" accent="sky" :href="route('sales.leads')" />
                <x-stat-card label="Won This Month" :value="$myLeadsWonThisMonth" icon="briefcase" accent="emerald" :href="route('sales.leads')" />
            @endif
        </div>
    @endif

    @if (auth()->user()->isSalesPerson())
        <div class="glass-card mt-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-xs font-bold uppercase tracking-widest text-white/40">Sales Performance</h2>
                <div class="flex items-center gap-2">
                    <select wire:model.live="salesMonth" class="input-glass w-36">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="salesYear" class="input-glass w-28">
                        @foreach (array_reverse(range(now()->year - 3, now()->year)) as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                <div>
                    <p class="text-xs text-white/40">Target</p>
                    <p class="mt-1 text-xl font-extrabold text-white">{{ $salesTargetAmount > 0 ? \App\Support\Currency::format($salesTargetAmount, 'INR') : '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-white/40">Achieved</p>
                    <p class="mt-1 text-xl font-extrabold text-emerald-300">{{ \App\Support\Currency::format($salesAchieved, 'INR') }}</p>
                </div>
                <div>
                    <p class="text-xs text-white/40">Attainment</p>
                    <p class="mt-1 text-xl font-extrabold text-gold-300">{{ $salesTargetAmount > 0 ? number_format(min(999, $salesAchieved / $salesTargetAmount * 100)) . '%' : '—' }}</p>
                </div>
            </div>
            @if ($salesTargetAmount == 0)
                <p class="mt-3 text-xs text-white/30">No target set for this month yet — achieved amount still reflects recorded payments.</p>
            @endif
            <a href="{{ route('sales.targets') }}" wire:navigate class="mt-4 inline-block text-xs font-semibold text-gold-300 hover:text-gold-200">View full Sales Targets &rarr;</a>
        </div>
    @endif

    @if (auth()->user()->can('access_hr_admin') || auth()->user()->can('access_finance_admin') || auth()->user()->isSuperAdmin())
        <h2 class="mb-3 mt-8 text-xs font-bold uppercase tracking-widest text-white/40">Admin Overview</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @if (auth()->user()->can('access_hr_admin'))
                <x-stat-card label="Active Headcount" :value="$headcount" icon="users" accent="emerald" :href="route('hradmin.employees')" />
                <x-stat-card label="Pending Leave Approvals" :value="$pendingLeaveApprovals" icon="calendar" accent="gold" :href="route('hradmin.leave-approvals')" />
                <x-stat-card label="Pending Resignations" :value="$pendingResignations" icon="exit" accent="rose" :href="route('hradmin.resignations')" />
            @endif
            @if (auth()->user()->can('access_finance_admin'))
                <x-stat-card label="Pending Billing Requests" :value="$pendingBillingRequests" icon="inbox" accent="sky" :href="route('finance.billing-requests')" />
                <x-stat-card label="Outstanding Invoices" value="${{ number_format($outstandingInvoices, 2) }}" icon="cash" accent="rose" :href="route('finance.invoices')" />
                <x-stat-card label="Pending Expense Approvals" :value="$pendingExpenseApprovals" icon="receipt" accent="violet" :href="route('finance.expenses')" />
                <x-stat-card label="Revenue This Month" value="{{ \App\Support\Currency::format($revenueThisMonth, 'INR') }}" icon="wallet" accent="emerald" :href="route('finance.reports')" />
            @endif
            @if (auth()->user()->isSuperAdmin())
                <x-stat-card label="Total Clients" :value="$totalClients" icon="briefcase" accent="sky" :href="route('sales.clients')" />
                <x-stat-card label="Open Leads (Company)" :value="$openLeads" icon="target" accent="gold" :href="route('sales.leads')" />
                <x-stat-card label="Total Revenue Collected" value="{{ \App\Support\Currency::format($totalRevenue, 'INR') }}" icon="wallet" accent="emerald" :href="route('finance.reports')" />
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
