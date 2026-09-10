<div>
    <x-page-header title="Lead Sources" subtitle="Manage the canonical list of channels salespeople can pick from when logging a lead.">
        <x-slot:actions>
            <button wire:click="openAddForm" class="btn-glass-primary"><x-icon name="plus" class="h-4 w-4" /> Add Source</button>
        </x-slot:actions>
    </x-page-header>

    <div class="glass-card mb-6">
        <p class="text-sm text-white/60">
            The Leads Pipeline "Source" field only offers <span class="font-semibold text-white/80">active</span> sources below, so every lead is tagged consistently — no more "Upwork" vs "upwork" splitting your channel reports.
            Renaming a source updates every existing lead tagged with it; deactivating removes it from the picker without touching history.
        </p>
    </div>

    <div class="glass-panel relative overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Leads Using It</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sources as $source)
                        <tr wire:key="source-{{ $source->id }}">
                            <td>
                                @if ($editingId === $source->id)
                                    <div class="flex items-center gap-2">
                                        <x-text-input wire:model="editingName" type="text" class="mt-0 !py-1.5 text-sm" />
                                    </div>
                                @else
                                    <span class="font-medium text-white">{{ $source->name }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($source->is_active)
                                    <span class="badge-glass !border-emerald-400/25 !bg-emerald-400/10 !text-emerald-200">Active</span>
                                @else
                                    <span class="badge-glass !border-white/15 !bg-white/5 !text-white/40">Inactive</span>
                                @endif
                            </td>
                            <td class="text-white/60">{{ $source->leadCount() }}</td>
                            <td class="text-right">
                                @if ($editingId === $source->id)
                                    <div class="flex justify-end gap-3">
                                        <x-input-error :messages="$errors->get('editingName')" class="mt-1" />
                                        <button wire:click="saveRename" class="text-xs font-semibold text-gold-300 hover:text-gold-200">Save</button>
                                        <button wire:click="cancelRename" class="text-xs font-semibold text-white/40 hover:text-white">Cancel</button>
                                    </div>
                                @else
                                    <div class="flex justify-end gap-3">
                                        <button wire:click="startRename({{ $source->id }})" class="text-xs font-semibold text-gold-300 hover:text-gold-200">Rename</button>
                                        <button wire:click="toggleActive({{ $source->id }})" class="text-xs font-semibold text-white/50 hover:text-white">{{ $source->is_active ? 'Deactivate' : 'Activate' }}</button>
                                        <button wire:click="deleteSource({{ $source->id }})" wire:confirm="Delete this source? This can't be undone." class="text-xs font-semibold text-white/30 hover:text-rose-300">Delete</button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-white/40">No sources yet. Add your first one above.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <x-modal-glass wire-model="showAddForm" title="Add Lead Source">
        <form wire:submit="addSource" class="space-y-4">
            <div>
                <x-input-label for="name" value="Source Name" />
                <x-text-input wire:model="name" id="name" type="text" class="mt-0" placeholder="e.g. TikTok Ads" autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Add Source</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
