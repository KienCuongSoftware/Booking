<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-bcom-navy">{{ __('Mã giảm giá') }}</h2></x-slot>
    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl space-y-4">
            <x-flash-status />
            <a href="{{ route('host.promo-codes.create') }}" class="inline-flex rounded-xl bg-bcom-blue px-4 py-2 text-sm font-semibold text-white hover:bg-bcom-blue/90">{{ __('Tạo mã') }}</a>
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="w-full text-left text-sm">
                    <thead class="border-b bg-sky-50/60 text-xs font-semibold uppercase text-bcom-navy">
                        <tr><th class="px-3 py-2">{{ __('Mã') }}</th><th class="px-3 py-2">{{ __('KS') }}</th><th class="px-3 py-2">{{ __('Hiệu lực') }}</th><th class="px-3 py-2">{{ __('Giảm') }}</th><th class="px-3 py-2"></th></tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($promoCodes as $p)
                            <tr>
                                <td class="px-3 py-2 font-mono font-semibold">{{ $p->code }}</td>
                                <td class="px-3 py-2">{{ $p->hotel?->name }}</td>
                                <td class="px-3 py-2 text-xs">{{ $p->valid_from->format('d/m/Y') }} — {{ $p->valid_to->format('d/m/Y') }}</td>
                                <td class="px-3 py-2 text-xs">{{ $p->discount_type === 'percent' ? $p->discount_value.'%' : number_format((float) $p->discount_value, 0, ',', '.').' VND' }}</td>
                                <td class="px-3 py-2">
                                    <a href="{{ route('host.promo-codes.edit', $p) }}" class="text-bcom-blue hover:underline">{{ __('Sửa') }}</a>
                                    <form method="POST" action="{{ route('host.promo-codes.destroy', $p) }}" class="inline" onsubmit="return confirm('{{ __('Xoá mã?') }}');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">{{ __('Xoá') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $promoCodes->links() }}
        </div>
    </div>
</x-app-layout>
