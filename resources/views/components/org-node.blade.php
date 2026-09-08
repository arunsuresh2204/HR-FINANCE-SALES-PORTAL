@props(['user', 'users'])

@php
    $children = $users->where('manager_id', $user->id)->values();

    $tier = $user->isSuperAdmin() ? ['Owner', 'bg-gold-400/15 text-gold-300 border-gold-400/20']
        : ($user->isManager() ? ['Manager', 'bg-sky-400/15 text-sky-300 border-sky-400/20']
        : ($user->isTeamLead() ? ['Team Lead', 'bg-violet-400/15 text-violet-300 border-violet-400/20']
        : ['Employee', 'bg-white/10 text-white/50 border-white/15']));
@endphp

<div class="flex flex-col items-start">
    <div class="glass-inset flex items-center gap-3 p-3">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gold-400/15 text-xs font-bold text-gold-300">{{ $user->initials() }}</span>
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <p class="truncate text-sm font-semibold text-white">{{ $user->name }}</p>
                <span class="badge-glass !px-2 !py-0.5 !text-[10px] {{ $tier[1] }}">{{ $tier[0] }}</span>
            </div>
            <p class="truncate text-xs text-white/40">{{ $user->designation ?? $user->department ?? '—' }}</p>
            @if ($user->additionalManagers->isNotEmpty())
                <p class="mt-0.5 truncate text-[11px] text-white/30">Also reports to: {{ $user->additionalManagers->pluck('name')->join(', ') }}</p>
            @endif
        </div>
    </div>

    @if ($children->isNotEmpty())
        <div class="ml-5 mt-3 space-y-3 border-l border-white/10 pl-5">
            @foreach ($children as $child)
                <x-org-node :user="$child" :users="$users" />
            @endforeach
        </div>
    @endif
</div>
