<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Booking') }}</title>
        <link rel="icon" href="{{ asset('ico.svg') }}" type="image/svg+xml">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gradient-to-br from-sky-50/60 via-white to-slate-50 font-sans text-gray-900 antialiased">
        <div class="flex min-h-screen flex-col items-center px-4 pb-10 pt-8 sm:justify-center sm:pt-0">
            <div class="mb-6">
                <a href="{{ route('home') }}" class="group inline-flex flex-col items-center gap-2">
                    <x-application-logo class="h-16 w-16 transition-transform group-hover:scale-105" />
                    <span class="text-sm font-semibold tracking-wide text-bcom-navy">{{ config('app.name', 'Booking') }}</span>
                </a>
            </div>

            <div
                class="w-full overflow-hidden rounded-2xl border border-slate-200 bg-white px-8 py-8 shadow-xl shadow-slate-900/10 sm:max-w-md sm:px-10 sm:py-10"
            >
                {{ $slot }}
            </div>

            <nav class="mt-8 flex max-w-md flex-wrap items-center justify-center gap-x-3 gap-y-2 text-center text-xs text-slate-600 sm:text-sm" aria-label="{{ __('Thông tin pháp lý') }}">
                <a href="{{ route('legal.cancellation-refunds') }}" class="text-bcom-blue hover:underline">{{ __('Hủy & hoàn tiền') }}</a>
                <span class="text-slate-300" aria-hidden="true">|</span>
                <a href="{{ route('legal.privacy') }}" class="text-bcom-blue hover:underline">{{ __('Quyền riêng tư') }}</a>
                <span class="text-slate-300" aria-hidden="true">|</span>
                <a href="{{ route('legal.terms') }}" class="text-bcom-blue hover:underline">{{ __('Điều khoản') }}</a>
            </nav>
        </div>
    </body>
</html>
