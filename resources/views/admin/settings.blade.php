<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-bcom-navy">{{ __('Cấu hình hiệu lực') }}</h2></x-slot>
    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm text-sm">
            <p class="text-gray-600">{{ __('Giá trị đang áp dụng (từ config/cache). Chỉnh trong file .env rồi chạy') }} <code class="rounded bg-gray-100 px-1">php artisan config:clear</code>.</p>
            <dl class="mt-4 space-y-2">
                @foreach ($settings as $key => $val)
                    <div class="flex justify-between gap-4 border-b border-slate-100 py-2">
                        <dt class="font-mono text-xs text-gray-500">{{ $key }}</dt>
                        <dd class="font-semibold text-bcom-navy">{{ is_bool($val) ? ($val ? 'true' : 'false') : $val }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </div>
</x-app-layout>
