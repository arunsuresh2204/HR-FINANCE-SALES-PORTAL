<div>
    <x-page-header title="Policy Documents" subtitle="Manage the company handbook and policy documents visible to all staff.">
        <x-slot:actions>
            <button wire:click="openForm" class="btn-glass-primary"><x-icon name="upload" class="h-4 w-4" /> Publish Policy</button>
        </x-slot:actions>
    </x-page-header>

    <div class="space-y-2">
        @forelse ($policies as $policy)
            <div class="glass-card-hover flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-400/15 text-sky-300"><x-icon name="document" class="h-5 w-5" /></span>
                    <div>
                        <p class="font-medium text-white">{{ $policy->title }}</p>
                        <p class="text-sm text-white/45">{{ $policy->description }}</p>
                        <p class="mt-1 text-xs text-white/30">Uploaded by {{ $policy->uploader->name }} &middot; {{ $policy->created_at->format('M j, Y') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ Storage::url($policy->file_path) }}" target="_blank" class="text-xs font-semibold text-gold-300 hover:text-gold-200">View</a>
                    <button wire:click="delete({{ $policy->id }})" wire:confirm="Remove this policy document?" class="text-white/30 hover:text-rose-300"><x-icon name="trash" class="h-4 w-4" /></button>
                </div>
            </div>
        @empty
            <p class="text-sm text-white/40">No policy documents published yet.</p>
        @endforelse
    </div>

    <x-modal-glass wire-model="showForm" title="Publish Policy Document">
        <form wire:submit="submit" class="space-y-4">
            <div>
                <x-input-label for="title" value="Title" />
                <x-text-input wire:model="title" id="title" type="text" class="mt-0" placeholder="e.g. Leave Policy" />
                <x-input-error :messages="$errors->get('title')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="description" value="Description" />
                <textarea wire:model="description" id="description" rows="2" class="input-glass"></textarea>
            </div>
            <div>
                <x-input-label for="file" value="File" />
                <input wire:model="file" id="file" type="file" class="input-glass file:mr-3 file:rounded-lg file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-white/80" />
                <x-input-error :messages="$errors->get('file')" class="mt-1" />
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Publish</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
