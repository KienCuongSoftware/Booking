<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-bcom-navy">{{ __('Đánh giá của tôi') }}</h2>
    </x-slot>

    <div class="min-w-0 px-4 py-10 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-4xl space-y-6">
            <x-flash-status />

            @if ($reviews->isEmpty())
                <x-customer.empty-state
                    :title="__('Chưa có đánh giá nào')"
                    :description="__('Sau khi hoàn tất lưu trú, bạn có thể đánh giá từ trang đơn đặt của mình.')" />
            @else
                <ul class="space-y-4">
                    @foreach ($reviews as $review)
                        @php $b = $review->booking; @endphp
                        <li class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-bcom-navy">{{ $b?->hotel?->name ?? __('Khách sạn') }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $b?->booking_code }} · {{ $b?->roomType?->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $b?->check_in_date?->translatedFormat('d/m/Y') }} — {{ $b?->check_out_date?->translatedFormat('d/m/Y') }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-amber-600" aria-label="{{ __('Điểm') }}">★ {{ $review->rating }}/5</p>
                                </div>
                            </div>
                            @if ($review->comment)
                                <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $review->comment }}</p>
                            @endif
                            <p class="mt-3 text-xs text-slate-400">{{ __('Gửi lúc') }} {{ $review->created_at?->translatedFormat('d/m/Y H:i') }}</p>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-6">{{ $reviews->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
