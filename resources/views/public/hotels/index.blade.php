<x-public-layout :title="__('Khách sạn')" :description="__('Browse active hotels by province, name, and price.')">
    {{-- Hero (Booking-style navy band) --}}
    <div class="bg-bcom-navy pb-20 pt-10 text-white lg:pb-24 lg:pt-14">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-tight lg:text-4xl">{{ __('Tìm khách sạn') }}</h1>
            <p class="mt-2 max-w-2xl text-sm text-white/85 lg:text-base">{{ __('Chỉ hiển thị khách sạn đang mở. Đặt phòng theo ngày sẽ có ở bước sau.') }}</p>
        </div>
    </div>

    {{-- Search bar: white card + yellow ring --}}
    <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('home') }}"
            class="-mt-14 mb-10 flex flex-col gap-4 rounded-lg bg-white p-4 shadow-xl ring-2 ring-bcom-yellow ring-offset-2 ring-offset-slate-100 sm:flex-row sm:flex-wrap sm:items-end sm:p-5">
            <div class="min-w-0 flex-1 sm:min-w-[200px]">
                <x-input-label for="q" :value="__('Tìm theo tên hoặc địa chỉ')" class="text-gray-700" />
                <x-text-input id="q" name="q" type="search" class="mt-1 block w-full border-slate-300 focus:border-bcom-blue focus:ring-bcom-blue/25" :value="request('q')" placeholder="{{ __('VD: Hanoi, đường…') }}" />
            </div>
            <div class="w-full min-w-[180px] sm:max-w-[220px]">
                <x-input-label for="province_code" :value="__('Tỉnh / Thành phố')" class="text-gray-700" />
                <select id="province_code" name="province_code"
                    class="mt-1 block w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-bcom-blue focus:ring-2 focus:ring-bcom-blue/20">
                    <option value="">{{ __('— Tất cả —') }}</option>
                    @foreach ($provinces as $p)
                        <option value="{{ $p->code }}" @selected((string) request('province_code') === (string) $p->code)>
                            {{ $p->type }} {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="w-full min-w-[160px] sm:max-w-[200px]">
                <x-input-label for="sort" :value="__('Sắp xếp')" class="text-gray-700" />
                <select id="sort" name="sort"
                    class="mt-1 block w-full rounded-lg border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-bcom-blue focus:ring-2 focus:ring-bcom-blue/20">
                    <option value="newest" @selected(request('sort', 'newest') === 'newest')>{{ __('Mới nhất') }}</option>
                    <option value="price_asc" @selected(request('sort') === 'price_asc')>{{ __('Giá: thấp → cao') }}</option>
                    <option value="price_desc" @selected(request('sort') === 'price_desc')>{{ __('Giá: cao → thấp') }}</option>
                    <option value="name" @selected(request('sort') === 'name')>{{ __('Tên A–Z') }}</option>
                </select>
            </div>
            <div class="flex w-full gap-2 sm:w-auto sm:shrink-0">
                <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-md bg-bcom-blue px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-bcom-blue/90 sm:flex-none">
                    {{ __('Lọc') }}
                </button>
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    {{ __('Xóa lọc') }}
                </a>
            </div>
        </form>
    </div>

    <div class="mx-auto max-w-7xl px-4 pb-12 sm:px-6 lg:px-8">
        @if ($hotels->isEmpty())
            <div class="rounded-lg border border-slate-200 bg-white py-16 text-center shadow-sm">
                <p class="text-slate-700">{{ __('Không có khách sạn phù hợp. Thử bỏ bớt bộ lọc hoặc từ khóa.') }}</p>
            </div>
        @else
            <h2 class="mb-4 text-lg font-semibold text-bcom-navy">{{ __('Danh sách khách sạn') }}</h2>
            {{-- 4 cột desktop, ảnh cố định 4:3 — thẻ đồng đều, không kéo dọc quá lớn --}}
            <ul class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($hotels as $hotel)
                    <li class="flex min-h-0">
                        <article class="flex w-full min-w-0 flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm transition hover:border-slate-300 hover:shadow-md">
                            <a href="{{ route('public.hotels.show', $hotel) }}" class="relative block aspect-[4/3] w-full shrink-0 overflow-hidden bg-slate-200">
                                <img src="{{ $hotel->thumbnailUrl() }}" alt=""
                                    class="h-full w-full object-cover object-center transition duration-300 ease-out hover:scale-[1.02]"
                                    loading="lazy" decoding="async">
                                @if ($hotel->star_rating)
                                    <span class="absolute right-2 top-2 inline-flex rounded bg-white/95 px-2 py-0.5 text-xs font-bold text-bcom-navy shadow-sm">
                                        {{ $hotel->star_rating }}★
                                    </span>
                                @endif
                            </a>
                            <div class="flex min-h-0 flex-1 flex-col p-3">
                                <h2 class="line-clamp-2 text-base font-semibold leading-snug text-slate-900">
                                    <a href="{{ route('public.hotels.show', $hotel) }}" class="hover:text-bcom-blue">{{ $hotel->name }}</a>
                                </h2>
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

            <div class="mt-8">{{ $hotels->links() }}</div>
        @endif
    </div>
</x-public-layout>
