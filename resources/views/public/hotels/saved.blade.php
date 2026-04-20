<x-public-layout
    :title="__('Danh sách xem sau')"
    :description="__('Các khách sạn bạn đã lưu để xem lại sau.')"
    :og-title="$ogTitle ?? null"
    :og-description="$ogDescription ?? null"
    :og-image="$ogImage ?? null"
    :canonical-url="$canonicalUrl ?? null"
>
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-bcom-navy">{{ __('Khách sạn đã lưu') }}</h1>
                <p class="mt-1 text-sm text-slate-600">{{ __('Danh sách xem sau lưu trong trình duyệt hiện tại.') }}</p>
            </div>
            <a href="{{ route('home') }}" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-bcom-blue hover:bg-sky-50">
                {{ __('← Quay lại catalog') }}
            </a>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                {{ session('status') }}
            </div>
        @endif

        @if ($hotels->isEmpty())
            <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-sm">
                <p class="text-slate-700">{{ __('Bạn chưa lưu khách sạn nào.') }}</p>
                <a href="{{ route('home') }}" class="mt-3 inline-block text-sm font-semibold text-bcom-blue hover:underline">{{ __('Khám phá ngay') }}</a>
            </div>
        @else
            <ul class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($hotels as $hotel)
                    <li class="flex min-h-0">
                        <article class="flex w-full min-w-0 flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm transition hover:border-slate-300 hover:shadow-md">
                            <a href="{{ route('public.hotels.show', $hotel) }}" class="relative block aspect-[4/3] w-full shrink-0 overflow-hidden bg-slate-200">
                                <img src="{{ $hotel->thumbnailUrl() }}" alt=""
                                    class="h-full w-full object-cover object-center transition duration-300 ease-out hover:scale-[1.02]"
                                    loading="lazy" decoding="async">
                            </a>
                            <div class="flex min-h-0 flex-1 flex-col p-3">
                                <div class="flex items-start justify-between gap-2">
                                    <h2 class="line-clamp-2 text-base font-semibold leading-snug text-slate-900">
                                        <a href="{{ route('public.hotels.show', $hotel) }}" class="hover:text-bcom-blue">{{ $hotel->name }}</a>
                                    </h2>
                                    <form method="POST" action="{{ route('public.hotels.saved.toggle', $hotel) }}">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="inline-flex shrink-0 items-center rounded-lg border border-amber-300 bg-amber-50 px-2 py-1 text-[11px] font-semibold text-amber-900 hover:bg-amber-100"
                                            title="{{ __('Bỏ khỏi xem sau') }}"
                                        >
                                            ★ {{ __('Đã lưu') }}
                                        </button>
                                    </form>
                                </div>
                                <p class="mt-1 line-clamp-2 text-xs leading-relaxed text-slate-600">
                                    {{ $hotel->province ? $hotel->province->type.' '.$hotel->province->name : $hotel->city }}
                                    — {{ $hotel->address }}
                                </p>
                                @if ($hotel->reviews_avg_rating)
                                    <p class="mt-1 text-xs font-semibold text-amber-800">
                                        ★ {{ number_format((float) $hotel->reviews_avg_rating, 1) }}
                                    </p>
                                @endif
                                <div class="mt-auto flex flex-wrap items-baseline gap-x-2 gap-y-0 border-t border-slate-100 pt-2.5">
                                    @if ($hotel->old_price)
                                        <span class="text-xs text-slate-400 line-through">{{ number_format((float) $hotel->old_price, 0, ',', '.') }}</span>
                                    @endif
                                    <span class="text-sm font-bold text-bcom-blue">
                                        {{ number_format((float) ($hotel->new_price ?? $hotel->base_price), 0, ',', '.') }} VND
                                    </span>
                                    <span class="text-xs text-slate-500">/ {{ __('đêm') }}</span>
                                </div>
                                <a href="{{ route('public.hotels.show', $hotel) }}" class="mt-2 inline-flex text-sm font-semibold text-bcom-blue hover:text-bcom-navy">
                                    {{ __('Xem chi tiết') }} →
                                </a>
                            </div>
                        </article>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-public-layout>

