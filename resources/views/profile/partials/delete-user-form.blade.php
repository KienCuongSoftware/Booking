<section class="space-y-6">
    <header>
        <h2 class="text-lg font-semibold text-gray-900">
            {{ __('Xóa tài khoản') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Khi tài khoản bị xóa, toàn bộ dữ liệu liên quan sẽ bị xóa vĩnh viễn. Hãy sao lưu dữ liệu cần thiết trước khi tiếp tục.') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Xóa tài khoản') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-8">
            @csrf
            @method('delete')

            <h2 class="text-lg font-semibold text-gray-900">
                {{ __('Bạn chắc chắn muốn xóa tài khoản?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                {{ __('Hành động này không thể hoàn tác. Vui lòng nhập mật khẩu để xác nhận xóa vĩnh viễn tài khoản.') }}
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Mật khẩu') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full max-w-md"
                    placeholder="{{ __('Mật khẩu') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3 flex-wrap">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Hủy') }}
                </x-secondary-button>

                <x-danger-button>
                    {{ __('Xóa tài khoản') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
