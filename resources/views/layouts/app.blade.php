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
    <body class="min-h-screen bg-slate-100 font-sans antialiased text-gray-900">
        <div class="flex min-h-screen">
            @include('layouts.sidebar')

            <div class="min-w-0 flex-1">
                <header class="border-b border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                        <div class="min-w-0">
                            @isset($header)
                                {{ $header }}
                            @else
                                <h2 class="text-xl font-semibold leading-tight text-bcom-navy">{{ __('Bảng điều khiển') }}</h2>
                            @endisset
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('profile.edit') }}" class="text-sm text-gray-600 hover:text-bcom-blue">{{ __('Hồ sơ') }}</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-sm text-gray-600 hover:text-bcom-blue">{{ __('Đăng xuất') }}</button>
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
