<?php

namespace App\Http\Controllers\Webhooks;

use App\Enums\BookingPaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\WebhookEvent;
use App\Services\BookingLedgerService;
use App\Services\BookingNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MoMoWebhookController extends Controller
{
    public function __construct(
        private readonly BookingLedgerService $bookingLedgerService,
        private readonly BookingNotificationService $bookingNotificationService,
    ) {}

    public function __invoke(Request $request): Response
    {
        if (! config('booking.payments.momo_webhook.enabled', false)) {
            return response('MoMo webhook disabled', 400);
        }

        $payload = $request->all();
        $orderId = (string) ($payload['orderId'] ?? $payload['order_id'] ?? '');
        $resultCode = $payload['resultCode'] ?? $payload['result_code'] ?? null;
        $transId = (string) ($payload['transId'] ?? $payload['trans_id'] ?? $orderId);

        if ($orderId === '') {
            return response('Bad request', 400);
        }

        $externalId = 'momo:'.$transId;
        $record = WebhookEvent::query()->firstOrCreate(
            [
                'provider' => 'momo',
                'external_id' => $externalId,
            ],
            [
                'booking_id' => null,
                'event_type' => 'payment',
                'processed_at' => now(),
            ],
        );

        if (! $record->wasRecentlyCreated) {
            return response('OK', 200);
        }

        if ((int) $resultCode === 0) {
            $booking = Booking::query()
                ->where('momo_order_id', $orderId)
                ->orWhere('booking_code', $orderId)
                ->first();

            if ($booking && $booking->payment_status !== BookingPaymentStatus::Paid) {
                $booking->forceFill([
                    'payment_status' => BookingPaymentStatus::Paid,
                    'payment_reference' => $transId,
                ])->save();

                $this->bookingLedgerService->recordMarkedPaid($booking, null);
                $this->bookingNotificationService->sendCreated($booking);
            }
        }

        return response('OK', 200);
    }
}
