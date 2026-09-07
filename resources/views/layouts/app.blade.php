<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Nexstarc Portal') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&amp;display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased" x-data="{ sidebarOpen: false, toasts: [] }" x-on:toast.window="
            const id = Date.now();
            toasts.push({ id, message: $event.detail.message, type: $event.detail.type || 'success' });
            setTimeout(() => { toasts = toasts.filter(t => t.id !== id) }, 4000);
        ">
        <div>
        <!-- Toasts -->
        <div class="pointer-events-none fixed inset-x-0 top-4 z-[100] flex flex-col items-center gap-2 px-4">
            <template x-for="toast in toasts" :key="toast.id">
                <div
                    x-show="true"
                    x-transition
                    class="glass pointer-events-auto flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold shadow-glass"
                    :class="toast.type === 'error' ? 'text-rose-300' : 'text-emerald-300'"
                    x-text="toast.message"
                ></div>
            </template>
        </div>

        <div class="flex min-h-screen">
            <x-nav-sidebar />

            <div class="flex min-w-0 flex-1 flex-col lg:pl-[272px]">
                <!-- Topbar -->
                <header class="sticky top-0 z-30 flex items-center gap-4 px-4 py-4 sm:px-6 lg:px-8">
                    <button @click="sidebarOpen = !sidebarOpen" class="glass rounded-xl p-2.5 text-white/70 lg:hidden">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    </button>

                    <div class="glass-panel flex flex-1 items-center justify-between px-5 py-3">
                        <div>
                            @if (isset($header))
                                <div class="text-base font-bold text-white sm:text-lg">{{ $header }}</div>
                            @else
                                <div class="text-base font-bold text-white sm:text-lg">{{ config('app.name') }}</div>
                            @endif
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="badge-glass hidden sm:inline-flex">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                {{ now()->format('D, M j') }}
                            </span>
                            <a href="{{ route('profile') }}" wire:navigate class="flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 py-1.5 pl-1.5 pr-3 text-sm font-semibold text-white/80 transition hover:bg-white/10">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-gold-400 text-xs font-extrabold text-ink-950">
                                    {{ auth()->user()->initials() }}
                                </span>
                                <span class="hidden sm:inline">{{ Str::of(auth()->user()->name)->before(' ') }}</span>
                            </a>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 px-4 pb-10 sm:px-6 lg:px-8">
                    {{ $slot }}
                </main>
            </div>
        </div>
        </div>
    </body>
</html>
