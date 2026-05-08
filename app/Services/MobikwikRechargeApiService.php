<?php

namespace App\Services;

use App\Models\OperatorRoute;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MobikwikRechargeApiService
{
    private const TOKEN_CACHE_SECONDS = 82800; // 23 hours, token validity is 24 hours.

    public function balance(OperatorRoute $route, array $payload): Response
    {
        return $this->postEncrypted($route, 'balance_api', '/recharge/v3/retailerBalance', $payload);
    }

    public function payment(OperatorRoute $route, array $payload, int $timeout, int $connectTimeout): Response
    {
        return $this->postEncrypted($route, 'recharge_api', '/recharge/v3/retailerPayment', $payload, $timeout, $connectTimeout);
    }

    public function status(OperatorRoute $route, array $payload): Response
    {
        return $this->postEncrypted($route, 'status_api', '/recharge/v3/retailerStatus', $payload);
    }

    public function viewBill(OperatorRoute $route, array $payload): Response
    {
        return $this->postEncrypted($route, 'view_bill_api', '/recharge/v3/retailerViewbill', $payload);
    }

    public function validation(OperatorRoute $route, array $payload): Response
    {
        return $this->postEncrypted($route, 'validation_api', '/recharge/v3/retailerValidation', $payload);
    }

    private function postEncrypted(
        OperatorRoute $route,
        string $section,
        string $defaultPath,
        array $payload,
        int $timeout = 20,
        int $connectTimeout = 5
    ): Response {
        $cfg = $route->api_config ?? [];
        $endpoint = $cfg[$section]['url'] ?? null;
        $url = $endpoint ?: $this->url($cfg, $defaultPath);
        $body = $this->encryptedBody($payload, $cfg);

        return Http::timeout($timeout)
            ->connectTimeout($connectTimeout)
            ->withHeaders([
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
                'Authorization' => $this->token($route),
            ])
            ->post($url, $body);
    }

    private function token(OperatorRoute $route): string
    {
        $cfg = $route->api_config ?? [];
        if (! empty($cfg['access_token'])) {
            return (string) $cfg['access_token'];
        }

        $cacheKey = 'mobikwik:token:route:' . $route->id;
        return Cache::remember($cacheKey, self::TOKEN_CACHE_SECONDS, function () use ($cfg) {
            $clientId = (string) ($cfg['client_id'] ?? $cfg['username'] ?? env('MOBIKWIK_CLIENT_ID', ''));
            $secret = (string) ($cfg['client_secret'] ?? $cfg['api_token'] ?? env('MOBIKWIK_CLIENT_SECRET', ''));

            if ($clientId === '' || $secret === '') {
                throw new \RuntimeException('Mobikwik clientId/clientSecret is not configured.');
            }

            $resp = Http::timeout(20)
                ->acceptJson()
                ->asJson()
                ->post($this->url($cfg, '/recharge/v1/verify/retailer'), [
                    'clientId'     => $clientId,
                    'clientSecret' => $secret,
                ]);

            $token = data_get($resp->json() ?? [], 'data.token');
            if (! $resp->successful() || ! $token) {
                throw new \RuntimeException('Mobikwik token generation failed: ' . $resp->body());
            }

            return (string) $token;
        });
    }

    private function encryptedBody(array $payload, array $cfg): array
    {
        $publicKey = $this->publicKey($cfg);
        $sessionKey = random_bytes(32);
        $iv = random_bytes(16);
        $tag = '';
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);

        $encryptedPayload = openssl_encrypt(
            $json,
            'aes-256-gcm',
            $sessionKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            16
        );

        if ($encryptedPayload === false) {
            throw new \RuntimeException('Mobikwik payload encryption failed.');
        }

        if (! openssl_public_encrypt($sessionKey, $encryptedSessionKey, $publicKey, OPENSSL_PKCS1_PADDING)) {
            throw new \RuntimeException('Mobikwik session key encryption failed.');
        }

        return [
            'encryptedSessionKey' => base64_encode($encryptedSessionKey),
            'encryptedPayload'    => base64_encode($encryptedPayload . $tag),
            'keyVersion'          => (string) ($cfg['key_version'] ?? env('MOBIKWIK_KEY_VERSION', '1.0')),
            'iv'                  => base64_encode($iv),
            'timestamp'           => (string) round(microtime(true) * 1000),
        ];
    }

    private function publicKey(array $cfg): string
    {
        $key = trim((string) ($cfg['public_key'] ?? env('MOBIKWIK_PUBLIC_KEY', '')));
        if ($key === '') {
            throw new \RuntimeException('Mobikwik public key is not configured.');
        }

        if (str_contains($key, 'BEGIN PUBLIC KEY')) {
            return $key;
        }

        $key = preg_replace('/\s+/', '', $key);
        return "-----BEGIN PUBLIC KEY-----\n" . chunk_split($key, 64, "\n") . "-----END PUBLIC KEY-----\n";
    }

    private function url(array $cfg, string $path): string
    {
        $base = rtrim((string) ($cfg['base_url'] ?? env('MOBIKWIK_BASE_URL', 'https://alpha3.mobikwik.com')), '/');
        return $base . '/' . ltrim($path, '/');
    }
}
