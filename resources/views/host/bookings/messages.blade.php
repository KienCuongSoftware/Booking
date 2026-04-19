<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-xl font-semibold text-bcom-navy">{{ __('Tin nhắn') }} · {{ $booking->booking_code }}</h2>
            <a href="{{ route('host.bookings.index') }}" class="text-sm font-medium text-bcom-blue hover:underline">{{ __('← Đơn đặt') }}</a>
        </div>
    </x-slot>
    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl space-y-4">
            <x-flash-status />
            <p class="text-sm text-gray-600">{{ __('Khách') }}: <strong>{{ $booking->customer?->name }}</strong> ({{ $booking->customer?->email }})</p>
            <div class="space-y-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm max-h-[480px] overflow-y-auto">
                @forelse ($booking->messages as $msg)
                    <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-2 text-sm">
                        <p class="text-xs font-semibold text-gray-500">{{ $msg->sender?->name }} · {{ $msg->created_at?->format('d/m/Y H:i') }}</p>
                        <p class="mt-1 whitespace-pre-wrap text-gray-800">{{ $msg->body }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-600">{{ __('Chưa có tin nhắn.') }}</p>
                @endforelse
            </div>
            <form method="POST" action="{{ route('host.bookings.messages.store', $booking) }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm space-y-2">
                @csrf
                <textarea name="body" rows="3" class="w-full rounded-xl border-gray-200 text-sm" required placeholder="{{ __('Trả lời khách…') }}">{{ old('body') }}</textarea>
                <x-primary-button type="submit">{{ __('Gửi') }}</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>
