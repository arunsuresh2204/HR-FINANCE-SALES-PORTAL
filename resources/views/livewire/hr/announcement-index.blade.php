<div>
    <x-page-header title="Company Announcements" subtitle="Stay up to date with company news.">
        @if (auth()->user()->can('access_hr_admin'))
            <x-slot:actions>
                <button wire:click="openForm" class="btn-glass-primary"><x-icon name="plus" class="h-4 w-4" /> Post Announcement</button>
            </x-slot:actions>
        @endif
    </x-page-header>

    <div class="space-y-4">
        @forelse ($announcements as $a)
            <div class="glass-card-hover">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            @if ($a->pinned)
                                <span class="badge-glass"><x-icon name="megaphone" class="h-3 w-3" /> Pinned</span>
                            @endif
                            <p class="text-lg font-bold text-white">{{ $a->title }}</p>
                        </div>
                        <p class="mt-2 text-sm text-white/60">{{ $a->body }}</p>
                        @if ($a->attachment_path)
                            <div class="mt-3">
                                @if ($a->isImageAttachment())
                                    <a href="{{ Storage::url($a->attachment_path) }}" target="_blank">
                                        <img src="{{ Storage::url($a->attachment_path) }}" alt="Announcement attachment" class="max-h-64 rounded-xl border border-white/10">
                                    </a>
                                @else
                                    <a href="{{ Storage::url($a->attachment_path) }}" target="_blank" class="inline-flex items-center gap-1.5 text-sm font-semibold text-gold-300 hover:text-gold-200">
                                        <x-icon name="document" class="h-4 w-4" /> View Attachment
                                    </a>
                                @endif
                            </div>
                        @endif
                        <p class="mt-3 text-xs text-white/35">{{ $a->poster->name }} &middot; {{ $a->created_at->format('M j, Y g:i A') }}</p>
                    </div>
                    @if (auth()->user()->can('access_hr_admin'))
                        <button wire:click="delete({{ $a->id }})" wire:confirm="Delete this announcement?" class="shrink-0 text-white/30 hover:text-rose-300">
                            <x-icon name="trash" class="h-4 w-4" />
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-white/40">No announcements yet.</p>
        @endforelse
        <div>{{ $announcements->links() }}</div>
    </div>

    <x-modal-glass wire-model="showForm" title="Post Announcement">
        <form wire:submit="submit" class="space-y-4">
            <div>
                <x-input-label for="title" value="Title" />
                <x-text-input wire:model="title" id="title" type="text" class="mt-0" />
                <x-input-error :messages="$errors->get('title')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="body" value="Message" />
                <textarea wire:model="body" id="body" rows="4" class="input-glass"></textarea>
                <x-input-error :messages="$errors->get('body')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="attachment" value="Attachment (optional) — e.g. event poster" />
                <input wire:model="attachment" id="attachment" type="file" accept=".jpg,.jpeg,.png,.pdf" class="input-glass file:mr-3 file:rounded-lg file:border-0 file:bg-white/10 file:px-3 file:py-1.5 file:text-white/80">
                <div wire:loading wire:target="attachment" class="mt-1 text-xs text-white/40">Uploading&hellip;</div>
                <x-input-error :messages="$errors->get('attachment')" class="mt-1" />
            </div>
            <label class="flex items-center gap-2 text-sm text-white/70">
                <input wire:model="pinned" type="checkbox" class="rounded border-white/20 bg-white/5 text-gold-400 focus:ring-gold-400/40">
                Pin to top
            </label>
            <div class="flex justify-end gap-3 pt-2">
                <x-secondary-button type="button" @click="show = false">Cancel</x-secondary-button>
                <x-primary-button>Post</x-primary-button>
            </div>
        </form>
    </x-modal-glass>
</div>
