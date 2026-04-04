@props(['variant' => 'default'])

@if ($variant === 'light')
    <img src="{{ asset('ico-mark-light.svg') }}" alt="{{ config('app.name', 'Booking') }}" width="32" height="32"
        {{ $attributes->merge(['class' => 'shrink-0']) }} />
@else
    <img src="{{ asset('ico.svg') }}" alt="{{ config('app.name', 'Booking') }}" width="32" height="32"
        {{ $attributes->merge(['class' => 'shrink-0']) }} />
@endif
