<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MoMoCheckoutService
{
    /**
     * Creates MoMo payment request and returns the redirect URL.
     *
     * Note: This uses a common MoMo payWithMoMoATM flow.
     */
    public function createCheckoutRedirectUrl(Booking $booking): ?string
    {
        $endpoint = (string) config('services.momo.endpoint', '');
        $partnerCode = (string) config('services.momo.partner_code', '');
        $accessKey = (string) config('services.momo.access_key', '');
        $secretKey = (string) config('services.momo.secret_key', '');

        if ($endpoint === '' || $partnerCode === '' || $accessKey === '' || $secretKey === '') {
            return null;
        }

        // MoMo requires orderId/requestId uniqueness per create request.
        // Reusing these values commonly results in expired/unpayable sessions.
        $orderId = $booking->booking_code.'-'.Str::upper(Str::random(8));
        $requestId = (string) Str::uuid();

        // Persist the latest MoMo orderId so return/ipn can map it back to the booking.
        $booking->forceFill(['momo_order_id' => $orderId])->save();
        $amountInt = max(0, (int) round((float) $booking->total_price, 0));
        $amount = (string) $amountInt;
        $orderInfo = 'Thanh toan don ' . $booking->booking_code;

        // MoMo v2 uses redirectUrl + ipnUrl.
        $redirectUrl = route('customer.bookings.pay.momo.return', [], true);
        $ipnUrl = route('webhooks.momo', [], true);

        // extraData is base64 in many integrations (empty is fine).
        $extraData = '';
        // For "Thông Tin Test Thẻ ATM" page, use the ATM one-time flow.
        // Docs: POST /v2/gateway/api/create with requestType=payWithATM
        $requestType = 'payWithATM';
        $partnerName = (string) config('app.name', 'Booking');
        $storeId = (string) config('app.name', 'Booking');
        $autoCapture = true;

        // Signature format for MoMo v2 create endpoint.
        // See MoMo docs: rawSignature = "accessKey=...&amount=...&extraData=...&ipnUrl=...&orderId=...&orderInfo=...&partnerCode=...&redirectUrl=...&requestId=...&requestType=..."
        $rawSignature =
            'accessKey='.$accessKey
            .'&amount='.$amountInt
            .'&extraData='.$extraData
            .'&ipnUrl='.$ipnUrl
            .'&orderId='.$orderId
            .'&orderInfo='.$orderInfo
            .'&partnerCode='.$partnerCode
            .'&redirectUrl='.$redirectUrl
            .'&requestId='.$requestId
            .'&requestType='.$requestType;

        $signature = hash_hmac('sha256', $rawSignature, $secretKey);

        $payload = [
            'partnerCode' => $partnerCode,
            // Some MoMo v2 implementations still accept accessKey; keep it for compatibility.
            'accessKey' => $accessKey,
            'partnerName' => $partnerName,
            'storeId' => $storeId,
            'requestId' => $requestId,
            // MoMo expects Long (number) for amount.
            'amount' => $amountInt,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'extraData' => $extraData,
            'requestType' => $requestType,
            'lang' => 'vi',
            // autoCapture is used in wallet flow; keep it but only apply when meaningful.
            // (MoMo may ignore it for ATM; harmless.)
            'autoCapture' => $autoCapture,
            'signature' => $signature,
        ];

        // MoMo test gateway yêu cầu body dạng JSON với Content-Type application/json.
        $response = Http::timeout(30)
            ->acceptJson()
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($endpoint, $payload);
        if (! $response->ok()) {
            $body = (string) $response->body();
            throw new RuntimeException('MoMo request failed: ' . $response->status() . ' - ' . $body);
        }

        $data = $response->json();
        $resultCode = (string) ($data['resultCode'] ?? $data['result_code'] ?? '');
        if ($resultCode !== '0') {
            $msg = (string) ($data['message'] ?? $data['errorMessage'] ?? $data['statusMessage'] ?? 'MoMo payment init failed');
            throw new RuntimeException('MoMo init failed: ' . $msg);
        }

        $payUrl = (string) ($data['payUrl'] ?? $data['paymentUrl'] ?? '');
        if ($payUrl === '') {
            throw new RuntimeException('MoMo init failed: missing payUrl.');
        }

        return $payUrl;
    }
}

