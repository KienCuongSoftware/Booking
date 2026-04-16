<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Mẫu email') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl space-y-6 sm:px-6 lg:px-8">
            <p class="text-sm text-gray-600">{{ __('Chọn khách sạn để chỉnh sửa nội dung email gửi cho khách và cho bạn.') }}</p>

            @if ($hotels->isEmpty())
                <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
                    <p class="text-sm text-gray-600">{{ __('Bạn chưa có khách sạn nào.') }}</p>
                    <a href="{{ route('host.hotels.create') }}" class="mt-4 inline-block text-sm font-medium text-bcom-blue hover:underline">
                        {{ __('Tạo khách sạn') }}
                    </a>
                </div>
            @else
                <ul class="divide-y divide-slate-200 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    @foreach ($hotels as $hotel)
                        <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                            <span class="font-medium text-gray-900">{{ $hotel->name }}</span>
                            <a href="{{ route('host.hotels.email-templates.edit', $hotel) }}"
                                class="text-sm font-medium text-bcom-blue hover:underline">
                                {{ __('Chỉnh sửa mẫu') }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-app-layout>
