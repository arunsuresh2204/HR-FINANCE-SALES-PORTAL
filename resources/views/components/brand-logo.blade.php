@props(['size' => 'md', 'withText' => true])

@php
    $dims = match ($size) {
        'sm' => 'h-7 w-7',
        'lg' => 'h-14 w-14',
        'xl' => 'h-20 w-20',
        default => 'h-9 w-9',
    };
    $textSize = match ($size) {
        'sm' => 'text-sm',
        'lg' => 'text-2xl',
        'xl' => 'text-4xl',
        default => 'text-lg',
    };
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <span class="relative inline-flex {{ $dims }} shrink-0 items-center justify-center rounded-[28%] bg-black shadow-glass ring-1 ring-white/10">
        <span class="absolute left-[14%] top-[14%] h-[24%] w-[24%] rounded-[2px] bg-gold-400"></span>
        <svg viewBox="0 0 100 100" class="h-[62%] w-[62%] translate-x-[6%]" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 92V38C12 24.7 22.7 14 36 14H60C79.3 14 95 29.7 95 49V92H73V49C73 41.8 67.2 36 60 36H34V92H12Z" fill="white" />
        </svg>
    </span>
    @if ($withText)
        <span class="flex flex-col leading-none">
            <span class="{{ $textSize }} font-extrabold tracking-tight text-white">nexstarc<span class="text-gold-400">.</span></span>
            @if ($size !== 'sm')
                <span class="mt-0.5 text-[10px] font-semibold uppercase tracking-[0.2em] text-white/40">technologies</span>
            @endif
        </span>
    @endif
</div>
