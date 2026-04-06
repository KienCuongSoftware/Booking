<section>
    <header>
        <h2 class="text-lg font-semibold text-gray-900">
            {{ __('Thông tin hồ sơ') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Cập nhật thông tin hồ sơ và địa chỉ email của tài khoản.') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Họ tên')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Địa chỉ email của bạn chưa được xác minh.') }}

                        <button form="send-verification" type="submit" class="font-medium text-bcom-blue hover:text-bcom-navy ms-1 rounded-lg focus:outline-none focus:ring-2 focus:ring-bcom-blue/30">
                            {{ __('Bấm vào đây để gửi lại email xác minh.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-bcom-navy bg-sky-50 border border-slate-200 rounded-xl px-3 py-2 inline-block">
                            {{ __('Liên kết xác minh mới đã được gửi tới email của bạn.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Lưu') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-bcom-blue font-medium"
                >{{ __('Đã lưu.') }}</p>
            @endif
        </div>
    </form>
</section>
