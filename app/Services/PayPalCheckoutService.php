<?php

namespace App\Services;

use App\Models\Booking;
use RuntimeException;

class PayPalCheckoutService
{
    public function __construct(
        private readonly PayPalApiClient $payPalApiClient,
    ) {}

    /**
     * Creates a PayPal order and returns the approval URL for redirect.
     */
    public function createCheckoutApprovalUrl(Booking $booking): ?string
    {
        if (! (string) config('services.paypal.client_id')
            || ! (string) config('services.paypal.client_secret')) {
            return null;
        }

        $currencyRaw = strtoupper((string) ($booking->currency ?: 'VND'));

        $amount = (float) $booking->total_price;
        $currency = $this->normalizeCurrencyForPayPal($currencyRaw);

        // Nếu đổi sang USD (ví dụ VND->USD) thì cũng cần quy đổi để số tiền không bị "quá lớn".
        if ($currencyRaw === 'VND' && $currency === 'USD') {
            $rate = (float) env('PAYPAL_VND_TO_USD_RATE', 23000);
            if ($rate > 0) {
                $amount = $amount / $rate;
            }
        }

        $value = $this->formatAmount($amount, $currency);

        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => (string) $booking->id,
                    'invoice_id' => 'B'.$booking->id,
                    'custom_id' => (string) $booking->id,
                    'description' => __('Đặt phòng :code', ['code' => $booking->booking_code]),
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => $value,
                    ],
                ],
            ],
            'application_context' => [
                'brand_name' => (string) config('app.name', 'Booking'),
                'landing_page' => 'BILLING',
                'user_action' => 'PAY_NOW',
                'return_url' => route('customer.bookings.pay.paypal.return', [], true),
                'cancel_url' => route('customer.bookings.pay.cancel', ['booking' => $booking->id], true),
            ],
        ];

        $order = $this->payPalApiClient->postJson('/v2/checkout/orders', $payload);
        $orderId = (string) ($order['id'] ?? '');
        if ($orderId === '') {
            throw new RuntimeException('PayPal order response missing id.');
        }

        $approveUrl = null;
        foreach ($order['links'] ?? [] as $link) {
            if (($link['rel'] ?? '') === 'approve' && isset($link['href'])) {
                $approveUrl = (string) $link['href'];
                break;
            }
        }

        if (! $approveUrl) {
            throw new RuntimeException('PayPal order response missing approve link.');
        }

        $booking->forceFill([
            'paypal_order_id' => $orderId,
        ])->save();

        return $approveUrl;
    }

    private function normalizeCurrencyForPayPal(string $currencyRaw): string
    {
        return match ($currencyRaw) {
            'VND' => 'USD',
            default => $currencyRaw,
        };
    }

    /**
     * @return array{id: string, status: string}
     */
    public function captureOrder(string $orderId): array
    {
        $result = $this->payPalApiClient->postJson('/v2/checkout/orders/'.$orderId.'/capture', (object)[]);

        $captureId = '';
        $units = $result['purchase_units'] ?? [];
        foreach ($units as $unit) {
            $captures = $unit['payments']['captures'] ?? [];
            foreach ($captures as $cap) {
                $captureId = (string) ($cap['id'] ?? '');
                if ($captureId !== '') {
                    break 2;
                }
            }
        }

        return [
            'id' => $captureId,
            'status' => (string) ($result['status'] ?? ''),
        ];
    }

    private function formatAmount(float $amount, string $currency): string
    {
        $zeroDecimal = ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];

        if (in_array(strtoupper($currency), $zeroDecimal, true)) {
            return (string) max(0, (int) round($amount));
        }

        return number_format($amount, 2, '.', '');
    }
}
