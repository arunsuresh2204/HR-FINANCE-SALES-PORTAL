@props(['label', 'value', 'icon' => 'chart', 'hint' => null, 'accent' => 'gold'])

@php
    $accents = [
        'gold' => 'bg-gold-400/15 text-gold-300',
        'emerald' => 'bg-emerald-400/15 text-emerald-300',
        'sky' => 'bg-sky-400/15 text-sky-300',
        'rose' => 'bg-rose-400/15 text-rose-300',
        'violet' => 'bg-violet-400/15 text-violet-300',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'glass-card-hover']) }}>
    <div class="flex items-start justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-white/40">{{ $label }}</p>
            <p class="mt-2 text-2xl font-extrabold text-white">{{ $value }}</p>
            @if ($hint)
                <p class="mt-1 text-xs text-white/40">{{ $hint }}</p>
            @endif
        </div>
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $accents[$accent] ?? $accents['gold'] }}">
            <x-icon :name="$icon" class="h-5 w-5" />
        </span>
    </div>
</div>
