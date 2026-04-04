<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Booking') }}</title>
        <link rel="icon" href="{{ asset('ico.svg') }}" type="image/svg+xml">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gradient-to-br from-red-50 via-white to-white text-gray-900 min-h-screen flex flex-col">
        <header class="w-full max-w-5xl mx-auto px-6 py-6 flex justify-end">
            @if (Route::has('login'))
                <nav class="flex items-center gap-3 text-sm">
                    @auth
                        <a href="{{ route(auth()->user()->role->dashboardRouteName()) }}"
                            class="rounded-lg border border-red-200 bg-white px-4 py-2 font-medium text-red-800 hover:bg-red-50">
                            {{ __('Bảng điều khiển') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-red-700 hover:text-red-900 font-medium">{{ __('Đăng nhập') }}</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                                class="rounded-lg bg-red-600 px-4 py-2 font-medium text-white hover:bg-red-700">
                                {{ __('Đăng ký') }}
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </header>

        <main class="flex-1 flex items-center justify-center px-6 pb-16">
            <div class="max-w-xl w-full text-center space-y-8">
                <div class="flex justify-center">
                    <img src="{{ asset('ico.svg') }}" alt="{{ config('app.name', 'Booking') }}" class="h-24 w-24 rounded-2xl shadow-lg shadow-red-900/10" width="96" height="96">
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-red-900 tracking-tight">{{ config('app.name', 'Booking') }}</h1>
                    <p class="mt-3 text-gray-600 leading-relaxed">
                        {{ __('Nền tảng đặt phòng khách sạn — quản lý chỗ nghỉ, loại phòng và đơn đặt trong một hệ thống thống nhất.') }}
                    </p>
                </div>
                @guest
                    @if (Route::has('login'))
                        <div class="flex flex-wrap items-center justify-center gap-3">
                            <a href="{{ route('login') }}"
                                class="inline-flex items-center justify-center rounded-xl bg-red-600 px-6 py-3 text-sm font-semibold text-white hover:bg-red-700">
                                {{ __('Bắt đầu') }}
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}"
                                    class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-6 py-3 text-sm font-semibold text-red-800 hover:bg-red-50">
                                    {{ __('Tạo tài khoản') }}
                                </a>
                            @endif
                        </div>
                    @endif
                @endguest
            </div>
        </main>
    </body>
</html>
