<div>
    <!-- Mobile overlay -->
    <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false" x-cloak></div>

    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="fixed inset-y-0 left-0 z-50 flex w-[272px] flex-col p-4 transition-transform duration-200 ease-out"
    >
        <div class="glass-panel relative flex h-full flex-col overflow-hidden">
            <div class="glass-sheen"></div>

            <div class="flex items-center justify-between px-5 pt-5">
                <a href="{{ route('dashboard') }}" wire:navigate>
                    <x-brand-logo />
                </a>
                <button @click="sidebarOpen = false" class="text-white/40 hover:text-white lg:hidden">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <nav class="mt-6 flex-1 space-y-6 overflow-y-auto px-3 pb-4">
                <div class="space-y-1">
                    <a href="{{ route('dashboard') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <x-icon name="home" class="h-4 w-4 shrink-0" />
                        Dashboard
                    </a>
                </div>

                <div>
                    <p class="px-3 text-[10px] font-bold uppercase tracking-widest text-white/30">My Workspace</p>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('hr.attendance') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('hr.attendance') ? 'active' : '' }}"><x-icon name="clock" class="h-4 w-4 shrink-0" />Attendance</a>
                        <a href="{{ route('hr.leave') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('hr.leave') ? 'active' : '' }}"><x-icon name="calendar" class="h-4 w-4 shrink-0" />Leave</a>
                        <a href="{{ route('hr.payslips') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('hr.payslips') ? 'active' : '' }}"><x-icon name="cash" class="h-4 w-4 shrink-0" />Payslips</a>
                        <a href="{{ route('hr.expenses') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('hr.expenses') ? 'active' : '' }}"><x-icon name="receipt" class="h-4 w-4 shrink-0" />Expenses</a>
                        <a href="{{ route('hr.assets') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('hr.assets') ? 'active' : '' }}"><x-icon name="box" class="h-4 w-4 shrink-0" />Assets</a>
                        <a href="{{ route('hr.documents') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('hr.documents') ? 'active' : '' }}"><x-icon name="document" class="h-4 w-4 shrink-0" />Documents</a>
                        <a href="{{ route('hr.announcements') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('hr.announcements') ? 'active' : '' }}"><x-icon name="megaphone" class="h-4 w-4 shrink-0" />Announcements</a>
                        <a href="{{ route('hr.resignation') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('hr.resignation') ? 'active' : '' }}"><x-icon name="exit" class="h-4 w-4 shrink-0" />Resignation</a>
                    </div>
                </div>

                @if (auth()->user()->isManager() || auth()->user()->isHrAdmin())
                    <div>
                        <p class="px-3 text-[10px] font-bold uppercase tracking-widest text-white/30">Team</p>
                        <div class="mt-2 space-y-1">
                            <a href="{{ route('org-chart') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('org-chart') ? 'active' : '' }}"><x-icon name="users" class="h-4 w-4 shrink-0" />Org Chart</a>
                        </div>
                    </div>
                @endif

                @if (auth()->user()->isProgrammer() || auth()->user()->isMarketer())
                    <div>
                        <p class="px-3 text-[10px] font-bold uppercase tracking-widest text-white/30">Daily Log</p>
                        <div class="mt-2 space-y-1">
                            @if (auth()->user()->isProgrammer())
                                <a href="{{ route('work.timesheets') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('work.timesheets') ? 'active' : '' }}"><x-icon name="code" class="h-4 w-4 shrink-0" />Timesheet</a>
                            @endif
                            @if (auth()->user()->isMarketer())
                                <a href="{{ route('work.marketing-logs') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('work.marketing-logs') ? 'active' : '' }}"><x-icon name="megaphone" class="h-4 w-4 shrink-0" />Marketing Log</a>
                            @endif
                        </div>
                    </div>
                @endif

                @if (auth()->user()->canManageLeads() || auth()->user()->isManager())
                    <div>
                        <p class="px-3 text-[10px] font-bold uppercase tracking-widest text-white/30">Sales</p>
                        <div class="mt-2 space-y-1">
                            @if (auth()->user()->canManageLeads())
                                <a href="{{ route('sales.leads') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('sales.leads*') ? 'active' : '' }}"><x-icon name="target" class="h-4 w-4 shrink-0" />Leads Pipeline</a>
                            @endif
                            @if (auth()->user()->isSalesExec())
                                <a href="{{ route('sales.clients') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('sales.clients*') ? 'active' : '' }}"><x-icon name="briefcase" class="h-4 w-4 shrink-0" />Clients</a>
                            @endif
                            @if (auth()->user()->isSalesExec() || auth()->user()->isManager())
                                <a href="{{ route('sales.targets') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('sales.targets') ? 'active' : '' }}"><x-icon name="chart" class="h-4 w-4 shrink-0" />Sales Targets</a>
                            @endif
                        </div>
                    </div>
                @endif

                @if (auth()->user()->isHrAdmin())
                    <div>
                        <p class="px-3 text-[10px] font-bold uppercase tracking-widest text-white/30">HR Admin</p>
                        <div class="mt-2 space-y-1">
                            <a href="{{ route('hradmin.employees') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('hradmin.employees*') ? 'active' : '' }}"><x-icon name="users" class="h-4 w-4 shrink-0" />Employees</a>
                            <a href="{{ route('hradmin.leave-approvals') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('hradmin.leave-approvals') ? 'active' : '' }}"><x-icon name="check" class="h-4 w-4 shrink-0" />Leave Approvals</a>
                            <a href="{{ route('hradmin.holidays') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('hradmin.holidays') ? 'active' : '' }}"><x-icon name="calendar" class="h-4 w-4 shrink-0" />Holiday Calendar</a>
                            <a href="{{ route('hradmin.resignations') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('hradmin.resignations') ? 'active' : '' }}"><x-icon name="exit" class="h-4 w-4 shrink-0" />Offboarding</a>
                            <a href="{{ route('hradmin.policies') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('hradmin.policies') ? 'active' : '' }}"><x-icon name="document" class="h-4 w-4 shrink-0" />Policies</a>
                            <a href="{{ route('hradmin.reports') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('hradmin.reports') ? 'active' : '' }}"><x-icon name="chart" class="h-4 w-4 shrink-0" />HR Reports</a>
                        </div>
                    </div>
                @endif

                @if (auth()->user()->isFinanceAdmin())
                    <div>
                        <p class="px-3 text-[10px] font-bold uppercase tracking-widest text-white/30">Finance Admin</p>
                        <div class="mt-2 space-y-1">
                            <a href="{{ route('finance.billing-requests') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('finance.billing-requests') ? 'active' : '' }}"><x-icon name="inbox" class="h-4 w-4 shrink-0" />Billing Requests</a>
                            <a href="{{ route('finance.invoices') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('finance.invoices*') ? 'active' : '' }}"><x-icon name="cash" class="h-4 w-4 shrink-0" />Invoices</a>
                            <a href="{{ route('finance.expenses') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('finance.expenses') ? 'active' : '' }}"><x-icon name="receipt" class="h-4 w-4 shrink-0" />Expense Approvals</a>
                            <a href="{{ route('finance.payroll') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('finance.payroll') ? 'active' : '' }}"><x-icon name="wallet" class="h-4 w-4 shrink-0" />Payroll</a>
                            <a href="{{ route('finance.reports') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('finance.reports') ? 'active' : '' }}"><x-icon name="chart" class="h-4 w-4 shrink-0" />Financial Reports</a>
                        </div>
                    </div>
                @endif

                @if (auth()->user()->isSuperAdmin())
                    <div>
                        <p class="px-3 text-[10px] font-bold uppercase tracking-widest text-white/30">System</p>
                        <div class="mt-2 space-y-1">
                            <a href="{{ route('admin.users') }}" wire:navigate class="nav-link-glass {{ request()->routeIs('admin.users*') ? 'active' : '' }}"><x-icon name="shield" class="h-4 w-4 shrink-0" />Users &amp; Roles</a>
                        </div>
                    </div>
                @endif
            </nav>

            <div class="border-t border-white/10 p-3">
                <div class="flex items-center gap-3 rounded-xl p-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-gold-400 text-sm font-extrabold text-ink-950">
                        {{ auth()->user()->initials() }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-white/40">{{ auth()->user()->designation ?? auth()->user()->email }}</p>
                    </div>
                </div>
                <div class="mt-2 flex gap-2">
                    <a href="{{ route('profile') }}" wire:navigate class="flex-1 rounded-lg px-3 py-2 text-center text-xs font-semibold text-white/60 hover:bg-white/5 hover:text-white">Profile</a>
                    <form method="POST" action="{{ route('logout') }}" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full rounded-lg px-3 py-2 text-center text-xs font-semibold text-white/60 hover:bg-white/5 hover:text-white">Log out</button>
                    </form>
                </div>
            </div>
        </div>
    </aside>
</div>
