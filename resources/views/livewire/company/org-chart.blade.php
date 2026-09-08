<div>
    <x-page-header title="Org Chart" subtitle="Company-wide reporting structure." />

    <div class="glass-panel relative overflow-x-auto p-6">
        <div class="glass-sheen"></div>
        <div class="space-y-6">
            @forelse ($roots as $root)
                <x-org-node :user="$root" :users="$users" />
            @empty
                <p class="text-sm text-white/40">No employees found.</p>
            @endforelse
        </div>
    </div>
</div>
