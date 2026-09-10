<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <button id="notification-bell-button" @click="open = ! open; if (open) $wire.markAllAsRead()" type="button" class="relative rounded-xl border border-white/10 bg-white/5 p-2.5 text-white/70 transition hover:bg-white/10">
        <x-icon name="bell" class="h-4 w-4" />
        @if ($unreadCount > 0)
            <span class="absolute -right-1 -top-1 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
    </button>

    <div
        x-show="open"
        x-transition
        x-cloak
        class="glass-panel absolute right-0 z-50 mt-2 w-80 max-h-[26rem] overflow-y-auto p-2"
        style="display: none;"
    >
        <div class="glass-sheen"></div>
        <p class="px-2 py-2 text-xs font-semibold uppercase tracking-wide text-white/40">Notifications</p>
        <div class="space-y-1">
            @forelse ($notifications as $n)
                <a
                    href="{{ $n->url ?? '#' }}"
                    @if ($n->url) wire:navigate @endif
                    wire:click="markAsRead({{ $n->id }})"
                    class="block rounded-lg px-3 py-2.5 text-sm transition hover:bg-white/5 {{ $n->read_at ? '' : 'bg-white/5' }}"
                >
                    <div class="flex items-start gap-2">
                        @unless ($n->read_at)
                            <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-gold-400"></span>
                        @else
                            <span class="mt-1.5 h-1.5 w-1.5 shrink-0"></span>
                        @endunless
                        <div class="min-w-0">
                            <p class="font-semibold text-white">{{ $n->title }}</p>
                            @if ($n->body)
                                <p class="mt-0.5 text-xs text-white/50">{{ $n->body }}</p>
                            @endif
                            <p class="mt-1 text-[11px] text-white/30">{{ $n->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </a>
            @empty
                <p class="px-3 py-6 text-center text-sm text-white/40">You're all caught up.</p>
            @endforelse
        </div>
    </div>
</div>
