<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-bcom-navy">{{ __('Đơn đặt') }}</h2></x-slot>
    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-4">
            <form method="GET" class="flex flex-wrap gap-2">
                <select name="status" class="rounded-xl border-gray-200 text-sm">
                    <option value="">{{ __('Trạng thái') }}</option>
                    @foreach (\App\Enums\BookingStatus::cases() as $s)
                        <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->labelVi() }}</option>
                    @endforeach
                </select>
                <input type="text" name="q" value="{{ request('q') }}" class="rounded-xl border-gray-200 text-sm" placeholder="{{ __('Mã đơn / khách / KS') }}">
                <x-primary-button type="submit">{{ __('Lọc') }}</x-primary-button>
            </form>
            <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="border-b bg-sky-50/60 text-xs font-semibold uppercase text-bcom-navy">
                        <tr><th class="px-3 py-2">{{ __('Mã') }}</th><th class="px-3 py-2">{{ __('Khách') }}</th><th class="px-3 py-2">{{ __('KS') }}</th><th class="px-3 py-2">{{ __('Trạng thái') }}</th><th class="px-3 py-2">{{ __('Tổng') }}</th></tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($bookings as $b)
                            <tr>
                                <td class="px-3 py-2 font-mono text-xs">{{ $b->booking_code }}</td>
                                <td class="px-3 py-2">{{ $b->customer?->name }}</td>
                                <td class="px-3 py-2">{{ $b->hotel?->name }}</td>
                                <td class="px-3 py-2">{{ $b->status->labelVi() }}</td>
                                <td class="px-3 py-2">{{ number_format((float) $b->total_price, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $bookings->links() }}
        </div>
    </div>
</x-app-layout>
