<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-bcom-navy leading-tight">
            {{ __('Mẫu email — :name', ['name' => $hotel->name]) }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto space-y-6">
            <x-flash-status />

            <p class="text-sm text-gray-600">
                {{ __('Nội dung tùy chỉnh hiển thị trong email «Đơn đặt mới» (Markdown đơn giản / xuống dòng). Để trống để dùng mặc định hệ thống.') }}
            </p>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md shadow-slate-900/5">
                <form method="POST" action="{{ route('host.hotels.email-templates.update', $hotel) }}" class="space-y-5 p-6 sm:p-8">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="customer_created" :value="__('Đoạn mở đầu — email gửi khách')" />
                        <textarea id="customer_created" name="customer_created" rows="6" class="mt-1 block w-full rounded-xl border-gray-200 text-sm focus:border-bcom-blue focus:ring-bcom-blue/20" placeholder="{{ __('VD: Cảm ơn bạn đã chọn khách sạn chúng tôi…') }}">{{ old('customer_created', $templates['customer_created'] ?? '') }}</textarea>
                        <x-input-error :messages="$errors->get('customer_created')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="host_created" :value="__('Đoạn mở đầu — email gửi chủ khách sạn')" />
                        <textarea id="host_created" name="host_created" rows="6" class="mt-1 block w-full rounded-xl border-gray-200 text-sm focus:border-bcom-blue focus:ring-bcom-blue/20" placeholder="{{ __('VD: Nhắc kiểm tra đơn trong vòng 24h…') }}">{{ old('host_created', $templates['host_created'] ?? '') }}</textarea>
                        <x-input-error :messages="$errors->get('host_created')" class="mt-2" />
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                        <a href="{{ route('host.hotels.show', $hotel) }}" class="text-sm text-gray-600 hover:text-bcom-blue">{{ __('Quay lại khách sạn') }}</a>
                        <x-primary-button>{{ __('Lưu mẫu email') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
