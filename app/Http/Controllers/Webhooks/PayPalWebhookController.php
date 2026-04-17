<?php

namespace App\Http\Controllers\Webhooks;

use App\Enums\BookingPaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\WebhookEvent;
use App\Services\BookingLedgerService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PayPalWebhookController extends Controller
{
    public function __construct(
        private readonly BookingLedgerService $bookingLedgerService,
    ) {}

    public function __invoke(Request $request): Response
    {
        if (! (string) config('services.paypal.client_id')
            || ! (string) config('services.paypal.client_secret')) {
            return response('PayPal not configured', 400);
        }

        $payload = $request->all();
        $eventId = (string) ($payload['id'] ?? '');
        $eventType = (string) ($payload['event_type'] ?? '');

        if ($eventId === '') {
            return response('Bad payload', 400);
        }

        $record = WebhookEvent::query()->firstOrCreate(
            [
                'provider' => 'paypal',
                'external_id' => $eventId,
            ],
            [
                'booking_id' => null,
                'event_type' => $eventType,
                'processed_at' => now(),
            ],
        );

        if (! $record->wasRecentlyCreated) {
            return response('OK', 200);
        }

        if ($eventType === 'PAYMENT.CAPTURE.COMPLETED') {
            $resource = is_array($payload['resource'] ?? null) ? $payload['resource'] : [];
            $booking = $this->resolveBookingFromCaptureResource($resource);

            if ($booking && $booking->payment_status !== BookingPaymentStatus::Paid) {
                $captureId = (string) ($resource['id'] ?? '');
                $booking->forceFill([
                    'payment_status' => BookingPaymentStatus::Paid,
                    'paypal_capture_id' => $captureId !== '' ? $captureId : $booking->paypal_capture_id,
                    'payment_reference' => $captureId !== '' ? $captureId : $booking->payment_reference,
                ])->save();

                $this->bookingLedgerService->recordMarkedPaid($booking, null);
            }
        }

        return response('OK', 200);
    }

    /**
     * @param  array<string, mixed>  $resource
     */
    private function resolveBookingFromCaptureResource(array $resource): ?Booking
    {
        $bookingId = (int) ($resource['custom_id'] ?? 0);
        if ($bookingId > 0) {
            $booking = Booking::query()->find($bookingId);
            if ($booking) {
                return $booking;
            }
        }

        $orderId = (string) data_get($resource, 'supplementary_data.related_ids.order_id', '');
        if ($orderId !== '') {
            return Booking::query()->where('paypal_order_id', $orderId)->first();
        }

        return null;
    }
}
