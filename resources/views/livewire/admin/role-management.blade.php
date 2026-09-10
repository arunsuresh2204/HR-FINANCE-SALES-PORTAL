<div>
    <x-page-header title="Functional Roles" subtitle="Create new roles that can be assigned to employees from system settings — no code deploy needed.">
        <x-slot:actions>
            <button wire:click="openAddForm" class="btn-glass-primary"><x-icon name="plus" class="h-4 w-4" /> Add Role</button>
        </x-slot:actions>
    </x-page-header>

    <div class="glass-card mb-6">
        <p class="text-sm text-white/60">
            A new role becomes assignable straight away from <span class="font-semibold text-white/80">Users &amp; Roles</span>.
            It won't automatically unlock a dedicated dashboard, nav section, or route access on its own — those still need a follow-up code change.
            It's most useful for tagging employees (e.g. reporting, filtering) or layering onto an existing permission-gated area.
        </p>
    </div>

    <div class="glass-panel relative overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Type</th>
                        <th>Users Assigned</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr wire:key="role-{{ $role->id }}">
                            <td><span class="font-medium text-white">{{ $role->name }}</span></td>
                            <td>
                                @if ($role->isCore)
                                    <span class="badge-glass !border-gold-400/25 !bg-gold-400/10 !text-gold-200">Core (system)</span>
                                @else
                                    <span class="badge-glass !border-sky-400/25 !bg-sky-400/10 !text-sky-200">Custom</span>
                                @endif
                            </td>
                            <td class="text-white/60">{{ $role->userCount }}</td>
                            <td class="text-right">
                                @if ($role->isCore)
                                    <span class="text-xs text-white/25">Protected</span>
                                @else
                                    <button wire:click="deleteRole({{ $role->id }})" wire:confirm="Delete this role? This can't be undone." class="text-xs font-semibold text-white/30 hover:text-rose-300">Delete</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-white/40">No roles yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-modal-glass wire-model="showAddForm" title="Add Functional Role">
        <form wire:submit="addRole" class="space-y-4">
            <div>
                <x-input-label for="name" value="Role Name" />
                <x-text-input wire:model="name" id="name" type="text" class="mt-0" placeholder="e.g. QA Lead" autofocus />
                <p class="mt-1 text-xs text-white/40">Saved in lowercase, snake_case form (e.g. "QA Lead" becomes "qa_lead").</p>
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Add Role</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
