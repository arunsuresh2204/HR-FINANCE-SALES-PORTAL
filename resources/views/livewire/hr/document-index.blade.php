<div>
    <x-page-header title="My Documents" subtitle="ID proofs, contracts and certifications on file.">
        <x-slot:actions>
            <button wire:click="openForm" class="btn-glass-primary"><x-icon name="upload" class="h-4 w-4" /> Upload Document</button>
        </x-slot:actions>
    </x-page-header>

    <div class="glass-panel relative overflow-hidden">
        <div class="glass-sheen"></div>
        <div class="overflow-x-auto">
            <table class="table-glass">
                <thead><tr><th>Title</th><th>Type</th><th>Uploaded</th><th></th></tr></thead>
                <tbody>
                    @forelse ($documents as $doc)
                        <tr>
                            <td class="font-medium text-white">{{ $doc->title }}</td>
                            <td><x-status-pill :status="$doc->type" /></td>
                            <td>{{ $doc->created_at->format('M j, Y') }}</td>
                            <td class="text-right space-x-3">
                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="text-xs font-semibold text-gold-300 hover:text-gold-200">View</a>
                                <button wire:click="delete({{ $doc->id }})" wire:confirm="Delete this document?" class="text-xs font-semibold text-rose-300 hover:text-rose-200">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-8 text-center text-white/40">No documents uploaded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="glass-panel relative mt-8 overflow-hidden p-6">
        <div class="glass-sheen"></div>
        <h2 class="mb-4 text-base font-bold text-white">Company Policies</h2>
        <div class="space-y-2">
            @forelse ($policies as $policy)
                <a href="{{ Storage::url($policy->file_path) }}" target="_blank" class="glass-inset flex items-center justify-between p-3 transition hover:bg-white/[0.06]">
                    <div class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-400/15 text-sky-300"><x-icon name="document" class="h-4 w-4" /></span>
                        <div>
                            <p class="text-sm font-medium text-white">{{ $policy->title }}</p>
                            <p class="text-xs text-white/40">{{ $policy->description }}</p>
                        </div>
                    </div>
                    <x-icon name="arrow-right" class="h-4 w-4 text-white/30" />
                </a>
            @empty
                <p class="text-sm text-white/40">No policy documents published yet.</p>
            @endforelse
        </div>
    </div>

    <x-modal-glass wire-model="showForm" title="Upload Document">
        <form wire:submit="submit" class="space-y-4">
            <div>
                <x-input-label for="title" value="Title" />
                <x-text-input wire:model="title" id="title" type="text" class="mt-0" placeholder="e.g. Passport Copy" />
                <x-input-error :messages="$errors->get('title')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="type" value="Document Type" />
                <select wire:model="type" id="type" class="input-glass">
                    <option value="id_proof">ID Proof</option>
                    <option value="contract">Contract</option>
                    <option value="certification">Certification</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div>
                <x-input-label for="file" value="File" />
                <input wire:model="file" id="file" type="file" class="input-glass file:mr-3 file:rounded-lg file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-white/80" />
                <x-input-error :messages="$errors->get('file')" class="mt-1" />
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Upload</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
