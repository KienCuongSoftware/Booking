<x-guest-layout>
    <p class="text-sm text-gray-600 mb-2">{{ __('Email') }}: <span class="font-medium text-gray-900">{{ $email }}</span></p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <p class="text-sm text-gray-600 mb-6 leading-relaxed">
        {{ __('Nhập mã 6 số đã gửi tới email của bạn để hoàn tất đăng ký.') }}
    </p>

    <form method="POST" action="{{ route('register.verify.submit') }}">
        @csrf

        <div>
            <x-input-label for="code" :value="__('Mã OTP')" />
            <x-text-input id="code" class="block mt-1 w-full tracking-widest text-center text-lg font-mono" type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autofocus autocomplete="one-time-code" />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full sm:w-auto justify-center">
                {{ __('Xác minh') }}
            </x-primary-button>
        </div>
    </form>

    <form method="POST" action="{{ route('register.resend-otp') }}" class="mt-6 text-center">
        @csrf
        <button type="submit" class="text-sm font-medium text-bcom-blue hover:text-bcom-navy rounded-lg focus:outline-none focus:ring-2 focus:ring-bcom-blue/30 px-2 py-1">
            {{ __('Gửi lại mã OTP') }}
        </button>
    </form>
</x-guest-layout>
