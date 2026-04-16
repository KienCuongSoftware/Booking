<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-bcom-navy">
            {{ __('Đăng ký chờ — :name', ['name' => $hotel->name]) }}
        </h2>
    </x-slot>

    <div class="min-w-0 px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-xl min-w-0 space-y-6">
            <x-flash-status />

            <p class="text-sm text-gray-600">
                {{ __('Bạn sẽ nhận email khi có chỗ trống cho loại phòng và khoảng ngày đã chọn.') }}
            </p>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-900/5">
                <form method="POST" action="{{ route('customer.waitlist.store', $hotel) }}" class="grid gap-4">
                    @csrf
                    <div>
                        <x-input-label for="room_type_id" :value="__('Loại phòng')" />
                        <select id="room_type_id" name="room_type_id" class="mt-1 block w-full rounded-xl border-gray-200 text-sm focus:border-bcom-blue focus:ring-bcom-blue/20" required>
                            <option value="">{{ __('Chọn loại phòng') }}</option>
                            @foreach ($hotel->roomTypes as $rt)
                                <option value="{{ $rt->id }}" @selected(old('room_type_id', request('room_type_id')) == $rt->id)>
                                    {{ $rt->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('room_type_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="check_in_date" :value="__('Nhận phòng')" />
                        <x-text-input id="check_in_date" type="date" name="check_in_date" class="mt-1 block w-full" :value="old('check_in_date', request('check_in_date'))" required />
                        <x-input-error :messages="$errors->get('check_in_date')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="check_out_date" :value="__('Trả phòng')" />
                        <x-text-input id="check_out_date" type="date" name="check_out_date" class="mt-1 block w-full" :value="old('check_out_date', request('check_out_date'))" required />
                        <x-input-error :messages="$errors->get('check_out_date')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="guest_count" :value="__('Số khách')" />
                        <x-text-input id="guest_count" type="number" min="1" max="10" name="guest_count" class="mt-1 block w-full" :value="old('guest_count', request('guest_count', 1))" required />
                        <x-input-error :messages="$errors->get('guest_count')" class="mt-2" />
                    </div>

                    <div class="flex flex-wrap justify-between gap-3 pt-2">
                        <a href="{{ route('public.hotels.show', $hotel) }}" class="text-sm font-medium text-gray-600 hover:text-bcom-blue">
                            ← {{ __('Quay lại khách sạn') }}
                        </a>
                        <x-primary-button>{{ __('Lưu đăng ký chờ') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
