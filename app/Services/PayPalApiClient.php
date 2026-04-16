<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayPalApiClient
{
    private function baseUrl(): string
    {
        $mode = strtolower((string) config('services.paypal.mode', 'sandbox'));

        return $mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    public function getAccessToken(): string
    {
        $clientId = (string) config('services.paypal.client_id');
        $secret = (string) config('services.paypal.client_secret');
        if ($clientId === '' || $secret === '') {
            throw new RuntimeException('PayPal client credentials are not configured.');
        }

        $cacheKey = 'paypal:oauth:'.sha1($clientId);

        return Cache::remember($cacheKey, now()->addMinutes(50), function () use ($clientId, $secret): string {
            $response = Http::asForm()
                ->withBasicAuth($clientId, $secret)
                ->withHeaders(['Accept' => 'application/json', 'Accept-Language' => 'en_US'])
                ->post($this->baseUrl().'/v1/oauth2/token', [
                    'grant_type' => 'client_credentials',
                ]);

            if (! $response->successful()) {
                throw new RuntimeException('PayPal OAuth failed: '.$response->body());
            }

            $token = (string) ($response->json('access_token') ?? '');
            if ($token === '') {
                throw new RuntimeException('PayPal OAuth returned empty token.');
            }

            return $token;
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function postJson(string $path, array $payload): array
    {
        $response = Http::withToken($this->getAccessToken())
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation',
            ])
            ->post($this->baseUrl().$path, $payload);

        if (! $response->successful()) {
            throw new RuntimeException('PayPal API '.$path.' failed: '.$response->body());
        }

        return $response->json() ?? [];
    }
}
