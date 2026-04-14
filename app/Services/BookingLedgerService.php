<?php

namespace App\Services;

use App\Enums\BookingPaymentStatus;
use App\Models\Booking;
use App\Models\BookingTransaction;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class BookingLedgerService
{
    public function recordChargeCreated(Booking $booking, ?User $actor = null): void
    {
        $this->createTransactionIfMissing($booking, 'charge', 'created', [
            'amount' => (float) $booking->total_price,
            'status' => $booking->payment_status === BookingPaymentStatus::Paid ? 'paid' : 'pending',
            'actor' => $actor,
            'metadata' => [
                'payment_method' => $booking->payment_method?->value,
                'payment_status' => $booking->payment_status?->value,
            ],
        ]);
    }

    public function recordMarkedPaid(Booking $booking, ?User $actor = null): void
    {
        $this->createTransactionIfMissing($booking, 'charge', 'paid', [
            'amount' => (float) $booking->total_price,
            'status' => 'paid',
            'actor' => $actor,
            'metadata' => [
                'payment_status' => $booking->payment_status?->value,
            ],
        ]);
    }

    public function recordCancellationFees(Booking $booking, ?User $actor = null, string $event = 'cancelled'): void
    {
        $fee = (float) ($booking->cancellation_fee_amount ?? 0);
        $refund = (float) ($booking->refund_amount ?? 0);

        if ($fee > 0) {
            $this->createTransactionIfMissing($booking, 'fee', $event, [
                'amount' => $fee,
                'status' => 'applied',
                'actor' => $actor,
                'metadata' => [
                    'status' => $booking->status?->value,
                ],
            ]);
        }

        if ($refund > 0) {
            $this->createTransactionIfMissing($booking, 'refund', $event, [
                'amount' => $refund,
                'status' => 'pending',
                'actor' => $actor,
                'metadata' => [
                    'status' => $booking->status?->value,
                ],
            ]);
        }
    }

    public function updateRefundStatus(BookingTransaction $transaction, string $targetStatus, ?User $actor = null): BookingTransaction
    {
        $allowed = [
            'pending' => ['processing', 'refunded', 'failed'],
            'processing' => ['refunded', 'failed'],
            'refunded' => [],
            'failed' => [],
        ];

        $current = (string) $transaction->status;
        if (! in_array($targetStatus, $allowed[$current] ?? [], true)) {
            throw ValidationException::withMessages([
                'status' => [__('Không thể chuyển hoàn tiền từ :from sang :to.', ['from' => $current, 'to' => $targetStatus])],
            ]);
        }

        $transaction->status = $targetStatus;
        $transaction->performed_by = $actor?->id;
        $transaction->performed_at = now();
        $transaction->save();

        return $transaction->refresh();
    }

    /**
     * @param  array{amount: float, status: string, actor?: ?User, metadata?: array<string, mixed>}  $payload
     */
    private function createTransactionIfMissing(Booking $booking, string $type, string $event, array $payload): void
    {
        $eventKey = "{$booking->id}:{$type}:{$event}";

        $booking->transactions()->firstOrCreate(
            ['event_key' => $eventKey],
            [
                'type' => $type,
                'status' => $payload['status'],
                'amount' => $payload['amount'],
                'currency' => $booking->currency ?: 'VND',
                'reference' => $booking->payment_reference,
                'metadata' => $payload['metadata'] ?? null,
                'performed_by' => $payload['actor']?->id ?? null,
                'performed_at' => now(),
            ]
        );
    }
}
