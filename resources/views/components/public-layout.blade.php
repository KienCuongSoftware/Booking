@props([
    'title' => null,
    'description' => null,
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @isset($description)
            <meta name="description" content="{{ $description }}">
        @endisset

        <title>{{ $title ? $title.' — ' : '' }}{{ config('app.name', 'Booking') }}</title>
        <link rel="icon" href="{{ asset('ico.svg') }}" type="image/svg+xml">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-100 font-sans antialiased text-gray-900">
        @auth
            @if (auth()->user()->role->value === 'customer')
                @include('layouts.customer-header')
            @else
                <header class="sticky top-0 z-40 border-b border-bcom-navy-dark/40 bg-bcom-navy shadow-md">
                    <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
                        <a href="{{ route('home') }}" class="flex items-center gap-2.5 rounded-lg text-white outline-none ring-white/40 focus-visible:ring-2">
                            <x-application-logo variant="light" class="h-9 w-9 shrink-0" />
                            <span class="text-lg font-semibold tracking-tight">{{ config('app.name', 'Booking') }}</span>
                        </a>
                        <nav class="flex flex-wrap items-center justify-end gap-1 text-sm sm:gap-2" aria-label="{{ __('Điều hướng chính') }}">
                            <a href="{{ route('home') }}" class="rounded-lg px-3 py-2 font-medium text-white/90 hover:bg-white/10 hover:text-white">{{ __('Khách sạn') }}</a>
                            <a href="{{ route(auth()->user()->role->dashboardRouteName()) }}" class="rounded-lg px-3 py-2 font-medium text-white/90 hover:bg-white/10 hover:text-white">{{ __('Bảng điều khiển') }}</a>
                            @include('layouts.app-header-user')
                        </nav>
                    </div>
                </header>
            @endif
        @else
            <header class="sticky top-0 z-40 border-b border-bcom-navy-dark/40 bg-bcom-navy shadow-md">
                <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 rounded-lg text-white outline-none ring-white/40 focus-visible:ring-2">
                        <x-application-logo variant="light" class="h-9 w-9 shrink-0" />
                        <span class="text-lg font-semibold tracking-tight">{{ config('app.name', 'Booking') }}</span>
                    </a>
                    <nav class="flex flex-wrap items-center justify-end gap-1 text-sm sm:gap-2" aria-label="{{ __('Điều hướng chính') }}">
                        <a href="{{ route('home') }}" class="rounded-lg px-3 py-2 font-medium text-white/90 hover:bg-white/10 hover:text-white">{{ __('Khách sạn') }}</a>
                        <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 font-medium text-white/90 hover:bg-white/10 hover:text-white">{{ __('Đăng nhập') }}</a>
                        <a href="{{ route('register') }}" class="inline-flex items-center rounded-md bg-bcom-yellow px-4 py-2 text-sm font-bold text-bcom-navy shadow-sm transition hover:brightness-95">{{ __('Đăng ký') }}</a>
                    </nav>
                </div>
            </header>
        @endauth

        <main>
            {{ $slot }}
        </main>

        <footer class="mt-auto border-t border-slate-200 bg-white py-8 text-center text-sm text-slate-600">
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'Booking') }}. {{ __('Đặt phòng theo ngày sẽ được bổ sung ở bước tiếp theo.') }}</p>
        </footer>
    </body>
</html>
