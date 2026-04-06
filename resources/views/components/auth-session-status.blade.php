@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-bcom-blue bg-sky-50 border border-slate-200 rounded-xl px-4 py-3']) }}>
        {{ $status }}
    </div>
@endif
