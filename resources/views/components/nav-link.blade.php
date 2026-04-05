@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-3 py-2 rounded-xl text-sm font-semibold text-bcom-navy bg-sky-50 border border-sky-200 shadow-sm'
            : 'inline-flex items-center px-3 py-2 rounded-xl text-sm font-medium text-gray-600 hover:text-bcom-blue hover:bg-sky-50/80 border border-transparent transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
