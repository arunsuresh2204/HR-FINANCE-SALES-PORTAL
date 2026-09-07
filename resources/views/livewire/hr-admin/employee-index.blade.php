<div>
    <x-page-header title="Employee Directory" subtitle="Onboard new employees and manage records.">
        <x-slot:actions>
            <button wire:click="openForm" class="btn-glass-primary"><x-icon name="plus" class="h-4 w-4" /> Onboard Employee</button>
        </x-slot:actions>
    </x-page-header>

    @if (session('temp_password'))
        <div class="glass-card mb-6 border-gold-400/30">
            <p class="font-semibold text-white">Account created for {{ session('temp_password_for') }}</p>
            <p class="mt-1 text-sm text-white/60">Temporary password: <code class="rounded bg-black/40 px-2 py-1 font-mono text-gold-300">{{ session('temp_password') }}</code></p>
            <p class="mt-1 text-xs text-white/40">Share this securely with the employee. They should change it after first login.</p>
        </div>
    @endif

    <div class="mb-4">
        <div class="relative max-w-sm">
            <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-white/30" />
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search employees..." class="input-glass pl-9">
        </div>
    </div>

    <div class="glass-panel relative overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead><tr><th>Employee</th><th>Department</th><th>Roles</th><th>Status</th><th>Joined</th><th></th></tr></thead>
                <tbody>
                    @forelse ($employees as $emp)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gold-400/20 text-xs font-bold text-gold-300">{{ $emp->initials() }}</span>
                                    <div>
                                        <p class="font-medium text-white">{{ $emp->name }}</p>
                                        <p class="text-xs text-white/40">{{ $emp->employee_code }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $emp->department ?? '—' }}</td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($emp->getRoleNames() as $role)
                                        <span class="badge-glass">{{ ucwords(str_replace('_', ' ', $role)) }}</span>
                                    @empty
                                        <span class="text-xs text-white/30">Employee</span>
                                    @endforelse
                                </div>
                            </td>
                            <td><x-status-pill :status="$emp->employment_status" /></td>
                            <td>{{ $emp->date_of_joining?->format('M j, Y') ?? '—' }}</td>
                            <td class="text-right">
                                <a href="{{ route('hradmin.employees.show', $emp) }}" wire:navigate class="text-xs font-semibold text-gold-300 hover:text-gold-200">Manage</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-white/40">No employees found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $employees->links() }}</div>
    </div>

    <x-modal-glass wire-model="showForm" title="Onboard New Employee" max-width="xl">
        <form wire:submit="createEmployee" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="name" value="Full Name" />
                    <x-text-input wire:model="name" id="name" type="text" class="mt-0" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input wire:model="email" id="email" type="email" class="mt-0" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="designation" value="Designation" />
                    <x-text-input wire:model="designation" id="designation" type="text" class="mt-0" />
                </div>
                <div>
                    <x-input-label for="department" value="Department" />
                    <x-text-input wire:model="department" id="department" type="text" class="mt-0" />
                </div>
            </div>
            <div>
                <x-input-label for="date_of_joining" value="Date of Joining" />
                <x-text-input wire:model="date_of_joining" id="date_of_joining" type="date" class="mt-0" />
                <x-input-error :messages="$errors->get('date_of_joining')" class="mt-1" />
            </div>
            <div>
                <x-input-label value="Functional Roles" />
                <div class="mt-2 grid grid-cols-2 gap-2">
                    @foreach ($allRoles as $role)
                        <label class="flex items-center gap-2 rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white/70">
                            <input type="checkbox" wire:model="roles" value="{{ $role }}" class="rounded border-white/20 bg-white/5 text-gold-400 focus:ring-gold-400/40">
                            {{ ucwords(str_replace('_', ' ', $role)) }}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Create Account</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
