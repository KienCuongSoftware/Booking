<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-bcom-navy">{{ __('Khách sạn yêu thích') }}</h2></x-slot>
    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl space-y-4">
            <x-flash-status />
            @if ($hotels->isEmpty())
                <p class="text-sm text-gray-600">{{ __('Chưa có khách sạn yêu thích.') }}</p>
            @else
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($hotels as $hotel)
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <a href="{{ route('public.hotels.show', $hotel) }}" class="text-lg font-semibold text-bcom-navy hover:underline">{{ $hotel->name }}</a>
                            <p class="mt-1 text-xs text-gray-600">{{ $hotel->city }}</p>
                            <form method="POST" action="{{ route('customer.favorites.toggle', $hotel) }}" class="mt-3">
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">{{ __('Bỏ yêu thích') }}</button>
                            </form>
                        </div>
                    @endforeach
                </div>
                {{ $hotels->links() }}
            @endif
        </div>
    </div>
</x-app-layout>
