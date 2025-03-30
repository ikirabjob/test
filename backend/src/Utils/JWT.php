<?php

declare(strict_types=1);

namespace App\Utils;

use JsonException;

final class JWT
{
    private string $secretKey;
    private int $expiryTime;

    public function __construct() {
        $this->secretKey = $_ENV['JWT_SECRET'] ?? 'your-secret-key';
        $this->expiryTime = (int)($_ENV['JWT_EXPIRY'] ?? 3600); // 1 hour by default
    }

    /**
     * @throws JsonException
     */
    public function generate(array $payload): string {
        $header = \json_encode(['typ' => 'JWT', 'alg' => 'HS256'], JSON_THROW_ON_ERROR);

        // Add issued at and expiry time
        $payload['iat'] = time();
        $payload['exp'] = time() + $this->expiryTime;

        $encodedHeader = $this->base64UrlEncode($header);
        $encodedPayload = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));

        $signature = hash_hmac('sha256', "$encodedHeader.$encodedPayload", $this->secretKey, true);
        $encodedSignature = $this->base64UrlEncode($signature);

        return "$encodedHeader.$encodedPayload.$encodedSignature";
    }

    /**
     * @throws JsonException
     */
    public function validate(string $token): ?array {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;

        // Verify signature
        $signature = $this->base64UrlDecode($encodedSignature);
        $expectedSignature = hash_hmac('sha256', "$encodedHeader.$encodedPayload", $this->secretKey, true);

        if (!hash_equals($signature, $expectedSignature)) {
            return null;
        }

        // Decode payload
        $payload = json_decode($this->base64UrlDecode($encodedPayload), true, 512, JSON_THROW_ON_ERROR);

        // Check if token is expired
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    private function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}