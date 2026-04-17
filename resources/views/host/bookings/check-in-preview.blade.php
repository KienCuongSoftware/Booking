<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-bcom-navy leading-tight">
            {{ __('Xác nhận check-in từ QR') }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl space-y-5">
            <x-flash-status />

            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Mã đơn') }}</p>
                        <p class="mt-1 text-xl font-bold text-bcom-navy">{{ $booking->booking_code }}</p>
                        <p class="mt-1 text-sm text-gray-600">{{ $booking->hotel->name }} — {{ $booking->roomType->name }}</p>
                    </div>
                    <span class="inline-flex rounded-full border px-2.5 py-1 text-xs {{ $booking->checked_in_at ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-sky-200 bg-sky-50 text-bcom-blue' }}">
                        {{ $booking->checked_in_at ? __('Đã check-in') : __('Chưa check-in') }}
                    </span>
                </div>

                <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">{{ __('Khách') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $booking->customer->name }} ({{ $booking->customer->email }})</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">{{ __('Lưu trú') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $booking->check_in_date->format('d/m/Y') }} → {{ $booking->check_out_date->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">{{ __('Số khách') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $booking->guest_count }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase text-gray-500">{{ __('Trạng thái đơn') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $booking->status->labelVi() }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                @if ($eligibilityError)
                    @if ($booking->checked_in_at)
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                            {{ __('Đã check-in thành công cho khách.') }}
                        </div>
                    @else
                        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                            {{ $eligibilityError }}
                        </div>
                    @endif
                @else
                    <p class="text-sm text-gray-700">{{ __('Thông tin hợp lệ. Xác nhận để hoàn tất check-in cho khách.') }}</p>
                    <form method="POST" action="{{ route('host.bookings.check-in.confirm') }}" class="mt-4">
                        @csrf
                        <input type="hidden" name="payload" value="{{ $payload }}">
                        <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                            {{ __('Xác nhận check-in') }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
