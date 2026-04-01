@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-red-700 bg-red-50 border border-red-100 rounded-xl px-4 py-3']) }}>
        {{ $status }}
    </div>
@endif
