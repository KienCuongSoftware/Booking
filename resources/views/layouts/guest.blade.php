<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gradient-to-br from-white via-red-50/40 to-white min-h-screen">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-8 sm:pt-0 px-4">
            <div class="mb-6">
                <a href="{{ route('login') }}" class="inline-flex flex-col items-center gap-2 group">
                    <x-application-logo class="w-16 h-16 fill-current text-red-600 transition-transform group-hover:scale-105" />
                    <span class="text-sm font-semibold tracking-wide text-red-700">{{ config('app.name', 'Booking') }}</span>
                </a>
            </div>

            <div
                class="w-full sm:max-w-md bg-white border border-red-100 shadow-xl shadow-red-900/10 overflow-hidden rounded-2xl px-8 py-8 sm:px-10 sm:py-10"
            >
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
