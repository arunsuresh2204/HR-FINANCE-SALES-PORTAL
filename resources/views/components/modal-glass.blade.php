@props(['wireModel', 'maxWidth' => 'lg', 'title' => ''])

@php
    $widths = ['sm' => 'max-w-sm', 'md' => 'max-w-md', 'lg' => 'max-w-lg', 'xl' => 'max-w-xl', '2xl' => 'max-w-2xl'];
@endphp

<div x-data="{ show: @entangle($wireModel) }" x-show="show" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4" style="display: none;">
    <div x-show="show" x-transition.opacity class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="show = false"></div>

    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        class="glass-panel relative w-full {{ $widths[$maxWidth] ?? $widths['lg'] }} max-h-[90vh] overflow-y-auto p-6"
    >
        <div class="glass-sheen"></div>

        <div class="mb-4 flex items-start justify-between gap-4">
            <h3 class="text-lg font-bold text-white">{{ $title }}</h3>
            <button @click="show = false" type="button" class="text-white/40 hover:text-white">
                <x-icon name="x" class="h-5 w-5" />
            </button>
        </div>

        {{ $slot }}
    </div>
</div>
