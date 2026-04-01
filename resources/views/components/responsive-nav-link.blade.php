@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-3 rounded-xl text-start text-base font-semibold text-red-800 bg-red-50 border border-red-100'
            : 'block w-full ps-3 pe-4 py-3 rounded-xl text-start text-base font-medium text-gray-600 hover:text-red-700 hover:bg-red-50/60 border border-transparent transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
