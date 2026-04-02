<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-red-800 leading-tight">
            {{ __('Xác minh OTP đổi mật khẩu') }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md mx-auto bg-white border border-red-100 rounded-2xl shadow-md shadow-red-900/5 p-8">
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <p class="text-sm text-gray-600 mb-2">{{ __('Email') }}: <span class="font-medium text-gray-900">{{ $email }}</span></p>
            <p class="text-sm text-gray-600 mb-6 leading-relaxed">
                {{ __('Nhập mã 6 số đã gửi tới email để xác nhận mật khẩu mới.') }}
            </p>

            <form method="POST" action="{{ route('password.otp.verify') }}">
                @csrf

                <div>
                    <x-input-label for="code" :value="__('Mã OTP')" />
                    <x-text-input id="code" class="block mt-1 w-full tracking-widest text-center text-lg font-mono" type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autofocus autocomplete="one-time-code" />
                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                </div>

                <div class="mt-6 flex flex-col gap-4">
                    <x-primary-button class="justify-center">
                        {{ __('Xác nhận đổi mật khẩu') }}
                    </x-primary-button>
                </div>
            </form>

            <form method="POST" action="{{ route('password.otp.resend') }}" class="mt-6 text-center">
                @csrf
                <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">
                    {{ __('Gửi lại mã OTP') }}
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
