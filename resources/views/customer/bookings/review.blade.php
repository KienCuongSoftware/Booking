<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-bcom-navy">
            {{ __('Đánh giá chuyến ở') }}
        </h2>
    </x-slot>

    <div class="min-w-0 px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-xl min-w-0 space-y-6">
            <x-flash-status />

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-md shadow-slate-900/5">
                <p class="text-sm font-semibold text-bcom-navy">{{ $booking->hotel->name }}</p>
                <p class="mt-1 text-sm text-gray-700">{{ $booking->roomType->name }}</p>
                <p class="mt-2 text-xs text-gray-500">
                    {{ $booking->check_in_date->format('d/m/Y') }} → {{ $booking->check_out_date->format('d/m/Y') }}
                    · {{ $booking->booking_code }}
                </p>

                <form method="POST" action="{{ route('customer.bookings.review.store', $booking) }}" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="rating" :value="__('Số sao (1–5)')" />
                        <select id="rating" name="rating" class="mt-1 block w-full rounded-xl border-gray-200 text-sm focus:border-bcom-blue focus:ring-bcom-blue/20" required>
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" @selected((int) old('rating', 5) === $i)>{{ $i }} ★</option>
                            @endfor
                        </select>
                        <x-input-error :messages="$errors->get('rating')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="comment" :value="__('Nhận xét (tuỳ chọn)')" />
                        <textarea id="comment" name="comment" rows="4" class="mt-1 block w-full rounded-xl border-gray-200 text-sm focus:border-bcom-blue focus:ring-bcom-blue/20" placeholder="{{ __('Trải nghiệm phòng, dịch vụ…') }}">{{ old('comment') }}</textarea>
                        <x-input-error :messages="$errors->get('comment')" class="mt-2" />
                    </div>

                    <div class="flex flex-wrap justify-between gap-3 pt-2">
                        <a href="{{ route('customer.bookings.index') }}" class="text-sm font-medium text-gray-600 hover:text-bcom-blue">
                            ← {{ __('Quay lại danh sách đơn') }}
                        </a>
                        <x-primary-button>{{ __('Gửi đánh giá') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
