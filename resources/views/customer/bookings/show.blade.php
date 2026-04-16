<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold leading-tight text-bcom-navy">
                {{ __('Chi tiết đơn') }} · {{ $booking->booking_code }}
            </h2>
            <a href="{{ route('customer.bookings.index') }}" class="text-sm font-medium text-bcom-blue hover:text-bcom-navy">{{ __('← Danh sách đơn') }}</a>
        </div>
    </x-slot>

    <div class="min-w-0 px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl min-w-0 space-y-6">
            <x-flash-status />

            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-sky-50/60 px-5 py-4">
                    <p class="text-sm text-gray-600">{{ __('Khách sạn') }}</p>
                    <p class="text-lg font-semibold text-bcom-navy">{{ $booking->hotel->name }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ $booking->hotel->city }} — {{ $booking->hotel->address }}</p>
                    <a href="{{ route('public.hotels.show', $booking->hotel) }}" class="mt-2 inline-block text-sm font-medium text-bcom-blue hover:underline">{{ __('Xem trang khách sạn') }}</a>
                </div>
                <div class="space-y-4 p-5 text-sm text-gray-800">
                    <div class="flex flex-wrap gap-4">
                        <div>
                            <p class="text-xs font-medium uppercase text-gray-500">{{ __('Loại phòng') }}</p>
                            <p class="font-medium">{{ $booking->roomType->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase text-gray-500">{{ __('Nhận / Trả') }}</p>
                            <p>{{ $booking->check_in_date->format('d/m/Y') }} → {{ $booking->check_out_date->format('d/m/Y') }}</p>
                            <p class="text-xs text-gray-500">{{ $booking->nights }} {{ __('đêm') }} · {{ $booking->guest_count }} {{ __('khách') }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-4 border-t border-slate-100 pt-4">
                        <div>
                            <p class="text-xs font-medium uppercase text-gray-500">{{ __('Trạng thái đơn') }}</p>
                            <span class="mt-1 inline-flex rounded-full border border-sky-200 bg-sky-50 px-2 py-0.5 text-xs font-medium text-bcom-blue">{{ $booking->status->labelVi() }}</span>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase text-gray-500">{{ __('Thanh toán') }}</p>
                            <p>{{ $booking->payment_method->labelVi() }}</p>
                            <p class="text-xs text-gray-500">{{ $booking->payment_provider?->labelVi() ?? '—' }} · {{ $booking->payment_status->labelVi() }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium uppercase text-gray-500">{{ __('Tổng cộng') }}</p>
                            <p class="text-lg font-bold text-bcom-blue">{{ number_format((float) $booking->total_price, 0, ',', '.') }} {{ $booking->currency }}</p>
                        </div>
                    </div>
                    @if ($booking->discount_amount && (float) $booking->discount_amount > 0)
                        <p class="text-xs text-emerald-800">{{ __('Đã giảm') }}: {{ number_format((float) $booking->discount_amount, 0, ',', '.') }} {{ $booking->currency }}
                            @if ($booking->promoCode)
                                ({{ $booking->promoCode->code }})
                            @endif
                        </p>
                    @endif
                    @if ($booking->hold_expires_at && $booking->status === \App\Enums\BookingStatus::Pending)
                        <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                            {{ __('Giữ chỗ / hoàn tất thanh toán trước') }}:
                            <strong>{{ $booking->hold_expires_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</strong>
                        </p>
                    @endif
                    @if ($booking->customer_note)
                        <div>
                            <p class="text-xs font-medium uppercase text-gray-500">{{ __('Ghi chú của bạn') }}</p>
                            <p class="mt-1 whitespace-pre-line text-gray-700">{{ $booking->customer_note }}</p>
                        </div>
                    @endif
                </div>
            </div>

            @if ($booking->isPayPalCheckoutPending())
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-semibold text-bcom-navy">{{ __('Thanh toán PayPal') }}</h3>
                    <p class="mt-1 text-xs text-gray-600">{{ __('Bạn sẽ được chuyển tới PayPal để thanh toán an toàn.') }}</p>
                    <a href="{{ route('customer.bookings.pay.paypal.resume', $booking) }}"
                        class="mt-4 inline-flex items-center rounded-xl bg-bcom-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-bcom-blue/90">
                        {{ __('Tiếp tục thanh toán PayPal') }}
                    </a>
                </div>
            @endif

            @if ($booking->isBankTransferAwaitingReference())
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-sm font-semibold text-bcom-navy">{{ __('Chuyển khoản / MoMo') }}</h3>
                    <p class="mt-1 text-xs text-gray-600">{{ __('Sau khi chuyển khoản, nhập mã giao dịch để chủ khách sạn đối soát nhanh hơn.') }}</p>
                    <form method="POST" action="{{ route('customer.bookings.payment-reference.update', $booking) }}" class="mt-4 space-y-3">
                        @csrf
                        @method('PATCH')
                        <div>
                            <x-input-label for="payment_reference" :value="__('Mã giao dịch')" />
                            <x-text-input id="payment_reference" name="payment_reference" type="text" class="mt-1 block w-full" :value="old('payment_reference', $booking->payment_reference)" required />
                            <x-input-error :messages="$errors->get('payment_reference')" class="mt-2" />
                        </div>
                        <x-primary-button>{{ __('Lưu mã giao dịch') }}</x-primary-button>
                    </form>
                </div>
            @endif

            @if ($cancellationPreview && in_array($booking->status, [\App\Enums\BookingStatus::Pending, \App\Enums\BookingStatus::Confirmed], true))
                <div class="rounded-2xl border border-amber-200 bg-amber-50/80 p-5 text-sm text-amber-950">
                    <p class="font-semibold">{{ __('Nếu hủy đơn (ước tính)') }}</p>
                    <p class="mt-2 text-xs">
                        {{ __('Phí hủy') }}:
                        {{ number_format((float) $cancellationPreview['fee_amount'], 0, ',', '.') }} {{ $booking->currency }}
                        ({{ rtrim(rtrim(number_format((float) $cancellationPreview['fee_percent'], 2, '.', ''), '0'), '.') }}%)
                    </p>
                    <p class="text-xs">{{ __('Hoàn dự kiến') }}: {{ number_format((float) $cancellationPreview['refund_amount'], 0, ',', '.') }} {{ $booking->currency }}</p>
                    <a href="{{ route('customer.bookings.cancellable') }}" class="mt-3 inline-block text-sm font-semibold text-bcom-blue hover:underline">{{ __('Đi tới trang hủy đơn') }}</a>
                </div>
            @endif

            <div class="flex flex-wrap gap-3">
                @if (in_array($booking->status->value, ['confirmed', 'completed'], true))
                    <a href="{{ route('customer.bookings.pass', $booking) }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 hover:bg-gray-50">
                        {{ __('Vé / QR check-in') }}
                    </a>
                @endif
                @if ($booking->status === \App\Enums\BookingStatus::Completed && ! $booking->review)
                    <a href="{{ route('customer.bookings.review.create', $booking) }}" class="inline-flex items-center rounded-xl bg-bcom-blue px-4 py-2.5 text-sm font-semibold text-white hover:bg-bcom-blue/90">
                        {{ __('Đánh giá kỳ nghỉ') }}
                    </a>
                @endif
                @if ($booking->review)
                    <p class="self-center text-sm text-gray-600">{{ __('Đánh giá') }}: {{ $booking->review->rating }}★ @if ($booking->review->comment) — {{ \Illuminate\Support\Str::limit($booking->review->comment, 80) }} @endif</p>
                @endif
            </div>

            @if ($booking->statusEvents->isNotEmpty())
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-200 bg-sky-50/60 px-5 py-3">
                        <h3 class="text-sm font-semibold text-bcom-navy">{{ __('Lịch sử trạng thái') }}</h3>
                    </div>
                    <ul class="divide-y divide-slate-100 px-5 py-2 text-sm">
                        @foreach ($booking->statusEvents as $event)
                            <li class="py-2 text-gray-700">
                                <span class="text-xs text-gray-500">{{ \Illuminate\Support\Carbon::parse($event->changed_at)->format('d/m/Y H:i') }}</span>
                                @if ($event->from_status)
                                    · {{ \App\Enums\BookingStatus::from($event->from_status)->labelVi() }}
                                    →
                                @endif
                                {{ \App\Enums\BookingStatus::from($event->to_status)->labelVi() }}
                                · {{ $event->actor?->name ?? __('Hệ thống') }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
