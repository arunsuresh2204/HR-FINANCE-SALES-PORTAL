<div>
    <x-page-header :title="$user->name" :subtitle="$user->employee_code . ' · ' . ($user->email)">
        <x-slot:actions>
            <a href="{{ route('hradmin.employees') }}" wire:navigate class="btn-glass-secondary"><x-icon name="arrow-right" class="h-4 w-4 rotate-180" /> Back</a>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="glass-card">
                <h2 class="mb-4 text-base font-bold text-white">Employment Details</h2>
                <form wire:submit="saveDetails" class="space-y-4">
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
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="employment_status" value="Employment Status" />
                            <select wire:model="employment_status" id="employment_status" class="input-glass">
                                <option value="active">Active</option>
                                <option value="on_notice">On Notice</option>
                                <option value="resigned">Resigned</option>
                                <option value="offboarded">Offboarded</option>
                            </select>
                        </div>
                        <div>
                            <x-input-label for="monthly_salary" value="Monthly Salary ($)" />
                            <x-text-input wire:model="monthly_salary" id="monthly_salary" type="number" step="0.01" class="mt-0" />
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <x-primary-button>Save Details</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="glass-card">
                <h2 class="mb-4 text-base font-bold text-white">Functional Roles</h2>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach ($allRoles as $role)
                        <label class="flex items-center gap-2 rounded-lg border border-white/10 bg-white/5 px-3 py-2 text-sm text-white/70">
                            <input type="checkbox" wire:model="roles" value="{{ $role }}" class="rounded border-white/20 bg-white/5 text-gold-400 focus:ring-gold-400/40">
                            {{ ucwords(str_replace('_', ' ', $role)) }}
                        </label>
                    @endforeach
                </div>
                <div class="mt-4 flex justify-end">
                    <x-primary-button wire:click="saveRoles">Update Roles</x-primary-button>
                </div>
            </div>

            <div class="glass-card">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-base font-bold text-white">Assets</h2>
                    <button wire:click="$set('showAssetForm', true)" class="text-xs font-semibold text-gold-300 hover:text-gold-200">+ Assign Asset</button>
                </div>
                <div class="space-y-2">
                    @forelse ($assets as $asset)
                        <div class="glass-inset flex items-center justify-between p-3">
                            <div>
                                <p class="text-sm font-medium text-white">{{ $asset->item_name }}</p>
                                <p class="text-xs text-white/40">{{ $asset->item_type }} &middot; Assigned {{ $asset->assigned_date->format('M j, Y') }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <x-status-pill :status="$asset->status" />
                                @if ($asset->status === 'assigned')
                                    <button wire:click="markAssetReturned({{ $asset->id }})" class="text-xs font-semibold text-white/50 hover:text-white">Mark Returned</button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-white/40">No assets assigned.</p>
                    @endforelse
                </div>

                @if ($showAssetForm)
                    <form wire:submit="assignAsset" class="mt-4 flex items-end gap-3 border-t border-white/10 pt-4">
                        <div class="flex-1">
                            <x-input-label for="item_name" value="Item Name" />
                            <x-text-input wire:model="item_name" id="item_name" type="text" class="mt-0" />
                        </div>
                        <div class="flex-1">
                            <x-input-label for="item_type" value="Type" />
                            <x-text-input wire:model="item_type" id="item_type" type="text" class="mt-0" placeholder="Laptop, Software..." />
                        </div>
                        <x-primary-button>Assign</x-primary-button>
                    </form>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div class="glass-card">
                <p class="text-xs font-semibold uppercase tracking-wide text-white/40">Contact</p>
                <div class="mt-3 space-y-2 text-sm">
                    <p class="text-white/70">{{ $user->phone ?? 'No phone on file' }}</p>
                    <p class="text-white/70">{{ $user->address ?? 'No address on file' }}</p>
                    <p class="text-white/40">Emergency: {{ $user->emergency_contact_name ?? '—' }} {{ $user->emergency_contact_phone ? '('.$user->emergency_contact_phone.')' : '' }}</p>
                </div>
            </div>

            <div class="glass-card">
                <p class="text-xs font-semibold uppercase tracking-wide text-white/40">Leave (This Year)</p>
                <p class="mt-2 text-2xl font-extrabold text-white">{{ $leaveBalanceUsed }} <span class="text-sm font-normal text-white/40">days used</span></p>
            </div>

            <div class="glass-card">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-white/40">Recent Attendance</p>
                <div class="space-y-2">
                    @forelse ($recentAttendance as $att)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-white/60">{{ $att->work_date->format('M j') }}</span>
                            <x-status-pill :status="$att->status" />
                        </div>
                    @empty
                        <p class="text-sm text-white/40">No attendance records.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
