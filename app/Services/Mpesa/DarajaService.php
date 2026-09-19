<?php

namespace App\Services\Mpesa;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class DarajaService
{
    /**
     * Normalize Kenyan mobile numbers to 2547XXXXXXXX / 2541XXXXXXXX.
     *
     * @throws RuntimeException
     */
    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '0') && strlen($digits) === 10) {
            $digits = '254'.substr($digits, 1);
        } elseif (str_starts_with($digits, '7') && strlen($digits) === 9) {
            $digits = '254'.$digits;
        } elseif (str_starts_with($digits, '1') && strlen($digits) === 9) {
            $digits = '254'.$digits;
        } elseif (str_starts_with($digits, '254') && strlen($digits) === 12) {
            // already normalized
        } else {
            throw new RuntimeException('Enter a valid Kenyan phone number (e.g. 0712345678).');
        }

        if (! preg_match('/^254[17]\d{8}$/', $digits)) {
            throw new RuntimeException('Enter a valid Safaricom / Kenyan mobile number.');
        }

        return $digits;
    }

    public function generateTimestamp(?\DateTimeInterface $at = null): string
    {
        $at = $at ?? now();

        return $at->format('YmdHis');
    }

    /**
     * Password = Base64(BusinessShortCode + Passkey + Timestamp)
     */
    public function generatePassword(string $shortcode, string $passkey, string $timestamp): string
    {
        return base64_encode($shortcode.$passkey.$timestamp);
    }

    /**
     * @return array{access_token: string, expires_in: int}
     */
    public function getAccessToken(bool $forceRefresh = false): array
    {
        $this->assertCredentialsConfigured();

        $cacheKey = (string) config('mpesa.token_cache_key');

        if (! $forceRefresh) {
            $cached = Cache::get($cacheKey);
            if (is_array($cached) && ! empty($cached['access_token'])) {
                return $cached;
            }
        }

        $key = (string) config('mpesa.consumer_key');
        $secret = (string) config('mpesa.consumer_secret');
        $url = (string) config('mpesa.oauth_url');
        $timeout = (int) config('mpesa.timeout', 30);

        Log::info('M-PESA OAuth token request initiated', [
            'environment' => config('mpesa.environment'),
            'oauth_host' => parse_url($url, PHP_URL_HOST),
        ]);

        try {
            $response = Http::asForm()
                ->timeout($timeout)
                ->withBasicAuth($key, $secret)
                ->get($url);
        } catch (ConnectionException $e) {
            Log::error('M-PESA OAuth connection failed', ['message' => $e->getMessage()]);
            throw new RuntimeException('Could not reach M-PESA authentication service. Try again shortly.');
        }

        if (! $response->successful()) {
            Log::error('M-PESA OAuth HTTP error', [
                'status' => $response->status(),
                'body' => $this->safeBodySnippet($response->body()),
            ]);
            throw new RuntimeException('M-PESA authentication failed. Check Consumer Key / Secret.');
        }

        $json = $response->json();
        $token = is_array($json) ? ($json['access_token'] ?? null) : null;
        $expiresIn = (int) (is_array($json) ? ($json['expires_in'] ?? 3599) : 3599);

        if (! is_string($token) || $token === '') {
            Log::error('M-PESA OAuth malformed response', [
                'keys' => is_array($json) ? array_keys($json) : [],
            ]);
            throw new RuntimeException('M-PESA authentication returned an unexpected response.');
        }

        $buffer = (int) config('mpesa.token_cache_buffer_seconds', 60);
        $ttl = max(30, $expiresIn - $buffer);
        $payload = [
            'access_token' => $token,
            'expires_in' => $expiresIn,
        ];
        Cache::put($cacheKey, $payload, $ttl);

        Log::info('M-PESA OAuth token cached', ['ttl_seconds' => $ttl]);

        return $payload;
    }

    /**
     * Initiate Lipa Na M-PESA Online (STK Push).
     *
     * @return array{
     *     success: bool,
     *     response_code: string|null,
     *     response_description: string|null,
     *     customer_message: string|null,
     *     merchant_request_id: string|null,
     *     checkout_request_id: string|null,
     *     raw: array
     * }
     */
    public function stkPush(
        float $amount,
        string $phone,
        string $accountReference,
        string $transactionDesc = 'POS Payment',
        ?string $callbackUrl = null
    ): array {
        $this->assertCredentialsConfigured();

        $shortcode = (string) config('mpesa.shortcode');
        $passkey = (string) config('mpesa.passkey');
        $callback = $callbackUrl ?: (string) config('mpesa.callback_url');
        $transactionType = (string) config('mpesa.transaction_type', 'CustomerPayBillOnline');

        if ($callback === '' || ! preg_match('#^https://#i', $callback)) {
            throw new RuntimeException('MPESA_CALLBACK_URL must be a public HTTPS URL (not localhost).');
        }

        if ($amount < 1) {
            throw new RuntimeException('Amount must be at least KES 1.');
        }

        $normalizedPhone = $this->normalizePhone($phone);
        $timestamp = $this->generateTimestamp();
        $password = $this->generatePassword($shortcode, $passkey, $timestamp);
        $stkAmount = (int) round($amount);

        $payload = [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => $transactionType,
            'Amount' => $stkAmount,
            'PartyA' => $normalizedPhone,
            'PartyB' => $shortcode,
            'PhoneNumber' => $normalizedPhone,
            'CallBackURL' => $callback,
            'AccountReference' => mb_substr($accountReference, 0, 12),
            'TransactionDesc' => mb_substr($transactionDesc, 0, 13),
        ];

        Log::info('M-PESA STK request initiated', [
            'amount' => $stkAmount,
            'phone' => $this->maskPhone($normalizedPhone),
            'account_reference' => $payload['AccountReference'],
            'transaction_type' => $transactionType,
            'callback_host' => parse_url($callback, PHP_URL_HOST),
            'environment' => config('mpesa.environment'),
        ]);

        $token = $this->getAccessToken()['access_token'];
        $url = (string) config('mpesa.stk_push_url');
        $timeout = (int) config('mpesa.timeout', 30);

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout($timeout)
                ->post($url, $payload);
        } catch (ConnectionException $e) {
            Log::error('M-PESA STK connection failed', ['message' => $e->getMessage()]);
            throw new RuntimeException('Could not reach M-PESA. Check your connection and try again.');
        }

        $json = $response->json();
        if (! is_array($json)) {
            Log::error('M-PESA STK malformed response', [
                'status' => $response->status(),
                'body' => $this->safeBodySnippet($response->body()),
            ]);
            throw new RuntimeException('M-PESA returned an unexpected response.');
        }

        // Retry once on auth failure with a fresh token
        if ($response->status() === 401 || ($json['errorCode'] ?? null) === '404.001.03') {
            Cache::forget((string) config('mpesa.token_cache_key'));
            $token = $this->getAccessToken(true)['access_token'];
            try {
                $response = Http::withToken($token)
                    ->acceptJson()
                    ->timeout($timeout)
                    ->post($url, $payload);
                $json = $response->json();
                if (! is_array($json)) {
                    throw new RuntimeException('M-PESA returned an unexpected response.');
                }
            } catch (ConnectionException $e) {
                Log::error('M-PESA STK retry connection failed', ['message' => $e->getMessage()]);
                throw new RuntimeException('Could not reach M-PESA. Check your connection and try again.');
            }
        }

        $responseCode = isset($json['ResponseCode']) ? (string) $json['ResponseCode'] : null;
        $normalized = [
            'success' => $response->successful() && $responseCode === '0',
            'response_code' => $responseCode,
            'response_description' => $json['ResponseDescription'] ?? ($json['errorMessage'] ?? null),
            'customer_message' => $json['CustomerMessage'] ?? null,
            'merchant_request_id' => $json['MerchantRequestID'] ?? null,
            'checkout_request_id' => $json['CheckoutRequestID'] ?? null,
            'raw' => $this->sanitizeStkResponse($json),
        ];

        Log::info('M-PESA STK response received', [
            'http_status' => $response->status(),
            'response_code' => $normalized['response_code'],
            'checkout_request_id' => $normalized['checkout_request_id'],
            'merchant_request_id' => $normalized['merchant_request_id'],
            'success' => $normalized['success'],
        ]);

        if (! $normalized['success']) {
            $msg = $normalized['customer_message']
                ?: $normalized['response_description']
                ?: 'M-PESA request was not accepted. Try again.';
            throw new RuntimeException($msg);
        }

        return $normalized;
    }

    public function assertCredentialsConfigured(): void
    {
        $required = [
            'MPESA_CONSUMER_KEY' => config('mpesa.consumer_key'),
            'MPESA_CONSUMER_SECRET' => config('mpesa.consumer_secret'),
            'MPESA_SHORTCODE' => config('mpesa.shortcode'),
            'MPESA_PASSKEY' => config('mpesa.passkey'),
        ];

        foreach ($required as $envKey => $value) {
            if ($value === null || $value === '') {
                throw new RuntimeException("M-PESA is not configured. Set {$envKey} in .env (sandbox).");
            }
        }
    }

    public function maskPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if (strlen($digits) < 6) {
            return '***';
        }

        return substr($digits, 0, 3).str_repeat('*', max(0, strlen($digits) - 6)).substr($digits, -3);
    }

    /**
     * @param  array<string, mixed>  $json
     * @return array<string, mixed>
     */
    private function sanitizeStkResponse(array $json): array
    {
        unset($json['Password'], $json['password']);

        return $json;
    }

    private function safeBodySnippet(string $body): string
    {
        $snippet = mb_substr($body, 0, 300);

        return preg_replace('/(Bearer\s+)[^\s"\']+/i', '$1[redacted]', $snippet) ?? '[redacted]';
    }
}
