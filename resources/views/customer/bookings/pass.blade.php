<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Vé điện tử / Check-in') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-lg sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-gray-600">{{ $booking->hotel->name }} — {{ $booking->roomType->name }}</p>
                <p class="mt-1 text-lg font-semibold text-bcom-navy">{{ $booking->booking_code }}</p>
                <p class="mt-2 text-sm text-gray-700">
                    {{ $booking->check_in_date->format('d/m/Y') }} → {{ $booking->check_out_date->format('d/m/Y') }}
                </p>
                <div class="mt-6 flex flex-col items-center gap-3">
                    <img src="{{ $qrUrl }}" width="220" height="220" alt="QR" class="rounded-lg border border-slate-200 bg-white p-2">
                    <p class="text-center text-xs text-gray-500">{{ __('Chủ khách sạn quét mã để nhận đủ thông tin đơn và xác nhận check-in.') }}</p>
                    <p class="text-center text-[11px] text-gray-500">
                        {{ __('Mã QR mở trực tiếp trang xác nhận') }}:
                        <a href="{{ $checkInUrl }}" class="font-medium text-bcom-blue hover:underline">{{ __('Mở trang check-in') }}</a>
                    </p>
                    <details class="w-full rounded-lg border border-slate-200 bg-slate-50 p-3 text-xs text-gray-700">
                        <summary class="cursor-pointer font-medium text-bcom-navy">{{ __('Hiển thị payload QR (dự phòng)') }}</summary>
                        <pre class="mt-2 overflow-x-auto whitespace-pre-wrap break-all rounded bg-white p-2 text-[11px]">{{ $payload }}</pre>
                    </details>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
