@props([
    'seconds' => 3,
])

@if (session('status'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => { show = false }, {{ max(1, (int) $seconds) * 1000 }})"
        x-show="show"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        {{ $attributes->class(['rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-800']) }}
        role="status"
        aria-live="polite"
    >
        {{ session('status') }}
    </div>
@endif
