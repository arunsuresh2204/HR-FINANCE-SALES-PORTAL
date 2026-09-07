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
    <body class="font-sans antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
            <a href="/" wire:navigate class="mb-8">
                <x-brand-logo size="lg" />
            </a>

            <div class="glass-panel relative w-full max-w-md overflow-hidden px-7 py-8 sm:px-9 sm:py-10">
                <div class="glass-sheen"></div>
                {{ $slot }}
            </div>

            <p class="mt-8 text-xs text-white/30">&copy; {{ now()->year }} Nexstarc Technologies. All rights reserved.</p>
        </div>
    </body>
</html>
