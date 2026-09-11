<div>
    <x-page-header title="Functional Roles" subtitle="Create roles and control exactly which parts of the platform each one can access.">
        <x-slot:actions>
            <button wire:click="openAddForm" class="btn-glass-primary"><x-icon name="plus" class="h-4 w-4" /> Add Role</button>
        </x-slot:actions>
    </x-page-header>

    <div class="glass-card mb-6">
        <p class="text-sm text-white/60">
            A new role becomes assignable straight away from an employee's <span class="font-semibold text-white/80">Functional Roles</span> section.
            Use the <span class="font-semibold text-white/80">Feature Access</span> grid below to decide what that role — or any existing role — can actually see and use. Checking a box grants access immediately; no code changes or deploys needed.
            Check <span class="font-semibold text-white/80">Team Manager</span> alongside a feature (e.g. Timesheets) to make that role see its whole reporting line's data there — set who reports to whom from each employee's profile.
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

    <div class="glass-panel relative mt-6 overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="p-4">
            <h2 class="text-base font-bold text-white">Feature Access</h2>
            <p class="mt-1 text-xs text-white/40">Check a box to grant that role access to a feature area; uncheck to revoke it. Changes apply immediately to every user with that role.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th class="sticky left-0 z-10 bg-ink-950">Role</th>
                        @foreach ($features as $key => $label)
                            <th class="whitespace-nowrap text-center" title="{{ $featureDescriptions[$key] }}">{{ $label }}</th>
                        @endforeach
                        <th class="whitespace-nowrap border-l border-white/10 pl-4 text-center" title="{{ $teamManagerDescription }}">{{ $teamManagerLabel }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr wire:key="perm-role-{{ $role->id }}">
                            <td class="sticky left-0 z-10 whitespace-nowrap bg-ink-950 font-medium text-white">
                                {{ $role->name }}
                                @if ($role->isCore)
                                    <span class="ml-1 text-[10px] font-normal text-white/30">core</span>
                                @endif
                            </td>
                            @foreach ($features as $key => $label)
                                @php
                                    $granted = in_array($key, $role->grantedPermissions, true);
                                    $locked = $role->name === 'super_admin' && $key === 'access_super_admin';
                                @endphp
                                <td class="text-center">
                                    <input
                                        type="checkbox"
                                        @checked($granted)
                                        @disabled($locked)
                                        title="{{ $locked ? 'Super Admin must always keep Super Admin access.' : $featureDescriptions[$key] }}"
                                        wire:click="togglePermission({{ $role->id }}, '{{ $key }}')"
                                        class="h-4 w-4 rounded border-white/20 bg-white/5 text-gold-400 focus:ring-gold-400/40 disabled:opacity-40"
                                    >
                                </td>
                            @endforeach
                            <td class="border-l border-white/10 pl-4 text-center">
                                <input
                                    type="checkbox"
                                    @checked(in_array($teamManagerPermission, $role->grantedPermissions, true))
                                    title="{{ $teamManagerDescription }}"
                                    wire:click="togglePermission({{ $role->id }}, '{{ $teamManagerPermission }}')"
                                    class="h-4 w-4 rounded border-emerald-400/30 bg-white/5 text-emerald-400 focus:ring-emerald-400/40"
                                >
                            </td>
                        </tr>
                    @endforeach
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
