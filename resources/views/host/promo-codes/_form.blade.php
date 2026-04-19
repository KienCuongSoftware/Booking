@php
    $p = $promoCode ?? null;
    $isEdit = $p !== null;
@endphp
<form method="POST" action="{{ $isEdit ? route('host.promo-codes.update', $p) : route('host.promo-codes.store') }}" class="space-y-4">
    @csrf
    @if ($isEdit) @method('PUT') @endif
    <div>
        <x-input-label for="hotel_id" :value="__('Khách sạn')" />
        <select id="hotel_id" name="hotel_id" class="mt-1 block w-full rounded-xl border-gray-200 text-sm" required>
            @foreach ($hotels as $h)
                <option value="{{ $h->id }}" @selected(old('hotel_id', $p?->hotel_id) == $h->id)>{{ $h->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('hotel_id')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="room_type_id" :value="__('Loại phòng (tuỳ chọn)')" />
        <select id="room_type_id" name="room_type_id" class="mt-1 block w-full rounded-xl border-gray-200 text-sm">
            <option value="">{{ __('Tất cả loại phòng của khách sạn') }}</option>
            @foreach ($roomTypes as $rt)
                <option value="{{ $rt->id }}" data-hotel="{{ $rt->hotel_id }}" @selected(old('room_type_id', $p?->room_type_id) == $rt->id)>{{ $rt->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <x-input-label for="code" :value="__('Mã')" />
        <x-text-input id="code" name="code" class="mt-1 block w-full font-mono" :value="old('code', $p?->code)" required />
        <x-input-error :messages="$errors->get('code')" class="mt-2" />
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="valid_from" :value="__('Từ ngày')" />
            <x-text-input id="valid_from" name="valid_from" type="date" class="mt-1 block w-full" :value="old('valid_from', $p?->valid_from?->format('Y-m-d'))" required />
            <x-input-error :messages="$errors->get('valid_from')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="valid_to" :value="__('Đến ngày')" />
            <x-text-input id="valid_to" name="valid_to" type="date" class="mt-1 block w-full" :value="old('valid_to', $p?->valid_to?->format('Y-m-d'))" required />
            <x-input-error :messages="$errors->get('valid_to')" class="mt-2" />
        </div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="discount_type" :value="__('Kiểu giảm')" />
            <select id="discount_type" name="discount_type" class="mt-1 block w-full rounded-xl border-gray-200 text-sm" required>
                <option value="percent" @selected(old('discount_type', $p?->discount_type ?? 'percent') === 'percent')>{{ __('Phần trăm') }}</option>
                <option value="fixed" @selected(old('discount_type', $p?->discount_type ?? '') === 'fixed')>{{ __('Số tiền cố định') }}</option>
            </select>
        </div>
        <div>
            <x-input-label for="discount_value" :value="__('Giá trị')" />
            <x-text-input id="discount_value" name="discount_value" type="number" step="0.01" class="mt-1 block w-full" :value="old('discount_value', $p?->discount_value)" required />
            <x-input-error :messages="$errors->get('discount_value')" class="mt-2" />
        </div>
    </div>
    <div>
        <x-input-label for="max_uses" :value="__('Giới hạn lượt (để trống = không giới hạn)')" />
        <x-text-input id="max_uses" name="max_uses" type="number" class="mt-1 block w-full" :value="old('max_uses', $p?->max_uses)" />
    </div>
    <div>
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300" @checked(old('is_active', $p?->is_active ?? true) ? true : false)>
            {{ __('Đang kích hoạt') }}
        </label>
    </div>
    <x-primary-button>{{ __('Lưu') }}</x-primary-button>
</form>
