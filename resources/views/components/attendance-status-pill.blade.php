@props(['info'])

@php
    $map = [
        'on_time' => 'bg-emerald-400/15 text-emerald-300 border-emerald-400/20',
        'grace' => 'bg-orange-400/15 text-orange-300 border-orange-400/20',
        'severe' => 'bg-rose-400/15 text-rose-300 border-rose-400/20',
        'absent' => 'bg-rose-500/20 text-rose-200 border-rose-500/30',
        'on_leave' => 'bg-violet-400/15 text-violet-300 border-violet-400/20',
        'pending' => 'bg-white/10 text-white/50 border-white/15',
        'no_schedule' => 'bg-white/10 text-white/40 border-white/15',
    ];
    $classes = $map[$info['tier']] ?? 'bg-white/10 text-white/60 border-white/15';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-xs font-semibold $classes"]) }}>
    @if ($info['warning'] ?? false)
        <x-icon name="bell" class="h-3 w-3" />
    @endif
    {{ $info['label'] }}
</span>
