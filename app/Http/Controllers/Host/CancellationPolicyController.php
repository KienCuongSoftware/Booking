<?php

namespace App\Http\Controllers\Host;

use App\Models\CancellationPolicy;
use App\Models\Hotel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CancellationPolicyController
{
    public function edit(Request $request): View
    {
        $hotels = Hotel::query()
            ->where('host_id', $request->user()->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $selectedHotelId = (int) ($request->integer('hotel_id') ?: ($hotels->first()->id ?? 0));
        $policy = CancellationPolicy::query()
            ->with('tiers')
            ->where('hotel_id', $selectedHotelId)
            ->first();

        return view('host.cancellation-policy.edit', [
            'hotels' => $hotels,
            'selectedHotelId' => $selectedHotelId,
            'policy' => $policy,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hotel_id' => ['required', 'integer', 'exists:hotels,id'],
            'name' => ['required', 'string', 'max:120'],
            'send_reminder_d3' => ['nullable', 'boolean'],
            'send_reminder_d1' => ['nullable', 'boolean'],
            'send_reminder_h6' => ['nullable', 'boolean'],
            'tiers' => ['required', 'array', 'min:1'],
            'tiers.*.id' => ['nullable', 'integer', 'exists:cancellation_policy_tiers,id'],
            'tiers.*.min_hours_before' => ['required', 'integer', 'min:0'],
            'tiers.*.max_hours_before' => ['nullable', 'integer', 'min:1'],
            'tiers.*.fee_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'tiers.*.sort_order' => ['required', 'integer', 'min:1'],
        ]);

        $hotel = Hotel::query()
            ->where('id', $validated['hotel_id'])
            ->where('host_id', $request->user()->id)
            ->firstOrFail();

        $this->assertNoTierConflicts($validated['tiers']);

        $policy = CancellationPolicy::query()->updateOrCreate(
            ['hotel_id' => $hotel->id],
            [
                'name' => $validated['name'],
                'is_active' => true,
                'send_reminder_d3' => (bool) ($validated['send_reminder_d3'] ?? false),
                'send_reminder_d1' => (bool) ($validated['send_reminder_d1'] ?? false),
                'send_reminder_h6' => (bool) ($validated['send_reminder_h6'] ?? false),
            ],
        );

        $keptTierIds = [];
        foreach ($validated['tiers'] as $tierInput) {
            $tier = $policy->tiers()->updateOrCreate(
                [
                    'id' => $tierInput['id'] ?? null,
                ],
                [
                    'min_hours_before' => $tierInput['min_hours_before'],
                    'max_hours_before' => $tierInput['max_hours_before'] ?: null,
                    'fee_percent' => $tierInput['fee_percent'],
                    'sort_order' => $tierInput['sort_order'],
                ],
            );
            $keptTierIds[] = $tier->id;
        }

        $policy->tiers()->whereNotIn('id', $keptTierIds)->delete();

        return redirect()
            ->route('host.cancellation-policy.edit', ['hotel_id' => $hotel->id])
            ->with('status', __('Đã cập nhật chính sách hủy và reminder.'));
    }

    /**
     * @param  array<int, array<string, mixed>>  $tiers
     */
    private function assertNoTierConflicts(array $tiers): void
    {
        $intervals = [];
        $sortOrders = [];

        foreach ($tiers as $index => $tier) {
            $min = (int) $tier['min_hours_before'];
            $max = $tier['max_hours_before'] !== null ? (int) $tier['max_hours_before'] : null;
            $order = (int) $tier['sort_order'];

            if ($max !== null && $max <= $min) {
                throw ValidationException::withMessages([
                    "tiers.{$index}.max_hours_before" => __('Mốc "Đến" phải lớn hơn "Từ".'),
                ]);
            }

            if (in_array($order, $sortOrders, true)) {
                throw ValidationException::withMessages([
                    "tiers.{$index}.sort_order" => __('Thứ tự không được trùng nhau.'),
                ]);
            }
            $sortOrders[] = $order;

            $intervals[] = [
                'index' => $index,
                'start' => $min,
                'end' => $max ?? PHP_INT_MAX,
            ];
        }

        usort($intervals, fn (array $a, array $b): int => $a['start'] <=> $b['start']);

        for ($i = 1; $i < count($intervals); $i++) {
            $prev = $intervals[$i - 1];
            $curr = $intervals[$i];
            if ($curr['start'] < $prev['end']) {
                throw ValidationException::withMessages([
                    "tiers.{$curr['index']}.min_hours_before" => __('Các tier đang bị chồng lấn thời gian. Vui lòng kiểm tra lại khoảng giờ.'),
                ]);
            }
        }
    }
}
