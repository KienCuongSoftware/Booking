<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class BookingLifecycleService
{
    /**
     * @var array<string, array<string>>
     */
    private const ALLOWED_TRANSITIONS = [
        BookingStatus::Pending->value => [
            BookingStatus::Confirmed->value,
            BookingStatus::Cancelled->value,
        ],
        BookingStatus::Confirmed->value => [
            BookingStatus::Completed->value,
            BookingStatus::Cancelled->value,
            BookingStatus::NoShow->value,
        ],
        BookingStatus::Cancelled->value => [],
        BookingStatus::NoShow->value => [],
        BookingStatus::Completed->value => [],
    ];

    /**
     * @param  array<string, mixed>  $context
     */
    public function transition(Booking $booking, BookingStatus $targetStatus, ?User $actor = null, array $context = []): Booking
    {
        $currentStatus = $booking->status instanceof BookingStatus
            ? $booking->status
            : BookingStatus::from((string) $booking->status);

        if ($currentStatus === $targetStatus) {
            throw ValidationException::withMessages([
                'status' => [__('Trạng thái đơn đặt không thay đổi.')],
            ]);
        }

        if (! $this->canTransition($currentStatus, $targetStatus)) {
            throw ValidationException::withMessages([
                'status' => [__('Không thể chuyển từ :from sang :to.', [
                    'from' => $currentStatus->labelVi(),
                    'to' => $targetStatus->labelVi(),
                ])],
            ]);
        }

        $now = Carbon::now();

        $booking->status = $targetStatus;
        $booking->status_changed_at = $now;
        $booking->status_changed_by = $actor?->id;

        if ($targetStatus === BookingStatus::Confirmed && ! $booking->confirmed_at) {
            $booking->confirmed_at = $now;
        }

        if ($targetStatus === BookingStatus::Completed && ! $booking->completed_at) {
            $booking->completed_at = $now;
        }

        if ($targetStatus === BookingStatus::Cancelled) {
            $booking->cancelled_at = $now;
            $booking->cancelled_by = $actor?->id;
            $booking->cancel_reason = $context['cancel_reason'] ?? $booking->cancel_reason;
            $booking->cancellation_fee_amount = $context['cancellation_fee_amount'] ?? $booking->cancellation_fee_amount;
            $booking->refund_amount = $context['refund_amount'] ?? $booking->refund_amount;
            $booking->cancellation_policy_snapshot = $context['cancellation_policy_snapshot'] ?? $booking->cancellation_policy_snapshot;
        }

        if ($targetStatus === BookingStatus::NoShow && ! $booking->no_show_at) {
            $booking->no_show_at = $now;
            $booking->cancel_reason = $context['cancel_reason'] ?? __('Khách không đến nhận phòng đúng giờ.');
            $booking->cancellation_fee_amount = $context['cancellation_fee_amount'] ?? $booking->cancellation_fee_amount;
            $booking->refund_amount = $context['refund_amount'] ?? $booking->refund_amount;
            $booking->cancellation_policy_snapshot = $context['cancellation_policy_snapshot'] ?? $booking->cancellation_policy_snapshot;
        }

        $booking->save();
        $this->recordStatusEvent($booking, $currentStatus, $targetStatus, $actor, $context);

        return $booking->refresh();
    }

    public function canTransition(BookingStatus $from, BookingStatus $to): bool
    {
        return in_array($to->value, self::ALLOWED_TRANSITIONS[$from->value] ?? [], true);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function recordStatusEvent(Booking $booking, ?BookingStatus $from, BookingStatus $to, ?User $actor = null, array $context = []): void
    {
        $booking->statusEvents()->create([
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'changed_by' => $actor?->id,
            'changed_at' => $booking->status_changed_at ?? now(),
            'note' => $context['event_note'] ?? null,
            'context' => $context !== [] ? $context : null,
        ]);
    }
}
