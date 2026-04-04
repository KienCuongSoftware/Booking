<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <x-google-auth-button class="mb-6">
        {{ __('Đăng nhập bằng Google') }}
    </x-google-auth-button>

    <div class="relative mb-6">
        <div class="absolute inset-0 flex items-center">
            <span class="w-full border-t border-red-100"></span>
        </div>
        <div class="relative flex justify-center text-xs uppercase tracking-wide">
            <span class="bg-white px-3 text-gray-500">{{ __('Hoặc') }}</span>
        </div>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Mật khẩu')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded-md border-gray-300 text-red-600 shadow-sm focus:ring-red-500/40 focus:ring-2" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Ghi nhớ đăng nhập') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-6 gap-3 flex-wrap">
            @if (Route::has('password.request'))
                <a class="text-sm text-red-600 hover:text-red-800 font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500/30 px-1" href="{{ route('password.request') }}">
                    {{ __('Quên mật khẩu?') }}
                </a>
            @endif

            <x-primary-button>
                {{ __('Đăng nhập') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
