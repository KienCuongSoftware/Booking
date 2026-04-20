<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-bcom-navy">{{ __('Khách sạn') }}</h2></x-slot>
    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl space-y-4">
            <div id="adminHotelsFilterRoot" class="space-y-4">
                <form method="GET" data-ajax-filter-form data-ajax-target="#adminHotelsFilterRoot"><input type="text" name="q" value="{{ request('q') }}" placeholder="{{ __('Tìm…') }}" class="rounded-xl border-gray-200 text-sm"> <x-primary-button type="submit">{{ __('Lọc') }}</x-primary-button></form>
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b bg-sky-50/60 text-xs font-semibold uppercase text-bcom-navy">
                            <tr><th class="px-4 py-3">{{ __('Tên') }}</th><th class="px-4 py-3">{{ __('Chủ') }}</th><th class="px-4 py-3">{{ __('Thành phố') }}</th><th class="px-4 py-3"></th></tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($hotels as $hotel)
                                <tr>
                                    <td class="px-4 py-3 font-medium">{{ $hotel->name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $hotel->host?->name }}</td>
                                    <td class="px-4 py-3">{{ $hotel->city }}</td>
                                    <td class="px-4 py-3"><a href="{{ route('admin.hotels.show', $hotel) }}" class="font-semibold text-bcom-blue hover:underline">{{ __('Chi tiết') }}</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $hotels->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
