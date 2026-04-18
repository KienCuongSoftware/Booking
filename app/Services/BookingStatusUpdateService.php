<?php

namespace App\Services;

use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Shared host + staff booking status transitions (same rules as host UI).
 */
class BookingStatusUpdateService
{
    public function __construct(
        private readonly BookingLifecycleService $bookingLifecycleService,
        private readonly CancellationFeeService $cancellationFeeService,
        private readonly BookingLedgerService $bookingLedgerService,
        private readonly BookingNotificationService $bookingNotificationService,
        private readonly BookingWaitlistService $bookingWaitlistService,
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * @param  array{status: string, host_note?: string|null, internal_tags?: string|null, mark_paid?: bool}  $validated
     */
    public function apply(Booking $booking, User $actor, array $validated, Request $request, string $auditAction = 'host_status_updated'): Booking
    {
        if ($actor->role === UserRole::Host) {
            abort_unless($booking->hotel->host_id === $actor->id, 403);
        } elseif ($actor->role !== UserRole::Staff) {
            abort(403);
        }

        $originalStatus = $booking->status;
        $booking->host_note = ($validated['host_note'] ?? null) ?: $booking->host_note;

        if (array_key_exists('internal_tags', $validated)) {
            $raw = (string) ($validated['internal_tags'] ?? '');
            $tags = array_values(array_filter(array_map('trim', explode(',', $raw))));
            $booking->internal_tags = array_slice($tags, 0, 20);
        }

        $markedPaid = false;
        if (($validated['mark_paid'] ?? false) && $booking->payment_status !== BookingPaymentStatus::Paid) {
            $booking->payment_status = BookingPaymentStatus::Paid;
            $markedPaid = true;
        }

        $booking->save();
        $transitionContext = [
            'event_note' => $actor->role === UserRole::Staff
                ? __('Nhân viên cập nhật trạng thái đơn.')
                : __('Chủ khách sạn cập nhật trạng thái đơn.'),
        ];
        if (in_array($validated['status'], [BookingStatus::Cancelled->value, BookingStatus::NoShow->value], true)) {
            $fee = $this->cancellationFeeService->calculate($booking);
            $transitionContext['cancellation_fee_amount'] = $fee['fee_amount'];
            $transitionContext['refund_amount'] = $fee['refund_amount'];
            $transitionContext['cancellation_policy_snapshot'] = $fee['policy_snapshot'];
            $transitionContext['cancel_reason'] = $validated['status'] === BookingStatus::NoShow->value
                ? __('Khách không đến nhận phòng.')
                : match ($actor->role) {
                    UserRole::Staff => __('Đơn được hủy bởi nhân viên.'),
                    default => __('Đơn được hủy bởi chủ khách sạn.'),
                };
        }

        $booking = $this->bookingLifecycleService->transition(
            $booking,
            BookingStatus::from($validated['status']),
            $actor,
            $transitionContext,
        );

        if ($markedPaid) {
            $this->bookingLedgerService->recordMarkedPaid($booking, $actor);
        }
        if (in_array($booking->status->value, [BookingStatus::Cancelled->value, BookingStatus::NoShow->value], true)) {
            $this->bookingLedgerService->recordCancellationFees($booking, $actor, $booking->status->value);
        }
        $this->bookingNotificationService->sendStatusChanged($booking, $originalStatus);

        if (in_array($booking->status->value, [BookingStatus::Cancelled->value, BookingStatus::NoShow->value], true)) {
            $this->bookingWaitlistService->notifyForFreedSlot($booking);
        }

        $this->auditLogService->record($booking, $auditAction, $actor, [
            'from' => $originalStatus instanceof BookingStatus ? $originalStatus->value : (string) $originalStatus,
            'to' => $booking->status->value,
            'actor_role' => $actor->role->value,
        ], $request);

        return $booking;
    }
}
