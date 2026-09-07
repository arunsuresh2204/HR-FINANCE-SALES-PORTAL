<div>
    <x-page-header title="My Assets" subtitle="Company equipment and licenses assigned to you." />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($assets as $asset)
            <div class="glass-card-hover">
                <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gold-400/15 text-gold-300">
                        <x-icon name="box" class="h-5 w-5" />
                    </span>
                    <x-status-pill :status="$asset->status" />
                </div>
                <p class="mt-3 font-semibold text-white">{{ $asset->item_name }}</p>
                <p class="text-sm text-white/45">{{ $asset->item_type ?? 'General' }}</p>
                <p class="mt-3 text-xs text-white/35">Assigned {{ $asset->assigned_date->format('M j, Y') }}</p>
                @if ($asset->return_date)
                    <p class="text-xs text-white/35">Returned {{ $asset->return_date->format('M j, Y') }}</p>
                @endif
            </div>
        @empty
            <p class="col-span-full text-sm text-white/40">No assets assigned to you yet.</p>
        @endforelse
    </div>
</div>
