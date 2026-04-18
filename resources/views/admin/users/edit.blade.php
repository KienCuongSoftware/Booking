<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-bcom-navy">{{ __('Sửa người dùng') }}</h2>
    </x-slot>
    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <x-input-label for="name" :value="__('Tên')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="role" :value="__('Vai trò')" />
                    <select id="role" name="role" class="mt-1 block w-full rounded-xl border-gray-200 text-sm">
                        @foreach ($roles as $r)
                            <option value="{{ $r->value }}" @selected(old('role', $user->role->value) === $r->value)>{{ $r->shortLabelVi() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="is_active" :value="__('Tài khoản hoạt động')" />
                    <select id="is_active" name="is_active" class="mt-1 block w-full rounded-xl border-gray-200 text-sm">
                        <option value="1" @selected(old('is_active', $user->is_active ? '1' : '0') == '1')>{{ __('Có') }}</option>
                        <option value="0" @selected(old('is_active', $user->is_active ? '1' : '0') == '0')>{{ __('Không (khoá đăng nhập)') }}</option>
                    </select>
                    <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
                </div>
                <div class="flex gap-2">
                    <x-primary-button>{{ __('Lưu') }}</x-primary-button>
                    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">{{ __('Huỷ') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
