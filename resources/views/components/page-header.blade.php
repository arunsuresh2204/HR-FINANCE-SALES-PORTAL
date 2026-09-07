@props(['title', 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between']) }}>
    <div>
        <h1 class="text-xl font-extrabold text-white sm:text-2xl">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-white/45">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
