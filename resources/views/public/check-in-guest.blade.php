<x-public-layout
    :title="$valid ? __('Thông tin check-in') : __('Nhận phòng')"
    :description="__('Xác minh đơn lưu trú tại quầy lễ tân.')"
>
    <div class="mx-auto max-w-lg px-4 py-12 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            @if (! $valid)
                <h1 class="text-xl font-semibold text-bcom-navy">{{ __('Không tìm thấy thông tin hợp lệ') }}</h1>
                <p class="mt-3 text-sm text-slate-600">
                    {{ __('Liên kết có thể đã hết hạn hoặc không đúng. Vui lòng mở lại mã QR từ ứng dụng / email đặt phòng, hoặc nhờ chủ khách sạn gửi lại.') }}
                </p>
            @else
                <h1 class="text-xl font-semibold text-bcom-navy">{{ __('Đơn lưu trú') }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('Đưa màn hình này cho lễ tân / chủ khách sạn để đối chiếu.') }}</p>

                <dl class="mt-6 space-y-3 text-sm">
                    <div>
                        <dt class="font-medium text-bcom-navy">{{ __('Khách sạn') }}</dt>
                        <dd class="text-slate-800">{{ $booking->hotel->name }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-bcom-navy">{{ __('Loại phòng') }}</dt>
                        <dd class="text-slate-800">{{ $booking->roomType->name }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-bcom-navy">{{ __('Mã đơn') }}</dt>
                        <dd class="font-mono text-slate-800">{{ $booking->booking_code }}</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-bcom-navy">{{ __('Nhận / Trả phòng') }}</dt>
                        <dd class="text-slate-800">
                            {{ $booking->check_in_date->format('d/m/Y') }} → {{ $booking->check_out_date->format('d/m/Y') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-bcom-navy">{{ __('Số khách') }}</dt>
                        <dd class="text-slate-800">{{ $booking->guest_count }}</dd>
                    </div>
                </dl>

                <p class="mt-6 rounded-lg border border-sky-200 bg-sky-50/80 p-3 text-xs text-sky-950">
                    {{ __('Chủ khách sạn đăng nhập tài khoản host và quét cùng mã QR để xác nhận check-in trong hệ thống.') }}
                </p>
            @endif
        </div>
    </div>
</x-public-layout>
