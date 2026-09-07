<div>
    <x-page-header title="Users & Role Management" subtitle="Full system access: assign functional roles and manage credentials.">
        <x-slot:actions>
            <a href="{{ route('hradmin.employees') }}" wire:navigate class="btn-glass-primary"><x-icon name="plus" class="h-4 w-4" /> Onboard Employee</a>
        </x-slot:actions>
    </x-page-header>

    @if (session('temp_password'))
        <div class="glass-card mb-6">
            <p class="font-semibold text-white">Password reset for {{ session('temp_password_for') }}</p>
            <p class="mt-1 text-sm text-white/60">New temporary password: <code class="rounded bg-black/40 px-2 py-1 font-mono text-gold-300">{{ session('temp_password') }}</code></p>
        </div>
    @endif

    <div class="space-y-3">
        @foreach ($users as $u)
            <div class="glass-card">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gold-400/20 text-sm font-bold text-gold-300">{{ $u->initials() }}</span>
                        <div>
                            <p class="font-semibold text-white">{{ $u->name }}</p>
                            <p class="text-xs text-white/40">{{ $u->email }} &middot; {{ $u->employee_code }}</p>
                        </div>
                        <div class="flex flex-wrap gap-1 ml-2">
                            @forelse ($u->getRoleNames() as $role)
                                <span class="badge-glass">{{ ucwords(str_replace('_', ' ', $role)) }}</span>
                            @empty
                                <span class="text-xs text-white/30">Employee</span>
                            @endforelse
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button wire:click="resetPassword({{ $u->id }})" wire:confirm="Reset password for {{ $u->name }}?" class="text-xs font-semibold text-white/50 hover:text-white">Reset Password</button>
                        <button wire:click="toggleEdit({{ $u->id }})" class="btn-glass-secondary !px-3 !py-1.5 text-xs">
                            {{ isset($editingRoles[$u->id]) ? 'Cancel' : 'Edit Roles' }}
                        </button>
                    </div>
                </div>

                @if (isset($editingRoles[$u->id]))
                    <div class="mt-4 border-t border-white/10 pt-4">
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            @foreach ($allRoles as $role)
                                <label class="flex items-center gap-2 rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white/70">
                                    <input type="checkbox" wire:model="editingRoles.{{ $u->id }}" value="{{ $role }}" class="rounded border-white/20 bg-white/5 text-gold-400 focus:ring-gold-400/40">
                                    {{ ucwords(str_replace('_', ' ', $role)) }}
                                </label>
                            @endforeach
                        </div>
                        <div class="mt-3 flex justify-end">
                            <x-primary-button wire:click="saveRoles({{ $u->id }})">Save Roles</x-primary-button>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
