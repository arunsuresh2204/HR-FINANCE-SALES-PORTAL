<x-app-layout>
    <x-slot name="header">Profile</x-slot>

    <x-page-header title="My Profile" subtitle="Manage your personal information and account settings." />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="glass-card lg:col-span-1">
            <div class="flex flex-col items-center text-center">
                <span class="flex h-20 w-20 items-center justify-center rounded-2xl bg-gold-400 text-2xl font-extrabold text-ink-950">
                    {{ auth()->user()->initials() }}
                </span>
                <p class="mt-4 text-lg font-bold text-white">{{ auth()->user()->name }}</p>
                <p class="text-sm text-white/45">{{ auth()->user()->designation ?? 'Employee' }}</p>
                <p class="mt-3"><x-status-pill :status="auth()->user()->employment_status" /></p>
            </div>
            <div class="mt-6 space-y-3 border-t border-white/10 pt-5 text-sm">
                <div class="flex justify-between"><span class="text-white/40">Employee Code</span><span class="font-medium text-white">{{ auth()->user()->employee_code }}</span></div>
                <div class="flex justify-between"><span class="text-white/40">Department</span><span class="font-medium text-white">{{ auth()->user()->department ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-white/40">Joined</span><span class="font-medium text-white">{{ auth()->user()->date_of_joining?->format('M j, Y') ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-white/40">Email</span><span class="font-medium text-white truncate">{{ auth()->user()->email }}</span></div>
                <div class="flex flex-wrap gap-1.5 pt-1">
                    @foreach (auth()->user()->getRoleNames() as $role)
                        <span class="badge-glass">{{ ucwords(str_replace('_', ' ', $role)) }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="glass-card">
                <livewire:profile.update-profile-information-form />
            </div>

            <div class="glass-card">
                <livewire:profile.update-employee-profile-form />
            </div>

            <div class="glass-card">
                <livewire:profile.update-password-form />
            </div>
        </div>
    </div>
</x-app-layout>
