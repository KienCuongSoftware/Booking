<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600 leading-relaxed">
        {{ __('Cảm ơn bạn đã đăng ký! Trước khi bắt đầu, vui lòng xác minh email bằng liên kết chúng tôi vừa gửi. Nếu chưa nhận được, bạn có thể yêu cầu gửi lại.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-bcom-navy bg-sky-50 border border-slate-200 rounded-xl px-4 py-3">
            {{ __('Liên kết xác minh mới đã được gửi tới email bạn đã đăng ký.') }}
        </div>
    @endif

    <div class="mt-6 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Gửi lại email xác minh') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="text-sm font-medium text-gray-600 hover:text-bcom-blue rounded-lg px-2 py-1 focus:outline-none focus:ring-2 focus:ring-bcom-blue/30">
                {{ __('Đăng xuất') }}
            </button>
        </form>
    </div>
</x-guest-layout>
