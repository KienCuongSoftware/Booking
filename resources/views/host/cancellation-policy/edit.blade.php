<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-bcom-navy leading-tight">
            {{ __('Chính sách hủy & nhắc lịch') }}
        </h2>
    </x-slot>

    <div class="py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-5xl mx-auto space-y-6">
            <x-flash-status />

            @if ($hotels->isEmpty())
                <div class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-gray-600">
                    {{ __('Bạn chưa có khách sạn để cấu hình chính sách.') }}
                </div>
            @else
                <form method="GET" class="rounded-2xl border border-slate-200 bg-white p-4">
                    <label class="text-sm font-medium text-gray-700" for="hotel_id">{{ __('Chọn khách sạn') }}</label>
                    <div class="mt-2 flex items-center gap-3">
                        <select id="hotel_id" name="hotel_id" class="w-full rounded-lg border-gray-200 text-sm focus:border-bcom-blue focus:ring-bcom-blue/20">
                            @foreach ($hotels as $hotel)
                                <option value="{{ $hotel->id }}" @selected($selectedHotelId === $hotel->id)>{{ $hotel->name }}</option>
                            @endforeach
                        </select>
                        <x-primary-button>{{ __('Tải') }}</x-primary-button>
                    </div>
                </form>

                <form method="POST" action="{{ route('host.cancellation-policy.update') }}" class="rounded-2xl border border-slate-200 bg-white p-6 space-y-5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="hotel_id" value="{{ $selectedHotelId }}">

                    <div>
                        <x-input-label for="name" :value="__('Tên chính sách')" />
                        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $policy?->name ?? 'Chính sách tiêu chuẩn')" required />
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 p-3 text-sm text-gray-700">
                            <input type="checkbox" name="send_reminder_d3" value="1" class="rounded border-gray-300 text-bcom-blue focus:ring-bcom-blue" @checked(old('send_reminder_d3', $policy?->send_reminder_d3 ?? true))>
                            {{ __('Reminder D-3') }}
                        </label>
                        <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 p-3 text-sm text-gray-700">
                            <input type="checkbox" name="send_reminder_d1" value="1" class="rounded border-gray-300 text-bcom-blue focus:ring-bcom-blue" @checked(old('send_reminder_d1', $policy?->send_reminder_d1 ?? true))>
                            {{ __('Reminder D-1') }}
                        </label>
                        <label class="inline-flex items-center gap-2 rounded-lg border border-slate-200 p-3 text-sm text-gray-700">
                            <input type="checkbox" name="send_reminder_h6" value="1" class="rounded border-gray-300 text-bcom-blue focus:ring-bcom-blue" @checked(old('send_reminder_h6', $policy?->send_reminder_h6 ?? true))>
                            {{ __('Reminder H-6') }}
                        </label>
                    </div>

                    <div>
                        <p class="text-sm font-semibold text-bcom-navy">{{ __('Tier phí hủy') }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Mỗi dòng là một mốc giờ trước check-in.') }}</p>
                        <div class="mt-3 overflow-x-auto rounded-lg border border-slate-200">
                            @php
                                $tiers = old('tiers');
                                if (! is_array($tiers)) {
                                    $tiers = $policy?->tiers?->sortBy('sort_order')->values()->map(fn ($tier) => [
                                        'id' => $tier->id,
                                        'min_hours_before' => $tier->min_hours_before,
                                        'max_hours_before' => $tier->max_hours_before,
                                        'fee_percent' => (float) $tier->fee_percent,
                                        'sort_order' => $tier->sort_order,
                                    ])->all() ?? [
                                        ['id' => null, 'min_hours_before' => 72, 'max_hours_before' => null, 'fee_percent' => 0, 'sort_order' => 1],
                                        ['id' => null, 'min_hours_before' => 24, 'max_hours_before' => 72, 'fee_percent' => 30, 'sort_order' => 2],
                                        ['id' => null, 'min_hours_before' => 0, 'max_hours_before' => 24, 'fee_percent' => 50, 'sort_order' => 3],
                                    ];
                                }
                            @endphp

                            <table class="w-full min-w-[720px] border-collapse text-left text-sm">
                                <thead class="bg-sky-50/70 text-xs font-semibold uppercase tracking-wide text-bcom-navy">
                                    <tr>
                                        <th class="w-12 px-3 py-2 text-center">{{ __('Kéo') }}</th>
                                        <th class="px-3 py-2">{{ __('Từ (giờ)') }}</th>
                                        <th class="px-3 py-2">{{ __('Đến (giờ, để trống = vô hạn)') }}</th>
                                        <th class="px-3 py-2">{{ __('Phí (%)') }}</th>
                                        <th class="px-3 py-2">{{ __('Thứ tự') }}</th>
                                        <th class="px-3 py-2 text-right">{{ __('Thao tác') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="tier-table-body" class="divide-y divide-slate-200 bg-white">
                                    @foreach ($tiers as $i => $tier)
                                        <tr data-tier-row draggable="true" class="hover:bg-sky-50/30">
                                            <td class="px-3 py-2 text-center">
                                                <span class="inline-flex cursor-move select-none text-slate-400" title="{{ __('Kéo để sắp xếp') }}">⋮⋮</span>
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="hidden" name="tiers[{{ $i }}][id]" value="{{ $tier['id'] ?? '' }}">
                                                <input type="number" min="0" name="tiers[{{ $i }}][min_hours_before]" value="{{ $tier['min_hours_before'] }}" class="block w-full rounded-lg border-gray-200 text-sm focus:border-bcom-blue focus:ring-bcom-blue/20" required>
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="number" min="1" name="tiers[{{ $i }}][max_hours_before]" value="{{ $tier['max_hours_before'] }}" class="block w-full rounded-lg border-gray-200 text-sm focus:border-bcom-blue focus:ring-bcom-blue/20">
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="number" min="0" max="100" step="0.01" name="tiers[{{ $i }}][fee_percent]" value="{{ $tier['fee_percent'] }}" class="block w-full rounded-lg border-gray-200 text-sm focus:border-bcom-blue focus:ring-bcom-blue/20" required>
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="number" min="1" name="tiers[{{ $i }}][sort_order]" value="{{ $tier['sort_order'] }}" class="block w-full rounded-lg border-gray-200 text-sm focus:border-bcom-blue focus:ring-bcom-blue/20" required>
                                            </td>
                                            <td class="px-3 py-2 text-right">
                                                <button type="button" data-remove-tier-row class="inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">
                                                    {{ __('Xóa dòng') }}
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <button type="button" id="add-tier-row" class="inline-flex items-center rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-semibold text-bcom-blue hover:bg-sky-100">
                                {{ __('Thêm dòng tier') }}
                            </button>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button>{{ __('Lưu cấu hình') }}</x-primary-button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
<script>
    (function () {
        const tableBody = document.getElementById('tier-table-body');
        const addButton = document.getElementById('add-tier-row');
        if (!tableBody || !addButton) {
            return;
        }

        const buildName = (index, field) => `tiers[${index}][${field}]`;

        function reindexRows() {
            const rows = Array.from(tableBody.querySelectorAll('[data-tier-row]'));
            rows.forEach((row, index) => {
                row.querySelectorAll('input[name^="tiers["]').forEach((input) => {
                    if (input.name.includes('[id]')) {
                        input.name = buildName(index, 'id');
                        return;
                    }
                    if (input.name.includes('[min_hours_before]')) {
                        input.name = buildName(index, 'min_hours_before');
                        return;
                    }
                    if (input.name.includes('[max_hours_before]')) {
                        input.name = buildName(index, 'max_hours_before');
                        return;
                    }
                    if (input.name.includes('[fee_percent]')) {
                        input.name = buildName(index, 'fee_percent');
                        return;
                    }
                    if (input.name.includes('[sort_order]')) {
                        input.name = buildName(index, 'sort_order');
                        input.value = index + 1;
                    }
                });
            });
        }

        function bindDragAndDrop() {
            let draggingRow = null;

            tableBody.querySelectorAll('[data-tier-row]').forEach((row) => {
                row.ondragstart = function (event) {
                    draggingRow = this;
                    this.classList.add('opacity-60');
                    event.dataTransfer.effectAllowed = 'move';
                    event.dataTransfer.setData('text/plain', 'tier-row');
                };

                row.ondragover = function (event) {
                    event.preventDefault();
                    event.dataTransfer.dropEffect = 'move';
                };

                row.ondrop = function (event) {
                    event.preventDefault();
                    if (!draggingRow || draggingRow === this) {
                        return;
                    }

                    const rect = this.getBoundingClientRect();
                    const insertAfter = event.clientY > rect.top + rect.height / 2;
                    if (insertAfter) {
                        this.after(draggingRow);
                    } else {
                        this.before(draggingRow);
                    }
                    reindexRows();
                };

                row.ondragend = function () {
                    this.classList.remove('opacity-60');
                    draggingRow = null;
                };
            });
        }

        function bindRemoveButtons() {
            tableBody.querySelectorAll('[data-remove-tier-row]').forEach((button) => {
                button.onclick = function () {
                    const rows = tableBody.querySelectorAll('[data-tier-row]');
                    if (rows.length <= 1) {
                        return;
                    }
                    this.closest('[data-tier-row]')?.remove();
                    reindexRows();
                };
            });
        }

        function createRow(index) {
            const row = document.createElement('tr');
            row.setAttribute('data-tier-row', '');
            row.setAttribute('draggable', 'true');
            row.className = 'hover:bg-sky-50/30';
            row.innerHTML = `
                <td class="px-3 py-2 text-center">
                    <span class="inline-flex cursor-move select-none text-slate-400" title="{{ __('Kéo để sắp xếp') }}">⋮⋮</span>
                </td>
                <td class="px-3 py-2">
                    <input type="hidden" name="${buildName(index, 'id')}" value="">
                    <input type="number" min="0" name="${buildName(index, 'min_hours_before')}" value="0" class="block w-full rounded-lg border-gray-200 text-sm focus:border-bcom-blue focus:ring-bcom-blue/20" required>
                </td>
                <td class="px-3 py-2">
                    <input type="number" min="1" name="${buildName(index, 'max_hours_before')}" value="" class="block w-full rounded-lg border-gray-200 text-sm focus:border-bcom-blue focus:ring-bcom-blue/20">
                </td>
                <td class="px-3 py-2">
                    <input type="number" min="0" max="100" step="0.01" name="${buildName(index, 'fee_percent')}" value="0" class="block w-full rounded-lg border-gray-200 text-sm focus:border-bcom-blue focus:ring-bcom-blue/20" required>
                </td>
                <td class="px-3 py-2">
                    <input type="number" min="1" name="${buildName(index, 'sort_order')}" value="${index + 1}" class="block w-full rounded-lg border-gray-200 text-sm focus:border-bcom-blue focus:ring-bcom-blue/20" required>
                </td>
                <td class="px-3 py-2 text-right">
                    <button type="button" data-remove-tier-row class="inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-100">
                        {{ __('Xóa dòng') }}
                    </button>
                </td>
            `;

            return row;
        }

        addButton.addEventListener('click', function () {
            const row = createRow(tableBody.querySelectorAll('[data-tier-row]').length);
            tableBody.appendChild(row);
            bindRemoveButtons();
            bindDragAndDrop();
            reindexRows();
        });

        bindRemoveButtons();
        bindDragAndDrop();
        reindexRows();
    })();
</script>
