<x-guest-layout>
    <p class="text-sm text-gray-600 mb-4 leading-relaxed">
        {{ __('Sau khi gửi form, bạn sẽ nhận mã OTP qua email để hoàn tất đăng ký.') }}
    </p>

    <x-google-auth-button class="mb-6">
        {{ __('Đăng ký bằng Google') }}
    </x-google-auth-button>

    <div class="relative mb-6">
        <div class="absolute inset-0 flex items-center">
            <span class="w-full border-t border-red-100"></span>
        </div>
        <div class="relative flex justify-center text-xs uppercase tracking-wide">
            <span class="bg-white px-3 text-gray-500">{{ __('Hoặc đăng ký email') }}</span>
        </div>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Họ tên')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Mật khẩu')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Xác nhận mật khẩu')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6 gap-3 flex-wrap">
            <a class="text-sm text-red-600 hover:text-red-800 font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500/30 px-1" href="{{ route('login') }}">
                {{ __('Đã có tài khoản?') }}
            </a>

            <x-primary-button>
                {{ __('Đăng ký') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
