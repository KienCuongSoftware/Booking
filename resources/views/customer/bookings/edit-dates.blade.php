<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-bcom-navy">{{ __('Đổi ngày lưu trú') }} · {{ $booking->booking_code }}</h2>
    </x-slot>
    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <x-flash-status />
            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
                    @foreach ($errors->all() as $e) <p>{{ $e }}</p> @endforeach
                </div>
            @endif
            <form method="POST" action="{{ route('customer.bookings.update-dates', $booking) }}" class="space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <x-input-label for="check_in_date" :value="__('Ngày nhận phòng')" />
                    <x-text-input id="check_in_date" name="check_in_date" type="date" class="mt-1 block w-full" :value="old('check_in_date', $booking->check_in_date->format('Y-m-d'))" required />
                </div>
                <div>
                    <x-input-label for="check_out_date" :value="__('Ngày trả phòng')" />
                    <x-text-input id="check_out_date" name="check_out_date" type="date" class="mt-1 block w-full" :value="old('check_out_date', $booking->check_out_date->format('Y-m-d'))" required />
                </div>
                <x-primary-button>{{ __('Cập nhật') }}</x-primary-button>
                <a href="{{ route('customer.bookings.show', $booking) }}" class="ml-2 text-sm text-bcom-blue hover:underline">{{ __('Huỷ') }}</a>
            </form>
        </div>
    </div>
</x-app-layout>
