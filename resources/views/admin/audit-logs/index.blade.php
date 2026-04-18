<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold text-bcom-navy">{{ __('Nhật ký audit') }}</h2></x-slot>
    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-4">
            <form method="GET" class="flex flex-wrap gap-2">
                <input type="text" name="action" value="{{ request('action') }}" placeholder="{{ __('Hành động') }}" class="rounded-xl border-gray-200 text-sm">
                <input type="text" name="subject" value="{{ request('subject') }}" placeholder="{{ __('Subject type') }}" class="rounded-xl border-gray-200 text-sm">
                <x-primary-button type="submit">{{ __('Lọc') }}</x-primary-button>
            </form>
            <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
                <table class="w-full min-w-[900px] text-left text-xs">
                    <thead class="border-b bg-sky-50/60 font-semibold uppercase text-bcom-navy">
                        <tr><th class="px-2 py-2">{{ __('Thời gian') }}</th><th class="px-2 py-2">{{ __('Actor') }}</th><th class="px-2 py-2">{{ __('Hành động') }}</th><th class="px-2 py-2">{{ __('Subject') }}</th><th class="px-2 py-2">{{ __('Chi tiết') }}</th></tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($logs as $log)
                            <tr>
                                <td class="px-2 py-2 whitespace-nowrap">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                                <td class="px-2 py-2">{{ $log->actor?->email ?? '—' }}</td>
                                <td class="px-2 py-2">{{ $log->action }}</td>
                                <td class="px-2 py-2">{{ $log->subject_type }}#{{ $log->subject_id }}</td>
                                <td class="px-2 py-2 break-all">{{ json_encode($log->properties, JSON_UNESCAPED_UNICODE) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $logs->links() }}
        </div>
    </div>
</x-app-layout>
