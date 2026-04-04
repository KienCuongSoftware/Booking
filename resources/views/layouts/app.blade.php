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
    <body class="font-sans antialiased text-gray-900 bg-red-50/30 min-h-screen">
        <div class="min-h-screen flex">
            @include('layouts.sidebar')

            <div class="flex-1 min-w-0">
                <header class="bg-white border-b border-red-100 shadow-sm">
                    <div class="px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            @isset($header)
                                {{ $header }}
                            @else
                                <h2 class="font-semibold text-xl text-red-800 leading-tight">{{ __('Bảng điều khiển') }}</h2>
                            @endisset
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('profile.edit') }}" class="text-sm text-gray-600 hover:text-red-700">{{ __('Hồ sơ') }}</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-sm text-gray-600 hover:text-red-700">{{ __('Đăng xuất') }}</button>
                            </form>
                        </div>
                    </div>
                </header>

                <main class="pb-10">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
