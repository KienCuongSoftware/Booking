@props([
    'title',
    'description' => null,
])
<div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 p-8 text-center sm:p-10">
    <p class="font-medium text-gray-900">{{ $title }}</p>
    @isset($description)
        <p class="mt-2 text-sm text-gray-600">{{ $description }}</p>
    @endisset
    <a href="{{ route('home') }}"
        class="mt-6 inline-flex items-center justify-center rounded-xl bg-bcom-blue px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-bcom-blue/20 hover:bg-bcom-blue/90">
        {{ __('Tìm khách sạn') }}
    </a>
</div>
