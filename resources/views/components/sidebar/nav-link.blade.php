@props([
    'href',
    'active' => false,
])

<a href="{{ $href }}" @class([
    'block rounded-lg px-3 py-2 text-sm transition-colors',
    'bg-white/20 font-semibold text-white' => $active,
    'text-sky-100 hover:bg-white/10 hover:text-white' => ! $active,
])>{{ $slot }}</a>
