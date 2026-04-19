<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-bcom-navy">{{ __('Sửa mã') }} {{ $promoCode->code }}</h2></x-slot>
    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            @include('host.promo-codes._form')
        </div>
    </div>
</x-app-layout>
